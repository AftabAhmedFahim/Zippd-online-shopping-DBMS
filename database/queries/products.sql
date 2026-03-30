-- ============================================
-- Product browsing SQL queries for Zippd project
-- ============================================

-- 1) List categories (for filter dropdown)
SELECT category_id, category_name
FROM categories
ORDER BY category_name ASC;

-- 2) Product list with aggregated categories
-- Pagination strategy: OFFSET uses (page - 1) * per_page and FETCH NEXT uses (per_page + 1)
-- to support simple pagination (detecting whether a next page exists) without COUNT(*).
-- Replace {ORDER_BY_CLAUSE} in application code with a safe whitelisted value.
SELECT
    p.product_id,
    p.product_name,
    p.description,
    p.stock_qty,
    p.price,
    STRING_AGG(c.category_name, ', ') WITHIN GROUP (ORDER BY c.category_name) AS category_names
FROM products p
LEFT JOIN product_categories pc ON pc.product_id = p.product_id
LEFT JOIN categories c ON c.category_id = pc.category_id
WHERE (? IS NULL OR EXISTS (
    SELECT 1
    FROM product_categories pcf
    WHERE pcf.product_id = p.product_id
      AND pcf.category_id = ?
))
AND (? IS NULL OR p.product_name LIKE ?)
GROUP BY
    p.product_id,
    p.product_name,
    p.description,
    p.stock_qty,
    p.price
ORDER BY {ORDER_BY_CLAUSE}
OFFSET ? ROWS FETCH NEXT ? ROWS ONLY;

-- 3) Single product lookup by product_id (used by cart add flow)
SELECT product_id, product_name, price, stock_qty
FROM products
WHERE product_id = ?;
