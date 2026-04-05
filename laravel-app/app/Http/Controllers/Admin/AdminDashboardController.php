<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;
use App\Services\AdminService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(
        private readonly AdminService $adminService,
        private readonly AdminDashboardService $adminDashboardService
    )
    {
    }

    public function index(Request $request): View
    {
        $dashboardData = $this->adminDashboardService->getDashboardData();
        $totals = $dashboardData['totals'];

        return $this->renderPage($request, 'admin.dashboard', 'dashboard', [
            'stats' => [
                ['title' => 'Total Users', 'value' => number_format((int) ($totals['total_users'] ?? 0)), 'tone' => 'cyan', 'icon' => 'user'],
                ['title' => 'Total Categories', 'value' => number_format((int) ($totals['total_categories'] ?? 0)), 'tone' => 'green', 'icon' => 'list'],
                ['title' => 'Total Products', 'value' => number_format((int) ($totals['total_products'] ?? 0)), 'tone' => 'blue', 'icon' => 'box'],
                ['title' => 'Total Orders', 'value' => number_format((int) ($totals['total_orders'] ?? 0)), 'tone' => 'rose', 'icon' => 'cart'],
            ],
            'recentOrders' => $dashboardData['recent_orders'],
            'newUsers' => $dashboardData['new_users'],
        ]);
    }

    public function users(Request $request): View
    {
        $initialSearchQuery = trim((string) $request->query('q', ''));

        return $this->renderPage($request, 'admin.users', 'users', [
            'users' => $this->adminService->getUsersForAdminView(),
            'initialSearchQuery' => $initialSearchQuery,
            'deletionUpdates' => $this->adminService->getUserDeletionUpdates(),
        ]);
    }

    public function products(Request $request): View
    {
        return $this->renderPage($request, 'admin.products', 'products');
    }

    public function categories(Request $request): View
    {
        return $this->renderPage($request, 'admin.categories', 'categories');
    }

    public function orders(Request $request): View
    {
        return $this->renderPage($request, 'admin.orders', 'orders');
    }

    public function returns(Request $request): View
    {
        return $this->renderPage($request, 'admin.returns', 'returns');
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
