-- ============================================
-- Admin Orders page SQL queries (MSSQL)
-- ============================================

-- 1) Orders directory with customer details + item count
-- Bind: [search, like, like, like, like, like, like, like]
SELECT
    o.order_id,
    o.user_id,
    u.full_name AS user_name,
    u.email AS user_email,
    o.order_date,
    o.order_status,
    o.shipping_address,
    o.total_amount,
    o.is_paid,
    ISNULL(items.total_items, 0) AS total_items
FROM orders o
INNER JOIN users u ON u.user_id = o.user_id
LEFT JOIN (
    SELECT
        oi.order_id,
        SUM(oi.quantity) AS total_items
    FROM order_items oi
    GROUP BY oi.order_id
) AS items ON items.order_id = o.order_id
WHERE (? = ''
    OR CAST(o.order_id AS VARCHAR(20)) LIKE ?
    OR CAST(o.user_id AS VARCHAR(20)) LIKE ?
    OR u.full_name LIKE ?
    OR u.email LIKE ?
    OR o.order_status LIKE ?
    OR o.shipping_address LIKE ?
    OR CASE WHEN o.is_paid = 1 THEN 'paid' ELSE 'unpaid' END LIKE ?)
ORDER BY o.order_date DESC, o.order_id DESC;


-- 2) Update order status
-- Bind: [order_status, order_id]
UPDATE orders
SET order_status = ?, updated_at = SYSDATETIME()
WHERE order_id = ?;


-- 3) Update payment status (paid/unpaid)
-- Bind: [is_paid_bit, order_id]
UPDATE orders
SET is_paid = ?, updated_at = SYSDATETIME()
WHERE order_id = ?;


-- 4) Update order status + payment together (used by edit modal)
-- Bind: [order_status, is_paid_bit, order_id]
UPDATE orders
SET order_status = ?, is_paid = ?, updated_at = SYSDATETIME()
WHERE order_id = ?;


-- 5) Check order exists (used when update affects 0 rows)
-- Bind: [order_id]
SELECT order_id
FROM orders
WHERE order_id = ?;
