-- ============================================
-- Authentication SQL queries for Zippd project
-- ============================================

-- 1. Find active admin by admin_id
-- Used by admin login (special ID + password on same login page).
SELECT admin_id, full_name, email, phone, password_hash, status, created_at, updated_at
FROM admins
WHERE admin_id = ? AND status = 'active';


-- 2. Find user by email
-- Used by user login and during registration to check for duplicates.
SELECT user_id, full_name, email, password_hash, phone, gender, address, created_at, updated_at 
FROM users 
WHERE email = ?;


-- 3. Check if email exists
-- Prevents duplicate registrations.
SELECT COUNT(*) as count 
FROM users 
WHERE email = ?;


-- 4. Insert new user
-- Used during registration to create a new account.
INSERT INTO users (full_name, email, password_hash, phone, gender, address, created_at) 
VALUES (?, ?, ?, ?, ?, ?, SYSDATETIME());


-- 5. Find user by ID
-- Utility query (used by UserService::findById, session lookup).
SELECT user_id, full_name, email, password_hash, phone, gender, address, created_at, updated_at 
FROM users 
WHERE user_id = ?;
