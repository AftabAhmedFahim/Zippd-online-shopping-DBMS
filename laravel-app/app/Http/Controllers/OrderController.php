<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly CartService $cartService
    ) {
    }

    public function index(Request $request): View
    {
        $userId = (int) $request->user()->user_id;
        $orders = $this->orderService->getOrdersForUser($userId);

        return view('orders', [
            'orders' => $orders,
            'cartSummary' => $this->cartService->getCartSummary($request),
        ]);
    }
}

