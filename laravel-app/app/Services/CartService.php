<?php

namespace App\Services;

use App\Support\MsSqlConsoleDebug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartService
{
    private const CART_KEY = 'shopping_cart_items';

    /**
     * @return array{success:bool,message:string}
     */
    public function addItem(Request $request, int $productId, int $quantity = 1): array
    {
        $quantity = max(1, $quantity);
        $product = $this->findProductById($productId);

        if ($product === null) {
            return [
                'success' => false,
                'message' => 'This product does not exist.',
            ];
        }

        $stockQty = (int) $product['stock_qty'];
        if ($stockQty <= 0) {
            return [
                'success' => false,
                'message' => sprintf('%s is out of stock.', (string) $product['product_name']),
            ];
        }

        $cart = $this->getRawCart($request);
        $existingQuantity = (int) ($cart[$productId] ?? 0);
        $requestedQuantity = $existingQuantity + $quantity;

        if ($requestedQuantity > $stockQty) {
            return [
                'success' => false,
                'message' => sprintf(
                    'Only %d unit(s) of %s available right now.',
                    $stockQty,
                    (string) $product['product_name']
                ),
            ];
        }

        $cart[$productId] = $requestedQuantity;
        $this->putRawCart($request, $cart);

        return [
            'success' => true,
            'message' => sprintf('%s added to cart.', (string) $product['product_name']),
        ];
    }

    /**
     * @return array{success:bool,message:string}
     */
    public function removeItem(Request $request, int $productId): array
    {
        $cart = $this->getRawCart($request);

        if (!array_key_exists($productId, $cart)) {
            return [
                'success' => false,
                'message' => 'This item is not in your cart.',
            ];
        }

        unset($cart[$productId]);
        $this->putRawCart($request, $cart);

        return [
            'success' => true,
            'message' => 'Item removed from cart.',
        ];
    }

    public function clear(Request $request): void
    {
        $request->session()->forget(self::CART_KEY);
    }

    /**
     * @return array<int,int>
     */
    public function getCartQuantities(Request $request): array
    {
        return $this->getRawCart($request);
    }

    /**
     * @return array{item_count:int,unique_count:int,has_items:bool}
     */
    public function getCartSummary(Request $request): array
    {
        $cart = $this->getRawCart($request);
        $itemCount = array_sum($cart);

        return [
            'item_count' => $itemCount,
            'unique_count' => count($cart),
            'has_items' => $itemCount > 0,
        ];
    }

    /**
     * @return array{
     *     items:array<int,array<string,mixed>>,
     *     item_count:int,
     *     unique_count:int,
     *     total_amount:float,
     *     total_amount_formatted:string,
     *     issues:array<int,string>,
     *     can_checkout:bool
     * }
     */
    public function getCartDetails(Request $request): array
    {
        $cart = $this->getRawCart($request);

        if ($cart === []) {
            return [
                'items' => [],
                'item_count' => 0,
                'unique_count' => 0,
                'total_amount' => 0.0,
                'total_amount_formatted' => $this->formatMoney(0),
                'issues' => [],
                'can_checkout' => false,
            ];
        }

        $products = $this->findProductsByIds(array_keys($cart));
        $items = [];
        $issues = [];
        $itemCount = 0;
        $totalAmount = 0.0;

        foreach ($cart as $productId => $quantity) {
            $product = $products[$productId] ?? null;

            if ($product === null) {
                $issues[] = 'A product in your cart is no longer available.';
                continue;
            }

            $stockQty = (int) $product['stock_qty'];
            $unitPrice = (float) $product['price'];
            $lineTotal = round($unitPrice * $quantity, 2);
            $canFulfill = $stockQty >= $quantity && $stockQty > 0;

            if (!$canFulfill) {
                $issues[] = sprintf(
                    '%s has insufficient stock. Available: %d, in cart: %d.',
                    (string) $product['product_name'],
                    $stockQty,
                    $quantity
                );
            }

            $items[] = [
                'product_id' => (int) $product['product_id'],
                'product_name' => (string) $product['product_name'],
                'quantity' => (int) $quantity,
                'stock_qty' => $stockQty,
                'unit_price' => $unitPrice,
                'unit_price_formatted' => $this->formatMoney($unitPrice),
                'line_total' => $lineTotal,
                'line_total_formatted' => $this->formatMoney($lineTotal),
                'can_fulfill' => $canFulfill,
            ];

            $itemCount += (int) $quantity;
            $totalAmount += $lineTotal;
        }

        $totalAmount = round($totalAmount, 2);

        return [
            'items' => $items,
            'item_count' => $itemCount,
            'unique_count' => count($items),
            'total_amount' => $totalAmount,
            'total_amount_formatted' => $this->formatMoney($totalAmount),
            'issues' => $issues,
            'can_checkout' => count($items) > 0 && $issues === [],
        ];
    }

    /**
     * @return array<int,int>
     */
    private function getRawCart(Request $request): array
    {
        $cart = $request->session()->get(self::CART_KEY, []);
        if (!is_array($cart)) {
            return [];
        }

        $normalizedCart = [];
        foreach ($cart as $productId => $quantity) {
            $normalizedProductId = (int) $productId;
            $normalizedQuantity = (int) $quantity;

            if ($normalizedProductId <= 0 || $normalizedQuantity <= 0) {
                continue;
            }

            $normalizedCart[$normalizedProductId] = $normalizedQuantity;
        }

        return $normalizedCart;
    }

    /**
     * @param array<int,int> $cart
     */
    private function putRawCart(Request $request, array $cart): void
    {
        if ($cart === []) {
            $request->session()->forget(self::CART_KEY);
            return;
        }

        $request->session()->put(self::CART_KEY, $cart);
    }

    /**
     * @return array<string,mixed>|null
     */
    private function findProductById(int $productId): ?array
    {
        $sql = 'SELECT product_id, product_name, price, stock_qty
                FROM products
                WHERE product_id = ?';
        $bindings = [$productId];
        $row = DB::connection('sqlsrv')->selectOne($sql, $bindings);

        MsSqlConsoleDebug::push($sql, $bindings, $row ? (array) $row : null);

        return $row ? (array) $row : null;
    }

    /**
     * @param array<int,int> $productIds
     * @return array<int,array<string,mixed>>
     */
    private function findProductsByIds(array $productIds): array
    {
        if ($productIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $sql = "SELECT product_id, product_name, price, stock_qty
                FROM products
                WHERE product_id IN ({$placeholders})";
        $rows = DB::connection('sqlsrv')->select($sql, $productIds);

        MsSqlConsoleDebug::push($sql, $productIds, array_map(static fn ($row) => (array) $row, $rows));

        $products = [];
        foreach ($rows as $row) {
            $product = (array) $row;
            $products[(int) $product['product_id']] = $product;
        }

        return $products;
    }

    private function formatMoney(float $amount): string
    {
        return 'BDT ' . number_format($amount, 2);
    }
}

