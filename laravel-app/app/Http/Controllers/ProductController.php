<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class ProductController extends Controller
{
    private const PER_PAGE = 12;
    private const REVIEWS_PER_PAGE = 5;

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
            'user_id' => isset($request->user()->user_id) ? (int) $request->user()->user_id : null,
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

    public function storeReview(Request $request, int $productId): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'review_text' => ['nullable', 'string', 'max:4000'],
        ]);

        try {
            $result = $this->productService->upsertProductReview(
                $productId,
                (int) $request->user()->user_id,
                (int) $validated['rating'],
                $validated['review_text'] ?? null
            );
        } catch (Throwable $exception) {
            report($exception);
            $message = 'Unable to save your review right now. Please try again.';

            if ($this->shouldReturnJson($request)) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->back()->with('review_error', $message);
        }

        if ($this->shouldReturnJson($request)) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'review' => $result['review'],
            ]);
        }

        return redirect()->back()->with('review_success', $result['message']);
    }

    public function reviews(Request $request, int $productId): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $page = (int) ($validated['page'] ?? 1);

        try {
            $reviews = $this->productService->getProductReviews($productId, $page, self::REVIEWS_PER_PAGE);
        } catch (RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 404);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load reviews right now. Please try again.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    private function shouldReturnJson(Request $request): bool
    {
        return $request->expectsJson() || $request->wantsJson() || $request->ajax();
    }
}
