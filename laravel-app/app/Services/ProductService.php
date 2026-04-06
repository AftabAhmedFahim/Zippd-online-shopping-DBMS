<?php

namespace App\Services;

use App\Support\MsSqlConsoleDebug;
use App\Support\ProductImagePath;
use Illuminate\Support\Facades\DB;

class ProductService
{
    private const DEFAULT_PER_PAGE = 12;

    /**
     * @param array<string, mixed> $filters
     * @return array{items: array<int, array<string, mixed>>}
     */
    public function getProductCatalog(array $filters): array
    {
        $userId = isset($filters['user_id']) ? (int) $filters['user_id'] : null;
        $categoryId = isset($filters['category_id']) ? (int) $filters['category_id'] : null;
        $searchTerm = isset($filters['search']) ? trim((string) $filters['search']) : '';
        $searchPattern = $searchTerm !== '' ? '%' . $searchTerm . '%' : null;
        $sort = (string) ($filters['sort'] ?? 'price_asc');
        $perPage = max(1, (int) ($filters['per_page'] ?? self::DEFAULT_PER_PAGE));
        $page = max(1, (int) ($filters['page'] ?? 1));
        $offset = ($page - 1) * $perPage;
        $fetchLimit = $perPage + 1;
        $orderByClause = $this->resolveSortClause($sort);

        $productsSql = "SELECT
                            p.product_id,
                            p.product_name,
                            p.description,
                            p.stock_qty,
                            p.price,
                            cat.category_names,
                            CAST(ISNULL(rev.avg_rating, 0) AS DECIMAL(4,2)) AS average_rating,
                            ISNULL(rev.review_count, 0) AS review_count,
                            ur.rating AS user_rating,
                            ur.review_text AS user_review_text
                        FROM products p
                        LEFT JOIN (
                            SELECT
                                pc.product_id,
                                STRING_AGG(c.category_name, ', ') WITHIN GROUP (ORDER BY c.category_name) AS category_names
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
                        LEFT JOIN reviews ur
                            ON ur.product_id = p.product_id
                           AND ur.user_id = ?
                        WHERE (? IS NULL OR EXISTS (
                            SELECT 1
                            FROM product_categories pcf
                            WHERE pcf.product_id = p.product_id
                              AND pcf.category_id = ?
                        ))
                        AND (? IS NULL OR p.product_name LIKE ?)
                        ORDER BY {$orderByClause}
                        OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";

        $productsBindings = [$userId, $categoryId, $categoryId, $searchPattern, $searchPattern, $offset, $fetchLimit];
        $rows = DB::connection('sqlsrv')->select($productsSql, $productsBindings);

        MsSqlConsoleDebug::push($productsSql, $productsBindings, array_map(static fn ($row) => (array) $row, $rows));

        $items = array_map(function (object $row): array {
            $item = (array) $row;
            $categoryNames = isset($item['category_names']) ? trim((string) $item['category_names']) : '';

            $item['categories'] = $categoryNames === ''
                ? []
                : array_values(array_filter(array_map('trim', explode(',', $categoryNames))));
            $item['image_path'] = ProductImagePath::resolve((string) ($item['product_name'] ?? ''));
            $item['average_rating'] = round((float) ($item['average_rating'] ?? 0), 2);
            $item['review_count'] = (int) ($item['review_count'] ?? 0);
            $item['user_rating'] = isset($item['user_rating']) ? (int) $item['user_rating'] : null;
            $item['user_review_text'] = isset($item['user_review_text']) ? (string) $item['user_review_text'] : null;

            return $item;
        }, $rows);

        return [
            'items' => $items,
        ];
    }

    /**
     * @return array<int, array{category_id:int, category_name:string}>
     */
    public function listCategories(): array
    {
        $sql = 'SELECT category_id, category_name
                FROM categories
                ORDER BY category_name ASC';

        $rows = DB::connection('sqlsrv')->select($sql);

        MsSqlConsoleDebug::push($sql, [], array_map(static fn ($row) => (array) $row, $rows));

        return array_map(static function (object $row): array {
            return [
                'category_id' => (int) $row->category_id,
                'category_name' => (string) $row->category_name,
            ];
        }, $rows);
    }

    /**
     * @return array{
     *     message:string,
     *     review:array{
     *         product_id:int,
     *         average_rating:float,
     *         review_count:int,
     *         user_rating:int,
     *         user_review_text:?string
     *     }
     * }
     */
    public function upsertProductReview(int $productId, int $userId, int $rating, ?string $reviewText): array
    {
        $normalizedText = trim((string) ($reviewText ?? ''));
        $normalizedText = $normalizedText === '' ? null : $normalizedText;

        $connection = DB::connection('sqlsrv');
        $connection->beginTransaction();

        try {
            MsSqlConsoleDebug::push('BEGIN TRANSACTION (PDO)', [], ['executed' => true]);

            $productSql = 'SELECT product_id
                           FROM products
                           WHERE product_id = ?';
            $productBindings = [$productId];
            $product = $connection->selectOne($productSql, $productBindings);
            MsSqlConsoleDebug::push($productSql, $productBindings, $product ? (array) $product : null);

            if ($product === null) {
                throw new \RuntimeException('This product does not exist.');
            }

            $existingReviewSql = 'SELECT review_id
                                  FROM reviews
                                  WHERE user_id = ?
                                    AND product_id = ?';
            $existingReviewBindings = [$userId, $productId];
            $existingReview = $connection->selectOne($existingReviewSql, $existingReviewBindings);
            MsSqlConsoleDebug::push($existingReviewSql, $existingReviewBindings, $existingReview ? (array) $existingReview : null);

            if ($existingReview !== null) {
                $updateSql = 'UPDATE reviews
                              SET rating = ?,
                                  review_text = ?,
                                  review_date = GETDATE(),
                                  updated_at = SYSDATETIME()
                              WHERE review_id = ?';
                $updateBindings = [$rating, $normalizedText, (int) $existingReview->review_id];
                $updatedRows = $connection->update($updateSql, $updateBindings);
                MsSqlConsoleDebug::push($updateSql, $updateBindings, ['affected_rows' => $updatedRows]);
            } else {
                $insertSql = 'INSERT INTO reviews (
                                  user_id,
                                  product_id,
                                  rating,
                                  review_text,
                                  review_date,
                                  created_at,
                                  updated_at
                              )
                              VALUES (?, ?, ?, ?, GETDATE(), SYSDATETIME(), SYSDATETIME())';
                $insertBindings = [$userId, $productId, $rating, $normalizedText];
                $inserted = $connection->insert($insertSql, $insertBindings);
                MsSqlConsoleDebug::push($insertSql, $insertBindings, ['executed' => $inserted]);
            }

            $summarySql = 'SELECT
                               CAST(ISNULL(AVG(CAST(r.rating AS DECIMAL(4,2))), 0) AS DECIMAL(4,2)) AS average_rating,
                               COUNT(*) AS review_count
                           FROM reviews r
                           WHERE r.product_id = ?';
            $summaryBindings = [$productId];
            $summary = $connection->selectOne($summarySql, $summaryBindings);
            MsSqlConsoleDebug::push($summarySql, $summaryBindings, $summary ? (array) $summary : null);

            $connection->commit();
            MsSqlConsoleDebug::push('COMMIT TRANSACTION (PDO)', [], ['executed' => true]);

            return [
                'message' => 'Your review has been saved.',
                'review' => [
                    'product_id' => $productId,
                    'average_rating' => round((float) ($summary->average_rating ?? 0), 2),
                    'review_count' => (int) ($summary->review_count ?? 0),
                    'user_rating' => $rating,
                    'user_review_text' => $normalizedText,
                ],
            ];
        } catch (\Throwable $exception) {
            $connection->rollBack();
            MsSqlConsoleDebug::push('ROLLBACK TRANSACTION (PDO)', [], ['executed' => true]);

            throw $exception;
        }
    }

