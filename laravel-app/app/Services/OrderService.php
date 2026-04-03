<?php

namespace App\Services;

use App\Support\MsSqlConsoleDebug;
use App\Support\ProductImagePath;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderService
{
    /**
     * @param array<int,int> $cartQuantities
     * @return array{
     *     order_id:int,
     *     order_status:string,
     *     shipping_address:string,
     *     total_amount:float,
     *     total_amount_formatted:string,
     *     items:array<int,array<string,mixed>>
     * }
     */
    public function confirmOrder(int $userId, string $shippingAddress, array $cartQuantities): array
    {
        $cart = $this->normalizeCartQuantities($cartQuantities);
        if ($cart === []) {
            throw new RuntimeException('Your cart is empty.');
        }

        $shippingAddress = trim($shippingAddress);
        if ($shippingAddress === '') {
            throw new RuntimeException('Shipping address is required to place an order.');
        }

        $connection = DB::connection('sqlsrv');
        $transactionStarted = false;

        try {
            $connection->beginTransaction();
            MsSqlConsoleDebug::push('BEGIN TRANSACTION (PDO)', [], ['executed' => true]);
            $transactionStarted = true;

            $lineItems = [];
            $totalAmount = 0.0;

            foreach ($cart as $productId => $quantity) {
                $productSql = 'SELECT product_id, product_name, price, stock_qty
                               FROM products
                               WHERE product_id = ?';
                $productBindings = [$productId];
                $productRow = $connection->selectOne($productSql, $productBindings);
                MsSqlConsoleDebug::push($productSql, $productBindings, $productRow ? (array) $productRow : null);

                if ($productRow === null) {
                    throw new RuntimeException('One of the products in your cart does not exist anymore.');
                }

                $product = (array) $productRow;
                $availableStock = (int) $product['stock_qty'];
                if ($availableStock < $quantity) {
                    throw new RuntimeException(sprintf(
                        'Not enough stock for %s. Available: %d, requested: %d.',
                        (string) $product['product_name'],
                        $availableStock,
                        $quantity
                    ));
                }

                $unitPrice = (float) $product['price'];
                $lineTotal = round($unitPrice * $quantity, 2);

                $lineItems[] = [
                    'product_id' => (int) $product['product_id'],
                    'product_name' => (string) $product['product_name'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'unit_price_formatted' => $this->formatMoney($unitPrice),
                    'line_total' => $lineTotal,
                    'line_total_formatted' => $this->formatMoney($lineTotal),
                ];
                $totalAmount += $lineTotal;
            }

            $totalAmount = round($totalAmount, 2);
            $orderStatus = 'pending';

            $insertOrderSql = 'INSERT INTO orders (user_id, order_date, order_status, shipping_address, total_amount, is_paid, created_at, updated_at)
                               OUTPUT INSERTED.order_id AS order_id
                               VALUES (?, SYSDATETIME(), ?, ?, ?, 0, SYSDATETIME(), SYSDATETIME())';
            $insertOrderBindings = [$userId, $orderStatus, $shippingAddress, $totalAmount];
            $orderRow = $connection->selectOne($insertOrderSql, $insertOrderBindings);
            MsSqlConsoleDebug::push($insertOrderSql, $insertOrderBindings, $orderRow ? (array) $orderRow : null);

            if ($orderRow === null) {
                throw new RuntimeException('Unable to create your order right now.');
            }

            $orderId = (int) $orderRow->order_id;

            foreach ($lineItems as $lineItem) {
                $insertItemSql = 'INSERT INTO order_items (order_id, product_id, quantity, unit_price, line_total)
                                  VALUES (?, ?, ?, ?, ?)';
                $insertItemBindings = [
                    $orderId,
                    (int) $lineItem['product_id'],
                    (int) $lineItem['quantity'],
                    (float) $lineItem['unit_price'],
                    (float) $lineItem['line_total'],
                ];
                $inserted = $connection->insert($insertItemSql, $insertItemBindings);
                MsSqlConsoleDebug::push($insertItemSql, $insertItemBindings, ['inserted' => $inserted]);

                $decrementStockSql = 'UPDATE products
                                      SET stock_qty = stock_qty - ?, updated_at = SYSDATETIME()
                                      WHERE product_id = ? AND stock_qty >= ?';
                $decrementStockBindings = [
                    (int) $lineItem['quantity'],
                    (int) $lineItem['product_id'],
                    (int) $lineItem['quantity'],
                ];
                $affectedRows = $connection->update($decrementStockSql, $decrementStockBindings);
                MsSqlConsoleDebug::push($decrementStockSql, $decrementStockBindings, ['affected_rows' => $affectedRows]);

                if ($affectedRows !== 1) {
                    throw new RuntimeException(sprintf(
                        'Stock update failed for %s. Please try checkout again.',
                        (string) $lineItem['product_name']
                    ));
                }
            }

            $confirmedOrder = [
                'order_id' => $orderId,
                'order_status' => $orderStatus,
                'shipping_address' => $shippingAddress,
                'total_amount' => $totalAmount,
                'total_amount_formatted' => $this->formatMoney($totalAmount),
                'items' => $lineItems,
            ];

            $connection->commit();
            MsSqlConsoleDebug::push('COMMIT TRANSACTION (PDO)', [], ['executed' => true]);
            $transactionStarted = false;

            return $confirmedOrder;
        } catch (\Throwable $exception) {
            if ($transactionStarted && $connection->transactionLevel() > 0) {
                try {
                    $connection->rollBack();
                    MsSqlConsoleDebug::push('ROLLBACK TRANSACTION (PDO)', [], ['executed' => true]);
                } catch (\Throwable) {
                    // Preserve original checkout exception when rollback call also fails.
                }
            }

            throw $exception;
        }
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function getOrdersForUser(int $userId): array
    {
        $ordersSql = 'SELECT order_id, order_date, order_status, shipping_address, total_amount, is_paid
                      FROM orders
                      WHERE user_id = ?
                      ORDER BY order_date DESC, order_id DESC';
        $ordersBindings = [$userId];
        $orderRows = DB::connection('sqlsrv')->select($ordersSql, $ordersBindings);
        MsSqlConsoleDebug::push($ordersSql, $ordersBindings, array_map(static fn ($row) => (array) $row, $orderRows));

        if ($orderRows === []) {
            return [];
        }

        $itemsSql = 'SELECT
                        oi.order_id,
                        oi.product_id,
                        p.product_name,
                        oi.quantity,
                        oi.unit_price,
                        oi.line_total,
                        r.return_id,
                        r.return_code,
                        r.status AS return_status
                     FROM order_items oi
                     INNER JOIN orders o ON o.order_id = oi.order_id
                     INNER JOIN products p ON p.product_id = oi.product_id
                     LEFT JOIN returns r ON r.order_id = oi.order_id AND r.product_id = oi.product_id
                     WHERE o.user_id = ?
                     ORDER BY oi.order_id DESC, p.product_name ASC';
        $itemsBindings = [$userId];
        $itemRows = DB::connection('sqlsrv')->select($itemsSql, $itemsBindings);
        MsSqlConsoleDebug::push($itemsSql, $itemsBindings, array_map(static fn ($row) => (array) $row, $itemRows));

        $itemsByOrderId = [];
        foreach ($itemRows as $row) {
            $item = (array) $row;
            $orderId = (int) $item['order_id'];
            $unitPrice = (float) $item['unit_price'];
            $lineTotal = (float) $item['line_total'];

            $itemsByOrderId[$orderId][] = [
                'product_id' => (int) $item['product_id'],
                'product_name' => (string) $item['product_name'],
                'image_path' => ProductImagePath::resolve((string) $item['product_name']),
                'quantity' => (int) $item['quantity'],
                'unit_price' => $unitPrice,
                'unit_price_formatted' => $this->formatMoney($unitPrice),
                'line_total' => $lineTotal,
                'line_total_formatted' => $this->formatMoney($lineTotal),
                'return_id' => isset($item['return_id']) ? (int) $item['return_id'] : null,
                'return_code' => isset($item['return_code']) ? (string) $item['return_code'] : null,
                'return_status' => isset($item['return_status']) ? (string) $item['return_status'] : null,
            ];
        }

        $orders = [];
        foreach ($orderRows as $orderRow) {
            $order = (array) $orderRow;
            $orderId = (int) $order['order_id'];
            $totalAmount = (float) $order['total_amount'];

            $orders[] = [
                'order_id' => $orderId,
                'order_date' => $order['order_date'],
                'order_status' => (string) $order['order_status'],
                'shipping_address' => (string) $order['shipping_address'],
                'total_amount' => $totalAmount,
                'total_amount_formatted' => $this->formatMoney($totalAmount),
                'is_paid' => (bool) $order['is_paid'],
                'items' => $itemsByOrderId[$orderId] ?? [],
            ];
        }

        return $orders;
    }

    /**
     * @param array<int,int> $cartQuantities
     * @return array<int,int>
     */
    private function normalizeCartQuantities(array $cartQuantities): array
    {
        $normalized = [];
        foreach ($cartQuantities as $productId => $quantity) {
            $normalizedProductId = (int) $productId;
            $normalizedQuantity = (int) $quantity;

            if ($normalizedProductId <= 0 || $normalizedQuantity <= 0) {
                continue;
            }

            $normalized[$normalizedProductId] = $normalizedQuantity;
        }

        return $normalized;
    }

    private function formatMoney(float $amount): string
    {
        return 'BDT ' . number_format($amount, 2);
    }
}
