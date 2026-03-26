<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\View\View;

class ProductController extends Controller
{
    private const PER_PAGE = 12;

    public function __construct(
        private readonly ProductService $productService,
        private readonly CartService $cartService
    ) {
    }

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'category' => ['nullable', 'integer', 'min:1'],
            'search' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'in:price_asc,price_desc'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $filters = [
            'category_id' => isset($validated['category']) ? (int) $validated['category'] : null,
            'search' => trim((string) ($validated['search'] ?? '')),
            'sort' => (string) ($validated['sort'] ?? 'price_asc'),
            'per_page' => self::PER_PAGE,
            'page' => (int) ($validated['page'] ?? 1),
        ];

        $catalog = $this->productService->getProductCatalog($filters);
        $categories = $this->productService->listCategories();
        $paginationQuery = $request->query();
        unset($paginationQuery['page']);

        $products = new Paginator(
            $catalog['items'],
            $filters['per_page'],
            $filters['page'],
            [
                'path' => $request->url(),
                'query' => $paginationQuery,
            ]
        );

        return view('products', [
            'products' => $products,
            'categories' => $categories,
            'selectedCategory' => $filters['category_id'],
            'searchTerm' => $filters['search'],
            'selectedSort' => $filters['sort'],
            'cartSummary' => $this->cartService->getCartSummary($request),
        ]);
    }
}
