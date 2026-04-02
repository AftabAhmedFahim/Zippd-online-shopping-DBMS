<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        return $this->renderPage($request, 'admin.dashboard', 'dashboard');
    }

    public function users(Request $request): View
    {
        return $this->renderPage($request, 'admin.users', 'users');
    }

    public function categories(Request $request): View
    {
        return $this->renderPage($request, 'admin.categories', 'categories');
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

    private function renderPage(Request $request, string $view, string $activeTab): View
    {
        $admin = $request->user('admin');
        abort_if($admin === null, 404, 'Admin information not found.');

        return view($view, [
            'adminInfo' => [
                'admin_id' => $admin->admin_id,
                'full_name' => $admin->full_name,
                'email' => $admin->email,
            ],
            'activeTab' => $activeTab,
        ]);
    }
}
