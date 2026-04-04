<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminCategoriesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AdminCategoriesController extends Controller
{
    public function __construct(private readonly AdminCategoriesService $adminCategoriesService)
    {
    }

    public function index(Request $request): View
    {
        $initialSearchQuery = trim((string) $request->query('q', ''));
        $editingCategoryId = max(0, (int) $request->query('edit', 0));

        return $this->renderPage($request, 'admin.categories', 'categories', [
            'categories' => $this->adminCategoriesService->getCategoriesForAdminView(),
            'initialSearchQuery' => $initialSearchQuery,
            'editingCategoryId' => $editingCategoryId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('storeCategory', [
            'category_name' => ['required', 'string', 'max:255', 'unique:categories,category_name'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->adminCategoriesService->createCategory(
            trim((string) $validated['category_name']),
            isset($validated['description']) ? trim((string) $validated['description']) : null
        );

        return redirect()
            ->route('admin.categories')
            ->with('categorySuccess', 'Category added successfully.');
    }

    public function update(Request $request, int $categoryId): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'category_name' => ['required', 'string', 'max:255', 'unique:categories,category_name,' . $categoryId . ',category_id'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.categories', ['edit' => $categoryId])
                ->withErrors($validator, 'updateCategory')
                ->withInput();
        }

        $validated = $validator->validated();

        $updated = $this->adminCategoriesService->updateCategory(
            $categoryId,
            trim((string) $validated['category_name']),
            isset($validated['description']) ? trim((string) $validated['description']) : null
        );

        if (! $updated) {
            return redirect()
                ->route('admin.categories')
                ->with('categoryError', 'Category not found or no changes were made.');
        }

        return redirect()
            ->route('admin.categories')
            ->with('categorySuccess', 'Category updated successfully.');
    }

    public function products(Request $request, int $categoryId): View
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $page = (int) ($validated['page'] ?? 1);
        $perPage = 10;

        $category = $this->adminCategoriesService->getCategoryById($categoryId);
        abort_if($category === null, 404, 'Category not found.');

        $result = $this->adminCategoriesService->getCategoryProductsPaginated($categoryId, $page, $perPage);
        $paginationQuery = $request->query();
        unset($paginationQuery['page']);

        $products = new LengthAwarePaginator(
            $result['items'],
            $result['total'],
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $paginationQuery,
            ]
        );

        return $this->renderPage($request, 'admin.category-products', 'categories', [
            'category' => $category,
            'products' => $products,
        ]);
    }

    private function renderPage(Request $request, string $view, string $activeTab, array $extra = []): View
    {
        $admin = $request->user('admin');
        abort_if($admin === null, 404, 'Admin information not found.');

        return view($view, array_merge([
            'adminInfo' => [
                'admin_id' => $admin->admin_id,
                'full_name' => $admin->full_name,
                'email' => $admin->email,
            ],
            'activeTab' => $activeTab,
        ], $extra));
    }
}
