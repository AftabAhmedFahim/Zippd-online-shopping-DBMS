<?php

namespace App\Services;

use App\Support\MsSqlConsoleDebug;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Read user information directly from SQL Server.
     *
     * @param int $userId
     * @return array<string, mixed>|null
     */
    public function getUserInformation(int $userId): ?array
    {
        $sql = 'SELECT user_id, full_name, email, phone, gender, address, created_at
                FROM users
                WHERE user_id = ?';
        $bindings = [$userId];

        $user = DB::connection('sqlsrv')->selectOne($sql, $bindings);

        MsSqlConsoleDebug::push($sql, $bindings, $user ? (array) $user : null);

        return $user ? (array) $user : null;
    }
}
