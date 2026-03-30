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
}
