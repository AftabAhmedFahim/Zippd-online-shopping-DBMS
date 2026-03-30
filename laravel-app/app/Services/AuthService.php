<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    protected UserService $userService;
    protected AdminService $adminService;

    public function __construct(UserService $userService, AdminService $adminService)
    {
        $this->userService = $userService;
        $this->adminService = $adminService;
    }

    /**
     * Authenticate user with MS SQL queries (Login)
     * Checks email & password match against database
     *
     * @param string $loginId
     * @param string $password
     * @return string|null Returns "admin" or "user" on success, null on failure
     */
    public function authenticateWithMsSQL(string $loginId, string $password): ?string
    {
        $loginId = trim($loginId);

        if ($loginId === '') {
            return null;
        }

        // Admin login by special numeric admin_id (same login page as users)
        if (ctype_digit($loginId)) {
            $adminId = (int) $loginId;
            $adminData = $this->adminService->findByAdminId($adminId);

            if ($adminData && Hash::check($password, (string) $adminData['password_hash'])) {
                $admin = Admin::find($adminId);

                if ($admin) {
                    Auth::guard('admin')->login($admin);
                    return 'admin';
                }
            }
        }

        // User login by email
        $userData = $this->userService->findByEmail($loginId);
        if (! $userData) {
            return null;
        }

        if (! Hash::check($password, (string) $userData['password_hash'])) {
            return null;
        }

        $user = User::find($userData['user_id']);
        if ($user) {
            Auth::guard('web')->login($user);
            return 'user';
        }

        return null;
    }

    /**
     * Register user with MS SQL queries (Registration)
     * Validates and inserts user data directly into database
     *
     * @param array $userData
     * @return array|null
     */
    public function registerWithMsSQL(array $userData): ?array
    {
        // Check if email already exists
        if ($this->userService->checkEmailExists($userData['email'])) {
            return null; // Email already exists
        }

        // Hash the password
        $userData['password_hash'] = Hash::make($userData['password']);
        unset($userData['password']); // Remove plain password

        // Insert user into database using MS SQL query
        $userId = $this->userService->createUser($userData);

        if (!$userId) {
            return null; // Failed to insert user
        }

        // Retrieve the newly created user and authenticate
        $user = User::find($userId);

        if ($user) {
            Auth::guard('web')->login($user);
            return [
                'success' => true,
                'user_id' => $userId,
                'message' => 'User registered and authenticated successfully'
            ];
        }

        return null;
    }

    /**
     * Logout user
     *
     * @return void
     */
    public function logout(): void
    {
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        }
    }
}
