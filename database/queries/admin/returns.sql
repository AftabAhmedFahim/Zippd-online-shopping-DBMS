-- ============================================
-- Admin Returns page SQL queries (MSSQL)
-- ============================================

-- 1) Returns directory with full return details + customer message
-- Bind: [search, like x14]
SELECT
    r.return_id,
    r.return_code,
    r.order_id,
    r.product_id,
    r.user_id,
    u.full_name AS user_name,
    u.email AS user_email,
    p.product_name,
    ISNULL(oi.quantity, 0) AS quantity,
    r.return_reason,
    r.comments,
    r.refund_to,
    r.return_date,
    r.status,
    r.created_at,
    r.updated_at
FROM returns r
INNER JOIN users u ON u.user_id = r.user_id
LEFT JOIN order_items oi ON oi.order_id = r.order_id AND oi.product_id = r.product_id
LEFT JOIN products p ON p.product_id = r.product_id
WHERE (? = ''
    OR CAST(r.return_id AS VARCHAR(20)) LIKE ?
    OR ISNULL(r.return_code, '') LIKE ?
    OR CAST(r.order_id AS VARCHAR(20)) LIKE ?
    OR CAST(r.product_id AS VARCHAR(20)) LIKE ?
    OR CAST(r.user_id AS VARCHAR(20)) LIKE ?
    OR u.full_name LIKE ?
    OR u.email LIKE ?
    OR ISNULL(p.product_name, '') LIKE ?
    OR r.return_reason LIKE ?
    OR ISNULL(r.comments, '') LIKE ?
    OR ISNULL(r.refund_to, '') LIKE ?
    OR r.status LIKE ?
    OR CONVERT(VARCHAR(19), r.return_date, 120) LIKE ?
    OR CONVERT(VARCHAR(19), r.updated_at, 120) LIKE ?)
ORDER BY r.return_date DESC, r.return_id DESC;


-- 2) Update return status (also stores modification time)
-- Bind: [status, return_id]
UPDATE returns
SET status = ?, updated_at = SYSDATETIME()
WHERE return_id = ?;


-- 3) Check return exists (used when update affects 0 rows)
-- Bind: [return_id]
SELECT return_id
FROM returns
WHERE return_id = ?;
