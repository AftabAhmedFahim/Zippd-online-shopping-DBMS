<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly OrderService $orderService
    ) {
    }

    public function show(Request $request): RedirectResponse|View
    {
        $cartDetails = $this->cartService->getCartDetails($request);
        if ($cartDetails['unique_count'] === 0) {
            return redirect()->route('products')->with('cart_error', 'Your cart is empty.');
        }

        return view('checkout', [
            'cartDetails' => $cartDetails,
            'cartSummary' => $this->cartService->getCartSummary($request),
            'defaultShippingAddress' => old('shipping_address', (string) ($request->user()->address ?? '')),
        ]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shipping_address' => ['required', 'string', 'max:2000'],
        ]);

        $cartQuantities = $this->cartService->getCartQuantities($request);
        if ($cartQuantities === []) {
            return redirect()->route('products')->with('cart_error', 'Your cart is empty.');
        }

        try {
            $confirmedOrder = $this->orderService->confirmOrder(
                (int) $request->user()->user_id,
                (string) $validated['shipping_address'],
                $cartQuantities
            );
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('checkout.show')
                ->with('cart_error', $exception->getMessage())
                ->withInput();
        } catch (\Throwable $exception) {
            Log::error('Checkout confirmation failed: ' . $exception->getMessage());

            return redirect()
                ->route('checkout.show')
                ->with('cart_error', 'Unable to confirm your order right now. Please try again.')
                ->withInput();
        }

        $this->cartService->clear($request);

        return redirect()
            ->route('dashboard.orders')
            ->with('order_success', sprintf(
                'Order #%d confirmed successfully.',
                (int) $confirmedOrder['order_id']
            ));
    }
}

