-- ============================================
-- Admin Categories page SQL queries
-- ============================================

-- 1) Categories listing with product counts
SELECT
    c.category_id,
    c.category_name,
    c.description,
    COUNT(pc.product_id) AS product_count
FROM categories c
LEFT JOIN product_categories pc ON pc.category_id = c.category_id
GROUP BY c.category_id, c.category_name, c.description
ORDER BY c.category_id ASC;


-- 2) Add category
-- Bind: [category_name, description]
INSERT INTO categories (category_name, description, created_at, updated_at)
OUTPUT INSERTED.category_id
VALUES (?, ?, SYSDATETIME(), SYSDATETIME());


-- 3) Update category
-- Bind: [category_name, description, category_id]
UPDATE categories
SET category_name = ?, description = ?, updated_at = SYSDATETIME()
WHERE category_id = ?;


-- 4) Check category exists (used when update affects 0 rows)
-- Bind: [category_id]
SELECT category_id
FROM categories
WHERE category_id = ?;


-- 5) Category details with product count
-- Bind: [category_id]
SELECT
    c.category_id,
    c.category_name,
    c.description,
    COUNT(pc.product_id) AS product_count
FROM categories c
LEFT JOIN product_categories pc ON pc.category_id = c.category_id
WHERE c.category_id = ?
GROUP BY c.category_id, c.category_name, c.description;


-- 6) Total products in one category (for pagination)
-- Bind: [category_id]
SELECT COUNT(*) AS total
FROM products p
INNER JOIN product_categories pc ON pc.product_id = p.product_id
WHERE pc.category_id = ?;


-- 7) Products in one category (paged)
-- Bind: [category_id, offset, per_page]
SELECT
    p.product_id,
    p.product_name,
    p.description,
    p.stock_qty,
    p.price
FROM products p
INNER JOIN product_categories pc ON pc.product_id = p.product_id
WHERE pc.category_id = ?
ORDER BY p.product_id ASC
OFFSET ? ROWS FETCH NEXT ? ROWS ONLY;
