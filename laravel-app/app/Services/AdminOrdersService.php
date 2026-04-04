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
                    ISNULL(items.total_items, 0) AS total_items
                FROM orders o
                INNER JOIN users u ON u.user_id = o.user_id
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
        ];

        $rows = DB::connection('sqlsrv')->select($sql, $bindings);
        $orders = array_map(static fn ($row): array => (array) $row, $rows);
        MsSqlConsoleDebug::push($sql, $bindings, $orders);

        return $orders;
    }

    public function updateOrderStatusForAdmin(int $orderId, string $status): void
    {
        $normalizedStatus = strtolower(trim($status));
        $allowedStatuses = ['pending', 'shipped', 'delivered'];

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
            return;
        }

        $this->assertOrderExists($orderId);
    }

    public function updateOrderForAdmin(int $orderId, string $status, bool $isPaid): void
    {
        $normalizedStatus = strtolower(trim($status));
        $allowedStatuses = ['pending', 'shipped', 'delivered'];

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
}
