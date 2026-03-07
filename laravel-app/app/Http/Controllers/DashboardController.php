<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService)
    {
    }

    public function index(Request $request): View
    {
        $userId = (int) $request->user()->user_id;
        $userInfo = $this->dashboardService->getUserInformation($userId);

        abort_if($userInfo === null, 404, 'User information not found.');

        return view('dashboard', [
            'userInfo' => $userInfo,
        ]);
    }
}
