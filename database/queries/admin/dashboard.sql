-- ============================================
-- Admin Dashboard SQL queries (MSSQL)
-- ============================================

-- 1) KPI totals for dashboard cards
SELECT
    (SELECT COUNT(*) FROM users) AS total_users,
    (SELECT COUNT(*) FROM categories) AS total_categories,
    (SELECT COUNT(*) FROM products) AS total_products,
    (SELECT COUNT(*) FROM orders) AS total_orders;


-- 2) Recent 5 orders for dashboard list
SELECT TOP (5)
    o.order_id,
    o.order_status,
    o.total_amount,
    o.order_date,
    u.full_name AS user_name
FROM orders o
INNER JOIN users u ON u.user_id = o.user_id
ORDER BY o.order_date DESC, o.order_id DESC;


-- 3) Recent 5 users for dashboard list
SELECT TOP (5)
    user_id,
    full_name,
    email,
    created_at
FROM users
ORDER BY created_at DESC, user_id DESC;
