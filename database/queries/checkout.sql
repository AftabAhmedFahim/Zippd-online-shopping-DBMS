-- ============================================
-- Checkout SQL queries for Zippd project
-- ============================================

-- Transaction boundary note:
-- The application (Laravel OrderService) starts/commits/rolls back the transaction.
-- This file intentionally stores only the DML/SELECT statements used inside that transaction.

-- 1) Re-check product stock at confirm-time
-- Used by: OrderService::confirmOrder(...)
SELECT product_id, product_name, price, stock_qty
FROM products
WHERE product_id = ?;

-- 2) Create order and return generated order_id
-- Used by: OrderService::confirmOrder(...)
INSERT INTO orders (user_id, order_date, order_status, shipping_address, total_amount, is_paid, created_at, updated_at)
OUTPUT INSERTED.order_id AS order_id
VALUES (?, SYSDATETIME(), ?, ?, ?, 0, SYSDATETIME(), SYSDATETIME());

-- 3) Insert order item line
-- Used by: OrderService::confirmOrder(...)
INSERT INTO order_items (order_id, product_id, quantity, unit_price, line_total)
VALUES (?, ?, ?, ?, ?);

-- 4) Decrease stock atomically with safety guard
-- Used by: OrderService::confirmOrder(...)
UPDATE products
SET stock_qty = stock_qty - ?, updated_at = SYSDATETIME()
WHERE product_id = ? AND stock_qty >= ?;
