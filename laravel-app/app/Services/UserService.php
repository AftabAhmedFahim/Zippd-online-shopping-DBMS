<?php

namespace App\Services;

use App\Support\MsSqlConsoleDebug;
use Illuminate\Support\Facades\DB;

class UserService
{
    /**
     * Find a user by email using raw MS SQL query
     *
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array
    {
        $sql = 'SELECT user_id, full_name, email, password_hash, phone, gender, address, created_at, updated_at 
                FROM users 
                WHERE email = ?';
        $bindings = [$email];

        $user = DB::connection('sqlsrv')
            ->selectOne($sql, $bindings);

        MsSqlConsoleDebug::push($sql, $bindings, $user ? (array) $user : null);

        return $user ? (array) $user : null;
    }

    /**
     * Find a user by ID using raw MS SQL query
     *
     * @param int $userId
     * @return array|null
     */
    public function findById(int $userId): ?array
    {
        $sql = 'SELECT user_id, full_name, email, password_hash, phone, gender, address, created_at, updated_at 
                FROM users 
                WHERE user_id = ?';
        $bindings = [$userId];

        $user = DB::connection('sqlsrv')
            ->selectOne($sql, $bindings);

        MsSqlConsoleDebug::push($sql, $bindings, $user ? (array) $user : null);

        return $user ? (array) $user : null;
    }

    /**
     * Check if email already exists in database
     *
     * @param string $email
     * @return bool
     */
    public function checkEmailExists(string $email): bool
    {
        $sql = 'SELECT COUNT(*) as count FROM users WHERE email = ?';
        $bindings = [$email];

        $result = DB::connection('sqlsrv')
            ->selectOne($sql, $bindings);

        MsSqlConsoleDebug::push($sql, $bindings, $result ? (array) $result : null);

        return $result->count > 0;
    }

    /**
     * Create a new user using raw MS SQL INSERT query
     *
     * @param array $userData
     * @return int|null Returns user_id on success, null on failure
     */
    public function createUser(array $userData): ?int
    {
        try {
            $sql = 'INSERT INTO users (full_name, email, password_hash, phone, gender, address, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, SYSDATETIME())';
            $bindings = [
                $userData['full_name'],
                $userData['email'],
                $userData['password_hash'],
                $userData['phone'] ?? null,
                $userData['gender'] ?? null,
                $userData['address'] ?? null,
            ];

            $result = DB::connection('sqlsrv')
                ->insert($sql, $bindings);

            MsSqlConsoleDebug::push($sql, $bindings, ['inserted' => $result]);

            // Retrieve the newly inserted user's ID
            if ($result) {
                $user = $this->findByEmail($userData['email']);
                return $user['user_id'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            // Log the error or handle as needed
            \Log::error('Error creating user: ' . $e->getMessage());
            return null;
        }
    }

}