    /**
     * @return array{
     *     product_id:int,
     *     average_rating:float,
     *     review_count:int,
     *     page:int,
     *     per_page:int,
     *     has_prev:bool,
     *     has_next:bool,
     *     items:array<int, array{
     *         review_id:int,
     *         reviewer_name:string,
     *         rating:int,
     *         review_text:?string,
     *         reviewed_at:string
     *     }>
     * }
     */
    public function getProductReviews(int $productId, int $page = 1, int $perPage = 5): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;
        $fetchLimit = $perPage + 1;
        $connection = DB::connection('sqlsrv');

        $productSql = 'SELECT product_id
                       FROM products
                       WHERE product_id = ?';
        $productBindings = [$productId];
        $product = $connection->selectOne($productSql, $productBindings);
        MsSqlConsoleDebug::push($productSql, $productBindings, $product ? (array) $product : null);

        if ($product === null) {
            throw new \RuntimeException('This product does not exist.');
        }

        $summarySql = 'SELECT
                           CAST(ISNULL(AVG(CAST(r.rating AS DECIMAL(4,2))), 0) AS DECIMAL(4,2)) AS average_rating,
                           COUNT(*) AS review_count
                       FROM reviews r
                       WHERE r.product_id = ?';
        $summaryBindings = [$productId];
        $summary = $connection->selectOne($summarySql, $summaryBindings);
        MsSqlConsoleDebug::push($summarySql, $summaryBindings, $summary ? (array) $summary : null);

        $reviewsSql = "SELECT
                           r.review_id,
                           COALESCE(NULLIF(LTRIM(RTRIM(u.full_name)), ''), 'User') AS reviewer_name,
                           r.rating,
                           NULLIF(LTRIM(RTRIM(ISNULL(r.review_text, ''))), '') AS review_text,
                           CONVERT(VARCHAR(16), ISNULL(r.review_date, r.created_at), 120) AS reviewed_at
                       FROM reviews r
                       INNER JOIN users u ON u.user_id = r.user_id
                       WHERE r.product_id = ?
                       ORDER BY ISNULL(r.review_date, r.created_at) DESC, r.review_id DESC
                       OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";
        $reviewsBindings = [$productId, $offset, $fetchLimit];
        $rows = $connection->select($reviewsSql, $reviewsBindings);
        MsSqlConsoleDebug::push($reviewsSql, $reviewsBindings, array_map(static fn ($row) => (array) $row, $rows));

        $hasNext = count($rows) > $perPage;
        if ($hasNext) {
            $rows = array_slice($rows, 0, $perPage);
        }

        $items = array_map(static function (object $row): array {
            return [
                'review_id' => (int) $row->review_id,
                'reviewer_name' => (string) $row->reviewer_name,
                'rating' => (int) $row->rating,
                'review_text' => isset($row->review_text) ? (string) $row->review_text : null,
                'reviewed_at' => (string) $row->reviewed_at,
            ];
        }, $rows);

        return [
            'product_id' => $productId,
            'average_rating' => round((float) ($summary->average_rating ?? 0), 2),
            'review_count' => (int) ($summary->review_count ?? 0),
            'page' => $page,
            'per_page' => $perPage,
            'has_prev' => $page > 1,
            'has_next' => $hasNext,
            'items' => $items,
        ];
    }

    private function resolveSortClause(string $sort): string
    {
        return match ($sort) {
            'price_desc' => 'p.price DESC, p.product_id DESC',
            default => 'p.price ASC, p.product_id DESC',
        };
    }
}
