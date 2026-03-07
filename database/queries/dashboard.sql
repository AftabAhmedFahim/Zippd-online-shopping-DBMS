-- ============================================
-- Dashboard SQL queries for Zippd project
-- ============================================

-- 1) Get dashboard user information by user_id
-- Used by: DashboardService::getUserInformation($userId)
SELECT user_id, full_name, email, phone, gender, address, created_at
FROM users
WHERE user_id = ?;
