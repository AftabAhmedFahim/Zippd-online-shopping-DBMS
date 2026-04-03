<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminProductsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class AdminProductsController extends Controller
{
    public function __construct(private readonly AdminProductsService $adminProductsService)
    {
    }

    public function index(Request $request): View
    {
        $admin = $request->user('admin');
        abort_if($admin === null, 404, 'Admin information not found.');

        $initialSearchQuery = trim((string) $request->query('q', ''));

        return view('admin.products', [
            'adminInfo' => [
                'admin_id' => $admin->admin_id,
                'full_name' => $admin->full_name,
                'email' => $admin->email,
            ],
            'activeTab' => 'products',
            'products' => $this->adminProductsService->getProductsForAdminView($initialSearchQuery),
            'allCategories' => $this->adminProductsService->getCategoriesForAdminForm(),
            'initialSearchQuery' => $initialSearchQuery,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('productCreate', [
            'product_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:4000'],
            'stock_qty' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'decimal:0,2', 'gte:0'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        try {
            $productId = $this->adminProductsService->createProductForAdmin(
                trim((string) $validated['product_name']),
                $this->normalizeNullableText($validated['description'] ?? null),
                (int) $validated['stock_qty'],
                $this->normalizeMoneyValue($validated['price']),
                $validated['category_ids']
            );
        } catch (Throwable $exception) {
            return redirect()
                ->route('admin.products')
                ->withInput()
                ->with('admin_products_error', $this->resolveProductErrorMessage($exception, 'Unable to add this product right now.'));
        }

        return redirect()
            ->route('admin.products')
            ->with('admin_products_success', "Product created successfully (ID: {$productId}).");
    }

    public function update(Request $request, int $productId): RedirectResponse
    {
        $validated = $request->validateWithBag('productUpdate', [
            'product_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:4000'],
            'stock_qty' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'decimal:0,2', 'gte:0'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['required', 'integer', 'distinct'],
            'edit_product_id' => ['nullable', 'integer'],
        ]);

        try {
            $this->adminProductsService->updateProductForAdmin(
                $productId,
                trim((string) $validated['product_name']),
                $this->normalizeNullableText($validated['description'] ?? null),
                (int) $validated['stock_qty'],
                $this->normalizeMoneyValue($validated['price']),
                $validated['category_ids']
            );
        } catch (Throwable $exception) {
            return redirect()
                ->route('admin.products')
                ->withInput()
                ->with('admin_products_error', $this->resolveProductErrorMessage($exception, 'Unable to update this product right now.'))
                ->with('admin_products_open_edit_id', $productId);
        }

        return redirect()
            ->route('admin.products')
            ->with('admin_products_success', 'Product updated successfully.');
    }

    public function destroy(int $productId): RedirectResponse
    {
        try {
            $this->adminProductsService->deleteProductForAdmin($productId);
        } catch (Throwable $exception) {
            return redirect()
                ->route('admin.products')
                ->with('admin_products_error', $this->resolveProductErrorMessage($exception, 'Unable to delete this product right now.'));
        }

        return redirect()
            ->route('admin.products')
            ->with('admin_products_success', 'Product deleted successfully.');
    }

    private function normalizeNullableText(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function normalizeMoneyValue(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    private function resolveProductErrorMessage(Throwable $exception, string $fallback): string
    {
        if ($exception instanceof RuntimeException) {
            return $exception->getMessage();
        }

        \Log::error('Admin products action failed: ' . $exception->getMessage(), [
            'exception' => $exception,
        ]);

        return $fallback;
    }
}
