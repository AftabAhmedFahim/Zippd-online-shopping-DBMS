-- ======================================
-- Return feature SQL queries for Zippd
-- ======================================

-- 1. Schema changes for customer return flow
-- Added to support return tracking code, user comments, and refund destination.
ALTER TABLE returns ADD return_code VARCHAR(20) NULL;
ALTER TABLE returns ADD comments VARCHAR(MAX) NULL;
ALTER TABLE returns ADD refund_to VARCHAR(30) NULL;

UPDATE returns
SET return_code = CONCAT('RET', RIGHT('000000' + CAST(return_id AS VARCHAR(6)), 6))
WHERE return_code IS NULL;

ALTER TABLE returns ALTER COLUMN return_code VARCHAR(20) NOT NULL;
CREATE UNIQUE INDEX ux_returns_return_code ON returns(return_code);
ALTER TABLE returns ADD CONSTRAINT chk_returns_refund_to
CHECK (refund_to IS NULL OR refund_to IN ('bkash', 'nagad', 'voucher'));


-- 2. Find a returnable order item for a user
-- Used before showing the return form and before inserting a new return.
SELECT
    o.order_id,
    o.order_status,
    oi.product_id,
    oi.quantity,
    p.product_name,
    r.return_id,
    r.return_code,
    r.status AS return_status
FROM orders o
INNER JOIN order_items oi ON oi.order_id = o.order_id
INNER JOIN products p ON p.product_id = oi.product_id
LEFT JOIN returns r ON r.order_id = oi.order_id AND r.product_id = oi.product_id
WHERE o.user_id = ?
  AND o.order_id = ?
  AND oi.product_id = ?;


-- 3. Create a new return request
-- Inserts the return and immediately returns the generated return_id.
INSERT INTO returns (order_id, product_id, user_id, return_reason, comments, refund_to, return_date, status, created_at, updated_at)
OUTPUT INSERTED.return_id AS return_id
VALUES (?, ?, ?, ?, ?, ?, SYSDATETIME(), ?, SYSDATETIME(), SYSDATETIME());


-- 4. Assign generated return code after insert
UPDATE returns
SET return_code = ?, updated_at = SYSDATETIME()
WHERE return_id = ?;


-- 5. Fetch return details for a specific user
-- Used by the return status/details page.
SELECT
    r.return_id,
    r.return_code,
    r.order_id,
    r.product_id,
    r.return_reason,
    r.comments,
    r.refund_to,
    r.return_date,
    r.status,
    oi.quantity,
    p.product_name
FROM returns r
INNER JOIN order_items oi ON oi.order_id = r.order_id AND oi.product_id = r.product_id
INNER JOIN products p ON p.product_id = r.product_id
WHERE r.user_id = ?
  AND r.return_id = ?;


-- 6. Cancel a return request
DELETE FROM returns
WHERE return_id = ?
  AND user_id = ?;
