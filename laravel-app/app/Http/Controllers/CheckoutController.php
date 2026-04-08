<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly OrderService $orderService,
        private readonly PaymentService $paymentService
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
            'payment_method' => ['required', 'string', 'in:cash_on_delivery,online'],
        ]);

        $cartQuantities = $this->cartService->getCartQuantities($request);
        if ($cartQuantities === []) {
            return redirect()->route('products')->with('cart_error', 'Your cart is empty.');
        }

        $createdOrder = null;
        $paymentMethod = (string) $validated['payment_method'];
        $initialOrderStatus = $paymentMethod === 'cash_on_delivery' ? 'confirmed' : 'pending';

        try {
            $createdOrder = $this->orderService->confirmOrder(
                (int) $request->user()->user_id,
                (string) $validated['shipping_address'],
                $cartQuantities,
                $initialOrderStatus
            );

            if ($paymentMethod === 'cash_on_delivery') {
                $this->paymentService->recordCashOnDelivery(
                    (int) $createdOrder['order_id'],
                    (float) $createdOrder['total_amount']
                );

                $this->cartService->clear($request);

                return redirect()
                    ->route('dashboard.orders')
                    ->with('order_success', sprintf(
                        'Order #%d confirmed with Cash on Delivery.',
                        (int) $createdOrder['order_id']
                    ));
            }

            $checkoutUrl = $this->paymentService->createStripeCheckoutSession(
                $createdOrder,
                (int) $request->user()->user_id,
                (string) ($request->user()->email ?? '')
            );

            $this->cartService->clear($request);

            return redirect()->away($checkoutUrl);
        } catch (RuntimeException $exception) {
            if ($createdOrder !== null) {
                $this->cartService->clear($request);

                return redirect()
                    ->route('dashboard.orders')
                    ->with(
                        'order_error',
                        sprintf(
                            'Order #%d was created, but payment could not be completed: %s',
                            (int) $createdOrder['order_id'],
                            $exception->getMessage()
                        )
                    );
            }

            return redirect()
                ->route('checkout.show')
                ->with('cart_error', $exception->getMessage())
                ->withInput();
        } catch (\Throwable $exception) {
            Log::error('Checkout confirmation failed: ' . $exception->getMessage());

            if ($createdOrder !== null) {
                $this->cartService->clear($request);

                return redirect()
                    ->route('dashboard.orders')
                    ->with(
                        'order_error',
                        sprintf(
                            'Order #%d was created, but online payment could not start. Please contact support.',
                            (int) $createdOrder['order_id']
                        )
                    );
            }

            return redirect()
                ->route('checkout.show')
                ->with('cart_error', 'Unable to confirm your order right now. Please try again.')
                ->withInput();
        }
    }
}
