<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    public function add(Request $request, int $productId): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);
        $result = $this->cartService->addItem($request, $productId, $quantity);
        $cartSummary = $this->cartService->getCartSummary($request);

        if ($this->shouldReturnJson($request)) {
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'cart_summary' => $cartSummary,
            ], $result['success'] ? 200 : 422);
        }

        if (!$result['success']) {
            return redirect()->back()->with('cart_error', $result['message']);
        }

        return redirect()->back()->with('cart_success', $result['message']);
    }

    public function remove(Request $request, int $productId): RedirectResponse|JsonResponse
    {
        $result = $this->cartService->removeItem($request, $productId);
        $cartSummary = $this->cartService->getCartSummary($request);

        if ($this->shouldReturnJson($request)) {
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'cart_summary' => $cartSummary,
            ], $result['success'] ? 200 : 422);
        }

        if (!$result['success']) {
            return redirect()->back()->with('cart_error', $result['message']);
        }

        return redirect()->back()->with('cart_success', $result['message']);
    }

    private function shouldReturnJson(Request $request): bool
    {
        return $request->expectsJson() || $request->wantsJson() || $request->ajax();
    }
}
