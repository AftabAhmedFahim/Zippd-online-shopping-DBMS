-- ============================================
-- Review SQL queries for Zippd project (MSSQL)
-- ============================================

-- 1) Validate product exists before accepting review
SELECT product_id
FROM products
WHERE product_id = ?;

-- 2) Check if current user already reviewed this product
SELECT review_id
FROM reviews
WHERE user_id = ?
  AND product_id = ?;

-- 3) Insert new review (rating required, review_text optional)
INSERT INTO reviews (
    user_id,
    product_id,
    rating,
    review_text,
    review_date,
    created_at,
    updated_at
)
VALUES (?, ?, ?, ?, GETDATE(), SYSDATETIME(), SYSDATETIME());

-- 4) Update existing review by review_id
UPDATE reviews
SET rating = ?,
    review_text = ?,
    review_date = GETDATE(),
    updated_at = SYSDATETIME()
WHERE review_id = ?;

-- 5) Product review summary (average + count)
SELECT
    CAST(ISNULL(AVG(CAST(r.rating AS DECIMAL(4,2))), 0) AS DECIMAL(4,2)) AS average_rating,
    COUNT(*) AS review_count
FROM reviews r
WHERE r.product_id = ?;

-- 6) Catalog-level review enrichments
-- a) All product aggregates (average + review_count)
SELECT
    r.product_id,
    AVG(CAST(r.rating AS DECIMAL(4,2))) AS avg_rating,
    COUNT(*) AS review_count
FROM reviews r
GROUP BY r.product_id;

-- b) Signed-in user's review for each product
SELECT product_id, rating, review_text
FROM reviews
WHERE user_id = ?;

-- 7) Paginated review feed for one product (max 5 per page in app layer)
-- page_offset = (page - 1) * per_page
-- fetch_limit = per_page + 1 (to detect next page)
SELECT
    r.review_id,
    COALESCE(NULLIF(LTRIM(RTRIM(u.full_name)), ''), 'User') AS reviewer_name,
    r.rating,
    NULLIF(LTRIM(RTRIM(ISNULL(r.review_text, ''))), '') AS review_text,
    CONVERT(VARCHAR(16), ISNULL(r.review_date, r.created_at), 120) AS reviewed_at
FROM reviews r
INNER JOIN users u ON u.user_id = r.user_id
WHERE r.product_id = ?
ORDER BY ISNULL(r.review_date, r.created_at) DESC, r.review_id DESC
OFFSET ? ROWS FETCH NEXT ? ROWS ONLY;
