<?php

namespace App\Services;

use App\Support\MsSqlConsoleDebug;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService
{
    private const DEFAULT_PER_PAGE = 12;

    /**
     * @param array<string, mixed> $filters
     * @return array{items: array<int, array<string, mixed>>}
     */
    public function getProductCatalog(array $filters): array
    {
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
                        ORDER BY {$orderByClause}
                        OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";

        $productsBindings = [$categoryId, $categoryId, $searchPattern, $searchPattern, $offset, $fetchLimit];
        $rows = DB::connection('sqlsrv')->select($productsSql, $productsBindings);

        MsSqlConsoleDebug::push($productsSql, $productsBindings, array_map(static fn ($row) => (array) $row, $rows));

        $items = array_map(function (object $row): array {
            $item = (array) $row;
            $categoryNames = isset($item['category_names']) ? trim((string) $item['category_names']) : '';

            $item['categories'] = $categoryNames === ''
                ? []
                : array_values(array_filter(array_map('trim', explode(',', $categoryNames))));
            $item['image_path'] = $this->resolveProductImagePath((string) ($item['product_name'] ?? ''));

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

    private function resolveSortClause(string $sort): string
    {
        return match ($sort) {
            'price_desc' => 'p.price DESC, p.product_id DESC',
            default => 'p.price ASC, p.product_id DESC',
        };
    }

    private function resolveProductImagePath(string $productName): string
    {
        $slug = Str::slug($productName);
        $candidates = [
            "images/products/{$slug}.jpg",
            "images/products/{$slug}.jpeg",
            "images/products/{$slug}.png",
            "images/products/{$slug}.webp",
        ];

        foreach ($candidates as $candidate) {
            if (file_exists(public_path($candidate))) {
                return $candidate;
            }
        }

        return 'images/products/placeholder.svg';
    }
}
