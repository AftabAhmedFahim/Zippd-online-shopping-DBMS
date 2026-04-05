<?php

namespace App\Services;

use App\Support\MsSqlConsoleDebug;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    /**
     * @return array{
     *     totals: array{total_users:int,total_categories:int,total_products:int,total_orders:int},
     *     recent_orders: array<int,array<string,mixed>>,
     *     new_users: array<int,array<string,mixed>>
     * }
     */
    public function getDashboardData(): array
    {
        return [
            'totals' => $this->getTotals(),
            'recent_orders' => $this->getRecentOrders(5),
            'new_users' => $this->getNewUsers(5),
        ];
    }

    /**
     * @return array{total_users:int,total_categories:int,total_products:int,total_orders:int}
     */
    private function getTotals(): array
    {
        $sql = 'SELECT
                    (SELECT COUNT(*) FROM users) AS total_users,
                    (SELECT COUNT(*) FROM categories) AS total_categories,
                    (SELECT COUNT(*) FROM products) AS total_products,
                    (SELECT COUNT(*) FROM orders) AS total_orders';

        $row = DB::connection('sqlsrv')->selectOne($sql);
        $totals = [
            'total_users' => (int) ($row->total_users ?? 0),
            'total_categories' => (int) ($row->total_categories ?? 0),
            'total_products' => (int) ($row->total_products ?? 0),
            'total_orders' => (int) ($row->total_orders ?? 0),
        ];

        MsSqlConsoleDebug::push($sql, [], $totals);

        return $totals;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function getRecentOrders(int $limit): array
    {
        $safeLimit = max(1, min($limit, 20));
        $sql = "SELECT TOP {$safeLimit}
                    o.order_id,
                    o.order_status,
                    o.total_amount,
                    o.order_date,
                    u.full_name AS user_name
                FROM orders o
                INNER JOIN users u ON u.user_id = o.user_id
                ORDER BY o.order_date DESC, o.order_id DESC";

        $rows = DB::connection('sqlsrv')->select($sql);
        $orders = array_map(function ($row): array {
            $order = (array) $row;
            $orderId = (int) ($order['order_id'] ?? 0);
            $amount = (float) ($order['total_amount'] ?? 0);

            return [
                'order_id' => $orderId,
                'order_code' => 'ORD' . str_pad((string) $orderId, 4, '0', STR_PAD_LEFT),
                'user_name' => (string) ($order['user_name'] ?? 'Unknown User'),
                'status' => (string) ($order['order_status'] ?? 'pending'),
                'status_label' => ucfirst((string) ($order['order_status'] ?? 'pending')),
                'amount' => $amount,
                'amount_formatted' => '$' . number_format($amount, 2),
                'order_date' => $order['order_date'] ?? null,
                'time_ago' => $this->toRelativeTime($order['order_date'] ?? null),
            ];
        }, $rows);

        MsSqlConsoleDebug::push($sql, [], $orders);

        return $orders;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function getNewUsers(int $limit): array
    {
        $safeLimit = max(1, min($limit, 20));
        $sql = "SELECT TOP {$safeLimit}
                    user_id,
                    full_name,
                    email,
                    created_at
                FROM users
                ORDER BY created_at DESC, user_id DESC";

        $rows = DB::connection('sqlsrv')->select($sql);
        $users = array_map(function ($row): array {
            $user = (array) $row;
            $userId = (int) ($user['user_id'] ?? 0);
            $name = trim((string) ($user['full_name'] ?? 'User'));

            return [
                'user_id' => $userId,
                'user_code' => 'USR' . str_pad((string) $userId, 4, '0', STR_PAD_LEFT),
                'name' => $name !== '' ? $name : 'User',
                'email' => (string) ($user['email'] ?? '-'),
                'created_at' => $user['created_at'] ?? null,
                'time_ago' => $this->toRelativeTime($user['created_at'] ?? null),
            ];
        }, $rows);

        MsSqlConsoleDebug::push($sql, [], $users);

        return $users;
    }

    private function toRelativeTime(mixed $datetime): string
    {
        if ($datetime === null || trim((string) $datetime) === '') {
            return 'Just now';
        }

        try {
            return Carbon::parse((string) $datetime)->diffForHumans();
        } catch (\Throwable) {
            return 'Just now';
        }
    }
}
