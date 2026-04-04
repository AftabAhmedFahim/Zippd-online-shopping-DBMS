-- ============================================
-- Admin Products page SQL queries (MSSQL)
-- ============================================

-- 1) Categories for create/update form (many-to-many)
SELECT category_id, category_name
FROM categories
ORDER BY category_name ASC;


-- 2) Products directory (with categories + review summary)
SELECT
    p.product_id,
    p.product_name,
    p.description,
    p.stock_qty,
    p.price,
    cat.category_ids,
    cat.category_names,
    CAST(ISNULL(rev.avg_rating, 0) AS DECIMAL(4,2)) AS average_rating,
    ISNULL(rev.review_count, 0) AS review_count
FROM products p
LEFT JOIN (
    SELECT
        pc.product_id,
        STRING_AGG(CAST(c.category_id AS VARCHAR(20)), ',') WITHIN GROUP (ORDER BY c.category_id) AS category_ids,
        STRING_AGG(c.category_name, ', ') WITHIN GROUP (ORDER BY c.category_id) AS category_names
    FROM product_categories pc
    INNER JOIN categories c ON c.category_id = pc.category_id
    GROUP BY pc.product_id
) AS cat ON cat.product_id = p.product_id
LEFT JOIN (
    SELECT
        r.product_id,
        AVG(CAST(r.rating AS DECIMAL(4,2))) AS avg_rating,
        COUNT(*) AS review_count
    FROM reviews r
    GROUP BY r.product_id
) AS rev ON rev.product_id = p.product_id
WHERE (? = ''
    OR CAST(p.product_id AS VARCHAR(20)) LIKE ?
    OR p.product_name LIKE ?
    OR ISNULL(p.description, '') LIKE ?
    OR ISNULL(cat.category_names, '') LIKE ?
    OR CAST(p.stock_qty AS VARCHAR(20)) LIKE ?
    OR CAST(p.price AS VARCHAR(32)) LIKE ?)
ORDER BY p.product_id ASC;


-- 3) Create product (must also insert at least one row in product_categories)
BEGIN TRANSACTION;

INSERT INTO products (product_name, description, stock_qty, price, created_at, updated_at)
OUTPUT INSERTED.product_id AS product_id
VALUES (?, ?, ?, ?, SYSDATETIME(), SYSDATETIME());

-- Repeat this statement for each selected category_id from form:
INSERT INTO product_categories (product_id, category_id)
VALUES (?, ?);

COMMIT TRANSACTION;
-- ROLLBACK TRANSACTION;


-- 4) Update product core fields + categories (replace mappings)
BEGIN TRANSACTION;

UPDATE products
SET product_name = ?,
    description = ?,
    stock_qty = ?,
    price = ?,
    updated_at = SYSDATETIME()
WHERE product_id = ?;

DELETE FROM product_categories
WHERE product_id = ?;

-- Repeat this statement for each selected category_id from form:
INSERT INTO product_categories (product_id, category_id)
VALUES (?, ?);

COMMIT TRANSACTION;
-- ROLLBACK TRANSACTION;


-- 5) Delete product and related rows (MSSQL transaction)
BEGIN TRANSACTION;

DELETE FROM returns WHERE product_id = ?;
DELETE FROM reviews WHERE product_id = ?;
DELETE FROM product_categories WHERE product_id = ?;
DELETE FROM order_items WHERE product_id = ?;
DELETE FROM products WHERE product_id = ?;

COMMIT TRANSACTION;
-- ROLLBACK TRANSACTION;
