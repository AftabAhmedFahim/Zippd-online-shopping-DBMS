-- ============================================
-- Cart SQL queries for Zippd project
-- ============================================

-- 1) Find product for add-to-cart validation
-- Used by: CartService::findProductById($productId)
SELECT product_id, product_name, price, stock_qty
FROM products
WHERE product_id = ?;

-- 2) Fetch cart product rows by dynamic product_id list
-- Used by: CartService::findProductsByIds($productIds)
-- Build placeholders safely in application code, e.g. (?, ?, ?)
SELECT product_id, product_name, price, stock_qty
FROM products
WHERE product_id IN ({PRODUCT_ID_PLACEHOLDERS});

