<?php

namespace App\Services;

use App\Support\MsSqlConsoleDebug;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AdminProductsService
{
    /**
     * Get products for admin product management view.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getProductsForAdminView(?string $searchQuery = null): array
    {
        $searchTerm = trim((string) $searchQuery);
        $likeValue = '%' . $searchTerm . '%';

        $sql = "SELECT
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
                ORDER BY p.product_id ASC";
        $bindings = [
            $searchTerm,
            $likeValue,
            $likeValue,
            $likeValue,
            $likeValue,
            $likeValue,
            $likeValue,
        ];

        $rows = DB::connection('sqlsrv')->select($sql, $bindings);
        $products = array_map(static fn ($row): array => (array) $row, $rows);

        MsSqlConsoleDebug::push($sql, $bindings, $products);

        return $products;
    }

    /**
     * @return array<int, array{category_id:int, category_name:string}>
     */
    public function getCategoriesForAdminForm(): array
    {
        $sql = 'SELECT category_id, category_name
                FROM categories
                ORDER BY category_name ASC';

        $rows = DB::connection('sqlsrv')->select($sql);
        $categories = array_map(static function (object $row): array {
            return [
                'category_id' => (int) $row->category_id,
                'category_name' => (string) $row->category_name,
            ];
        }, $rows);

        MsSqlConsoleDebug::push($sql, [], $categories);

        return $categories;
    }

    /**
     * @param array<int, mixed> $categoryIds
     */
    public function createProductForAdmin(
        string $productName,
        ?string $description,
        int $stockQty,
        string $price,
        array $categoryIds
    ): int {
        $validatedCategoryIds = $this->resolveValidCategoryIds($categoryIds);
        $connection = DB::connection('sqlsrv');
        $connection->beginTransaction();

        try {
            MsSqlConsoleDebug::push('BEGIN TRANSACTION (PDO)', [], ['executed' => true]);

            $insertProductSql = 'INSERT INTO products (product_name, description, stock_qty, price, created_at, updated_at)
                                 OUTPUT INSERTED.product_id AS product_id
                                 VALUES (?, ?, ?, ?, SYSDATETIME(), SYSDATETIME())';
            $insertProductBindings = [$productName, $description, $stockQty, $price];
            $productRow = $connection->selectOne($insertProductSql, $insertProductBindings);
            MsSqlConsoleDebug::push($insertProductSql, $insertProductBindings, $productRow ? (array) $productRow : null);

            if ($productRow === null || !isset($productRow->product_id)) {
                throw new RuntimeException('Unable to create product right now.');
            }

            $productId = (int) $productRow->product_id;
            $insertMappingSql = 'INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)';

            foreach ($validatedCategoryIds as $categoryId) {
                $insertMappingBindings = [$productId, $categoryId];
                $connection->insert($insertMappingSql, $insertMappingBindings);
                MsSqlConsoleDebug::push($insertMappingSql, $insertMappingBindings, ['executed' => true]);
            }

            $connection->commit();
            MsSqlConsoleDebug::push('COMMIT TRANSACTION (PDO)', [], ['executed' => true]);

            return $productId;
        } catch (\Throwable $exception) {
            $connection->rollBack();
            MsSqlConsoleDebug::push('ROLLBACK TRANSACTION (PDO)', [], ['executed' => true]);
            throw $exception;
        }
    }

    /**
     * @param array<int, mixed> $categoryIds
     */
    public function updateProductForAdmin(
        int $productId,
        string $productName,
        ?string $description,
        int $stockQty,
        string $price,
        array $categoryIds
    ): void {
        $validatedCategoryIds = $this->resolveValidCategoryIds($categoryIds);

        $existsSql = 'SELECT product_id FROM products WHERE product_id = ?';
        $existsBindings = [$productId];
        $existing = DB::connection('sqlsrv')->selectOne($existsSql, $existsBindings);
        MsSqlConsoleDebug::push($existsSql, $existsBindings, $existing ? (array) $existing : null);

        if ($existing === null) {
            throw new RuntimeException('Product not found.');
        }

        $connection = DB::connection('sqlsrv');
        $connection->beginTransaction();

        try {
            MsSqlConsoleDebug::push('BEGIN TRANSACTION (PDO)', [], ['executed' => true]);

            $updateSql = 'UPDATE products
                          SET product_name = ?, description = ?, stock_qty = ?, price = ?, updated_at = SYSDATETIME()
                          WHERE product_id = ?';
            $updateBindings = [$productName, $description, $stockQty, $price, $productId];
            $affectedRows = $connection->update($updateSql, $updateBindings);
            MsSqlConsoleDebug::push($updateSql, $updateBindings, ['affected_rows' => $affectedRows]);

            if ($affectedRows < 1) {
                throw new RuntimeException('Unable to update this product right now.');
            }

            $deleteMappingsSql = 'DELETE FROM product_categories WHERE product_id = ?';
            $deleteMappingsBindings = [$productId];
            $deletedMappings = $connection->delete($deleteMappingsSql, $deleteMappingsBindings);
            MsSqlConsoleDebug::push($deleteMappingsSql, $deleteMappingsBindings, ['affected_rows' => $deletedMappings]);

            $insertMappingSql = 'INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)';
            foreach ($validatedCategoryIds as $categoryId) {
                $insertMappingBindings = [$productId, $categoryId];
                $connection->insert($insertMappingSql, $insertMappingBindings);
                MsSqlConsoleDebug::push($insertMappingSql, $insertMappingBindings, ['executed' => true]);
            }

            $connection->commit();
            MsSqlConsoleDebug::push('COMMIT TRANSACTION (PDO)', [], ['executed' => true]);
        } catch (\Throwable $exception) {
            $connection->rollBack();
            MsSqlConsoleDebug::push('ROLLBACK TRANSACTION (PDO)', [], ['executed' => true]);
            throw $exception;
        }
    }

    public function deleteProductForAdmin(int $productId): void
    {
        $existsSql = 'SELECT product_id FROM products WHERE product_id = ?';
        $existsBindings = [$productId];
        $existing = DB::connection('sqlsrv')->selectOne($existsSql, $existsBindings);
        MsSqlConsoleDebug::push($existsSql, $existsBindings, $existing ? (array) $existing : null);

        if ($existing === null) {
            throw new RuntimeException('Product not found.');
        }

        $connection = DB::connection('sqlsrv');
        $connection->beginTransaction();

        try {
            MsSqlConsoleDebug::push('BEGIN TRANSACTION (PDO)', [], ['executed' => true]);

            $deleteReturnsSql = 'DELETE FROM returns WHERE product_id = ?';
            $deleteReturnsBindings = [$productId];
            $deletedReturns = $connection->delete($deleteReturnsSql, $deleteReturnsBindings);
            MsSqlConsoleDebug::push($deleteReturnsSql, $deleteReturnsBindings, ['affected_rows' => $deletedReturns]);

            $deleteReviewsSql = 'DELETE FROM reviews WHERE product_id = ?';
            $deleteReviewsBindings = [$productId];
            $deletedReviews = $connection->delete($deleteReviewsSql, $deleteReviewsBindings);
            MsSqlConsoleDebug::push($deleteReviewsSql, $deleteReviewsBindings, ['affected_rows' => $deletedReviews]);

            $deleteProductCategoriesSql = 'DELETE FROM product_categories WHERE product_id = ?';
            $deleteProductCategoriesBindings = [$productId];
            $deletedProductCategories = $connection->delete($deleteProductCategoriesSql, $deleteProductCategoriesBindings);
            MsSqlConsoleDebug::push($deleteProductCategoriesSql, $deleteProductCategoriesBindings, ['affected_rows' => $deletedProductCategories]);

            $deleteOrderItemsSql = 'DELETE FROM order_items WHERE product_id = ?';
            $deleteOrderItemsBindings = [$productId];
            $deletedOrderItems = $connection->delete($deleteOrderItemsSql, $deleteOrderItemsBindings);
            MsSqlConsoleDebug::push($deleteOrderItemsSql, $deleteOrderItemsBindings, ['affected_rows' => $deletedOrderItems]);

            $deleteProductSql = 'DELETE FROM products WHERE product_id = ?';
            $deleteProductBindings = [$productId];
            $deletedProducts = $connection->delete($deleteProductSql, $deleteProductBindings);
            MsSqlConsoleDebug::push($deleteProductSql, $deleteProductBindings, ['affected_rows' => $deletedProducts]);

            if ($deletedProducts < 1) {
                throw new RuntimeException('Unable to delete this product right now.');
            }

            $connection->commit();
            MsSqlConsoleDebug::push('COMMIT TRANSACTION (PDO)', [], ['executed' => true]);
        } catch (\Throwable $exception) {
            $connection->rollBack();
            MsSqlConsoleDebug::push('ROLLBACK TRANSACTION (PDO)', [], ['executed' => true]);
            throw $exception;
        }
    }

    /**
     * @param array<int, mixed> $categoryIds
     * @return array<int, int>
     */
    private function resolveValidCategoryIds(array $categoryIds): array
    {
        $normalizedCategoryIds = array_values(array_filter(
            array_unique(array_map(static fn ($id): int => (int) $id, $categoryIds)),
            static fn (int $id): bool => $id > 0
        ));

        if ($normalizedCategoryIds === []) {
            throw new RuntimeException('Select at least one category.');
        }

        $placeholders = implode(', ', array_fill(0, count($normalizedCategoryIds), '?'));
        $sql = "SELECT category_id
                FROM categories
                WHERE category_id IN ({$placeholders})";
        $rows = DB::connection('sqlsrv')->select($sql, $normalizedCategoryIds);

        $existingCategoryIds = array_values(array_unique(array_map(
            static fn (object $row): int => (int) $row->category_id,
            $rows
        )));

        MsSqlConsoleDebug::push($sql, $normalizedCategoryIds, ['category_ids' => $existingCategoryIds]);

        if (count($existingCategoryIds) !== count($normalizedCategoryIds)) {
            throw new RuntimeException('One or more selected categories are invalid.');
        }

        return $normalizedCategoryIds;
    }
}
