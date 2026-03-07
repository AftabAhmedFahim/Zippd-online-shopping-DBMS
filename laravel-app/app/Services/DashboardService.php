<?php

namespace App\Services;

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
        $user = DB::connection('sqlsrv')->selectOne(
            'SELECT user_id, full_name, email, phone, gender, address, created_at
             FROM users
             WHERE user_id = ?',
            [$userId]
        );

        return $user ? (array) $user : null;
    }
}
