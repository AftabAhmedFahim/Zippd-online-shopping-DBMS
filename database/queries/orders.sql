-- ============================================
-- Order history SQL queries for Zippd project
-- ============================================

-- 1) Get orders for a user (latest first)
-- Used by: OrderService::getOrdersForUser($userId)
SELECT order_id, order_date, order_status, shipping_address, total_amount, is_paid
FROM orders
WHERE user_id = ?
ORDER BY order_date DESC, order_id DESC;

-- 2) Get all order items (with product names) for a user's orders
-- Used by: OrderService::getOrdersForUser($userId)
SELECT
    oi.order_id,
    oi.product_id,
    p.product_name,
    oi.quantity,
    oi.unit_price,
    oi.line_total
FROM order_items oi
INNER JOIN orders o ON o.order_id = oi.order_id
INNER JOIN products p ON p.product_id = oi.product_id
WHERE o.user_id = ?
ORDER BY oi.order_id DESC, p.product_name ASC;

