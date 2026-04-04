<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(private readonly AdminService $adminService)
    {
    }

    public function index(Request $request): View
    {
        return $this->renderPage($request, 'admin.dashboard', 'dashboard');
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
