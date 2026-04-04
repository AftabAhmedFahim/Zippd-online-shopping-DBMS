<?php

namespace App\Services;

use App\Support\MsSqlConsoleDebug;
use Illuminate\Support\Facades\DB;

class AdminCategoriesService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getCategoriesForAdminView(): array
    {
        $sql = 'SELECT
                    c.category_id,
                    c.category_name,
                    c.description,
                    COUNT(pc.product_id) AS product_count
                FROM categories c
                LEFT JOIN product_categories pc ON pc.category_id = c.category_id
                GROUP BY c.category_id, c.category_name, c.description
                ORDER BY c.category_id ASC';

        $rows = DB::connection('sqlsrv')->select($sql);
        $categories = array_map(static fn ($row): array => (array) $row, $rows);
        MsSqlConsoleDebug::push($sql, [], $categories);

        return $categories;
    }

    public function createCategory(string $categoryName, ?string $description = null): int
    {
        $normalizedDescription = trim((string) $description);
        $descriptionValue = $normalizedDescription !== '' ? $normalizedDescription : null;

        $sql = 'INSERT INTO categories (category_name, description, created_at, updated_at)
                OUTPUT INSERTED.category_id
                VALUES (?, ?, SYSDATETIME(), SYSDATETIME())';
        $bindings = [trim($categoryName), $descriptionValue];
        $row = DB::connection('sqlsrv')->selectOne($sql, $bindings);

        $categoryId = (int) ($row->category_id ?? 0);
        MsSqlConsoleDebug::push($sql, $bindings, ['category_id' => $categoryId]);

        return $categoryId;
    }

    public function updateCategory(int $categoryId, string $categoryName, ?string $description = null): bool
    {
        $normalizedDescription = trim((string) $description);
        $descriptionValue = $normalizedDescription !== '' ? $normalizedDescription : null;

        $sql = 'UPDATE categories
                SET category_name = ?, description = ?, updated_at = SYSDATETIME()
                WHERE category_id = ?';
        $bindings = [trim($categoryName), $descriptionValue, $categoryId];
        $affectedRows = DB::connection('sqlsrv')->affectingStatement($sql, $bindings);

        MsSqlConsoleDebug::push($sql, $bindings, ['affected_rows' => $affectedRows]);

        if ($affectedRows > 0) {
            return true;
        }

        $existsSql = 'SELECT category_id
                      FROM categories
                      WHERE category_id = ?';
        $existsBindings = [$categoryId];
        $existingRow = DB::connection('sqlsrv')->selectOne($existsSql, $existsBindings);

        MsSqlConsoleDebug::push($existsSql, $existsBindings, $existingRow ? (array) $existingRow : null);

        return $existingRow !== null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getCategoryById(int $categoryId): ?array
    {
        $sql = 'SELECT
                    c.category_id,
                    c.category_name,
                    c.description,
                    COUNT(pc.product_id) AS product_count
                FROM categories c
                LEFT JOIN product_categories pc ON pc.category_id = c.category_id
                WHERE c.category_id = ?
                GROUP BY c.category_id, c.category_name, c.description';
        $bindings = [$categoryId];

        $row = DB::connection('sqlsrv')->selectOne($sql, $bindings);
        $category = $row ? (array) $row : null;
        MsSqlConsoleDebug::push($sql, $bindings, $category);

        return $category;
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function getCategoryProductsPaginated(int $categoryId, int $page = 1, int $perPage = 10): array
    {
        $safePage = max(1, $page);
        $safePerPage = max(1, $perPage);
        $offset = ($safePage - 1) * $safePerPage;

        $totalSql = 'SELECT COUNT(*) AS total
                     FROM products p
                     INNER JOIN product_categories pc ON pc.product_id = p.product_id
                     WHERE pc.category_id = ?';
        $totalBindings = [$categoryId];
        $totalRow = DB::connection('sqlsrv')->selectOne($totalSql, $totalBindings);
        $total = (int) ($totalRow->total ?? 0);
        MsSqlConsoleDebug::push($totalSql, $totalBindings, ['total' => $total]);

        $productsSql = 'SELECT
                            p.product_id,
                            p.product_name,
                            p.description,
                            p.stock_qty,
                            p.price
                        FROM products p
                        INNER JOIN product_categories pc ON pc.product_id = p.product_id
                        WHERE pc.category_id = ?
                        ORDER BY p.product_id ASC
                        OFFSET ? ROWS FETCH NEXT ? ROWS ONLY';
        $productsBindings = [$categoryId, $offset, $safePerPage];
        $rows = DB::connection('sqlsrv')->select($productsSql, $productsBindings);

        $items = array_map(static fn ($row): array => (array) $row, $rows);
        MsSqlConsoleDebug::push($productsSql, $productsBindings, $items);

        return [
            'items' => $items,
            'total' => $total,
        ];
    }
}
