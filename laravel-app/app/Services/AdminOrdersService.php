<?php

namespace App\Services;

use App\Support\MsSqlConsoleDebug;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AdminOrdersService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getOrdersForAdminView(?string $searchQuery = null): array
    {
        $searchTerm = trim((string) $searchQuery);
        $likeValue = '%' . $searchTerm . '%';

        $sql = "SELECT
                    o.order_id,
                    o.user_id,
                    u.full_name AS user_name,
                    u.email AS user_email,
                    o.order_date,
                    o.order_status,
                    o.shipping_address,
                    o.total_amount,
                    o.is_paid,
                    p.payment_method,
                    p.payment_status,
                    ISNULL(items.total_items, 0) AS total_items
                FROM orders o
                INNER JOIN users u ON u.user_id = o.user_id
                LEFT JOIN payments p ON p.order_id = o.order_id
                LEFT JOIN (
                    SELECT
                        oi.order_id,
                        SUM(oi.quantity) AS total_items
                    FROM order_items oi
                    GROUP BY oi.order_id
                ) AS items ON items.order_id = o.order_id
                WHERE (? = ''
                    OR CAST(o.order_id AS VARCHAR(20)) LIKE ?
                    OR CAST(o.user_id AS VARCHAR(20)) LIKE ?
                    OR u.full_name LIKE ?
                    OR u.email LIKE ?
                    OR o.order_status LIKE ?
                    OR ISNULL(p.payment_status, '') LIKE ?
                    OR ISNULL(p.payment_method, '') LIKE ?
                    OR o.shipping_address LIKE ?
                    OR CASE WHEN o.is_paid = 1 THEN 'paid' ELSE 'unpaid' END LIKE ?)
                ORDER BY o.order_date DESC, o.order_id DESC";

        $bindings = [
            $searchTerm,
            $likeValue,
            $likeValue,
            $likeValue,
            $likeValue,
            $likeValue,
            $likeValue,
            $likeValue,
            $likeValue,
            $likeValue,
        ];

        $rows = DB::connection('sqlsrv')->select($sql, $bindings);
        $orders = array_map(static fn ($row): array => (array) $row, $rows);
        MsSqlConsoleDebug::push($sql, $bindings, $orders);

        return $orders;
    }

    public function updateOrderStatusForAdmin(int $orderId, string $status): void
    {
        $normalizedStatus = strtolower(trim($status));
        $allowedStatuses = ['pending', 'confirmed', 'shipped', 'delivered'];

        if (!in_array($normalizedStatus, $allowedStatuses, true)) {
            throw new RuntimeException('Invalid order status.');
        }

        $sql = 'UPDATE orders
                SET order_status = ?, updated_at = SYSDATETIME()
                WHERE order_id = ?';
        $bindings = [$normalizedStatus, $orderId];
        $affectedRows = DB::connection('sqlsrv')->update($sql, $bindings);
        MsSqlConsoleDebug::push($sql, $bindings, ['affected_rows' => $affectedRows]);

        if ($affectedRows > 0) {
            return;
        }

        $this->assertOrderExists($orderId);
    }

    public function updateOrderPaymentForAdmin(int $orderId, bool $isPaid): void
    {
        $sql = 'UPDATE orders
                SET is_paid = ?, updated_at = SYSDATETIME()
                WHERE order_id = ?';
        $bindings = [$isPaid ? 1 : 0, $orderId];
        $affectedRows = DB::connection('sqlsrv')->update($sql, $bindings);
        MsSqlConsoleDebug::push($sql, $bindings, ['affected_rows' => $affectedRows]);

        if ($affectedRows > 0) {
            $this->syncPaymentState($orderId, $isPaid);
            return;
        }

        $this->assertOrderExists($orderId);
    }

    public function updateOrderForAdmin(int $orderId, string $status, bool $isPaid): void
    {
        $normalizedStatus = strtolower(trim($status));
        $allowedStatuses = ['pending', 'confirmed', 'shipped', 'delivered'];

        if (!in_array($normalizedStatus, $allowedStatuses, true)) {
            throw new RuntimeException('Invalid order status.');
        }

        $sql = 'UPDATE orders
                SET order_status = ?, is_paid = ?, updated_at = SYSDATETIME()
                WHERE order_id = ?';
        $bindings = [$normalizedStatus, $isPaid ? 1 : 0, $orderId];
        $affectedRows = DB::connection('sqlsrv')->update($sql, $bindings);
        MsSqlConsoleDebug::push($sql, $bindings, ['affected_rows' => $affectedRows]);

        if ($affectedRows > 0) {
            $this->syncPaymentState($orderId, $isPaid);
            return;
        }

        $this->assertOrderExists($orderId);
    }

    private function assertOrderExists(int $orderId): void
    {
        $existsSql = 'SELECT order_id
                      FROM orders
                      WHERE order_id = ?';
        $existsBindings = [$orderId];
        $existingRow = DB::connection('sqlsrv')->selectOne($existsSql, $existsBindings);
        MsSqlConsoleDebug::push($existsSql, $existsBindings, $existingRow ? (array) $existingRow : null);

        if ($existingRow === null) {
            throw new RuntimeException('Order not found.');
        }
    }

    private function syncPaymentState(int $orderId, bool $isPaid): void
    {
        $orderSql = 'SELECT order_id, total_amount FROM orders WHERE order_id = ?';
        $orderBindings = [$orderId];
        $orderRow = DB::connection('sqlsrv')->selectOne($orderSql, $orderBindings);
        MsSqlConsoleDebug::push($orderSql, $orderBindings, $orderRow ? (array) $orderRow : null);

        if ($orderRow === null) {
            return;
        }

        $existingPaymentSql = 'SELECT payment_id FROM payments WHERE order_id = ?';
        $existingPaymentBindings = [$orderId];
        $existingPaymentRow = DB::connection('sqlsrv')->selectOne($existingPaymentSql, $existingPaymentBindings);
        MsSqlConsoleDebug::push($existingPaymentSql, $existingPaymentBindings, $existingPaymentRow ? (array) $existingPaymentRow : null);

        $paymentStatus = $isPaid ? 'paid' : 'pending';
        if ($existingPaymentRow !== null) {
            $updatePaymentSql = "UPDATE payments
                                 SET payment_status = ?,
                                     payment_date = CASE WHEN ? = 'paid' THEN COALESCE(payment_date, SYSDATETIME()) ELSE NULL END,
                                     updated_at = SYSDATETIME()
                                 WHERE order_id = ?";
            $updatePaymentBindings = [$paymentStatus, $paymentStatus, $orderId];
            $updatedRows = DB::connection('sqlsrv')->update($updatePaymentSql, $updatePaymentBindings);
            MsSqlConsoleDebug::push($updatePaymentSql, $updatePaymentBindings, ['affected_rows' => $updatedRows]);
            return;
        }

        $insertPaymentSql = 'INSERT INTO payments
                             (order_id, amount, payment_date, payment_method, gateway, payment_status, failure_reason, created_at, updated_at)
                             VALUES (?, ?, CASE WHEN ? = \'paid\' THEN SYSDATETIME() ELSE NULL END, ?, ?, ?, NULL, SYSDATETIME(), SYSDATETIME())';
        $insertPaymentBindings = [
            $orderId,
            (float) $orderRow->total_amount,
            $paymentStatus,
            'cash_on_delivery',
            'cash_on_delivery',
            $paymentStatus,
        ];
        $inserted = DB::connection('sqlsrv')->insert($insertPaymentSql, $insertPaymentBindings);
        MsSqlConsoleDebug::push($insertPaymentSql, $insertPaymentBindings, ['inserted' => $inserted]);
    }
}
