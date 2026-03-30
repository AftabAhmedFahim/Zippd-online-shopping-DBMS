<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Authenticate user with MS SQL queries (Login)
     * Checks email & password match against database
     *
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function authenticateWithMsSQL(string $email, string $password): bool
    {
        // Query the database to find user by email
        $userData = $this->userService->findByEmail($email);

        // If user not found, return false
        if (!$userData) {
            return false;
        }

        // Verify password using Hash::check()
        if (!Hash::check($password, $userData['password_hash'])) {
            return false;
        }

        // User is valid, now retrieve the User model and authenticate
        $user = User::find($userData['user_id']);

        if ($user) {
            Auth::login($user);
            return true;
        }

        return false;
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
            Auth::login($user);
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
        Auth::logout();
    }
}
