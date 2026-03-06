-- ============================================
-- Authentication SQL queries for Zippd project
-- ============================================

-- 1. Find user by email
-- Used by login and during registration to check for duplicates.
SELECT user_id, full_name, email, password_hash, phone, gender, address, created_at, updated_at 
FROM users 
WHERE email = ?;


-- 2. Check if email exists
-- Prevents duplicate registrations.
SELECT COUNT(*) as count 
FROM users 
WHERE email = ?;


-- 3. Insert new user
-- Used during registration to create a new account.
INSERT INTO users (full_name, email, password_hash, phone, gender, address, created_at) 
VALUES (?, ?, ?, ?, ?, ?, SYSDATETIME());


-- 4. Find user by ID
-- Utility query (used by UserService::findById, session lookup).
SELECT user_id, full_name, email, password_hash, phone, gender, address, created_at, updated_at 
FROM users 
WHERE user_id = ?;