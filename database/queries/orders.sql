-- ============================================
-- Order history SQL queries for Zippd project
-- ============================================

-- 1) Get orders for a user (latest first)
-- Used by: OrderService::getOrdersForUser($userId)
SELECT
    o.order_id,
    o.order_date,
    o.order_status,
    o.shipping_address,
    o.total_amount,
    o.is_paid,
    p.payment_method,
    p.payment_status
FROM orders o
LEFT JOIN payments p ON p.order_id = o.order_id
WHERE o.user_id = ?
ORDER BY o.order_date DESC, o.order_id DESC;

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

