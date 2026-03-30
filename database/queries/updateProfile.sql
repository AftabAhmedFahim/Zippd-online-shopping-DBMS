-- ============================================
-- Profile update and account deletion queries
-- ============================================

-- 1. Update user profile (without password change)
UPDATE users
SET full_name = ?, phone = ?, address = ?, updated_at = SYSDATETIME()
WHERE user_id = ?;


-- 2. Update user profile (with password change)
UPDATE users
SET full_name = ?, phone = ?, address = ?, password_hash = ?, updated_at = SYSDATETIME()
WHERE user_id = ?;


-- 3. Delete user account
DELETE FROM users
WHERE user_id = ?;
