<?php

namespace App\Services;

use App\Support\MsSqlConsoleDebug;
use Illuminate\Support\Facades\DB;

class AdminService
{
    /**
     * Find an active admin by admin_id using raw MS SQL query.
     *
     * @param int $adminId
     * @return array<string, mixed>|null
     */
    public function findByAdminId(int $adminId): ?array
    {
        $sql = 'SELECT admin_id, full_name, email, phone, password_hash, status, created_at, updated_at
                FROM admins
                WHERE admin_id = ? AND status = ?';
        $bindings = [$adminId, 'active'];

        $admin = DB::connection('sqlsrv')->selectOne($sql, $bindings);

        MsSqlConsoleDebug::push($sql, $bindings, $admin ? (array) $admin : null);

        return $admin ? (array) $admin : null;
    }

    /**
     * Get users for admin view (optionally filtered by a search query).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getUsersForAdminView(?string $searchQuery = null): array
    {
        $baseSql = 'SELECT user_id, full_name, email, phone, gender, address, created_at, updated_at
                    FROM users';
        $bindings = [];

        $searchQuery = trim((string) $searchQuery);
        if ($searchQuery !== '') {
            $baseSql .= ' WHERE full_name LIKE ?
                          OR email LIKE ?
                          OR phone LIKE ?
                          OR address LIKE ?
                          OR CONVERT(VARCHAR(10), created_at, 23) LIKE ?';
            $likeValue = '%' . $searchQuery . '%';
            $bindings = [$likeValue, $likeValue, $likeValue, $likeValue, $likeValue];
        }

        $sql = $baseSql . ' ORDER BY user_id ASC';
        $rows = DB::connection('sqlsrv')->select($sql, $bindings);

        $users = array_map(static fn ($row): array => (array) $row, $rows);
        MsSqlConsoleDebug::push($sql, $bindings, $users);

        return $users;
    }

    /**
     * Get latest user deletion updates captured by SQL trigger.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getUserDeletionUpdates(int $limit = 8): array
    {
        $safeLimit = max(1, min($limit, 50));

        $sql = "SELECT TOP {$safeLimit} notification_id, event_type, title, message, related_user_id, event_at, is_read
                FROM admin_notifications
                WHERE event_type = ?
                ORDER BY event_at DESC, notification_id DESC";
        $bindings = ['user_deleted'];

        try {
            $rows = DB::connection('sqlsrv')->select($sql, $bindings);
            $updates = array_map(static fn ($row): array => (array) $row, $rows);
            MsSqlConsoleDebug::push($sql, $bindings, $updates);

            return $updates;
        } catch (\Throwable $e) {
            // Keep the admin page functional before trigger/table script is applied.
            \Log::warning('admin_notifications query failed: ' . $e->getMessage());
            MsSqlConsoleDebug::push($sql, $bindings, [
                'warning' => 'admin_notifications table/trigger not found yet.',
            ]);

            return [];
        }
    }
}
