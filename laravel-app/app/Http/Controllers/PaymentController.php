<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    public function success(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'session_id' => ['required', 'string', 'max:255'],
        ]);
        $sessionId = (string) $validated['session_id'];
        $userId = (int) $request->user()->user_id;

        try {
            $result = $this->paymentService->confirmStripePaymentFromSuccessCallback(
                $sessionId,
                $userId
            );
        } catch (RuntimeException $exception) {
            $fallbackOrderId = $this->resolvePaidOrderFallback($sessionId, $userId);
            if ($fallbackOrderId !== null) {
                return $this->buildPaidRedirect($fallbackOrderId);
            }

            return redirect()
                ->route('dashboard.orders')
                ->with('order_error', $exception->getMessage());
        } catch (\Throwable $exception) {
            Log::error('Stripe success callback failed: ' . $exception->getMessage(), [
                'exception' => $exception,
            ]);

            $fallbackOrderId = $this->resolvePaidOrderFallback($sessionId, $userId);
            if ($fallbackOrderId !== null) {
                return $this->buildPaidRedirect($fallbackOrderId);
            }

            return redirect()
                ->route('dashboard.orders')
                ->with('order_error', 'Payment verification failed. Please refresh your orders after a few seconds.');
        }

        if (!$result['confirmed']) {
            $fallbackOrderId = $this->resolvePaidOrderFallback($sessionId, $userId);
            if ($fallbackOrderId !== null) {
                return $this->buildPaidRedirect($fallbackOrderId);
            }

            return redirect()
                ->route('dashboard.orders')
                ->with(
                    'order_error',
                    sprintf(
                        'Payment for order #%d is still processing. Please refresh shortly.',
                        (int) $result['order_id']
                    )
                );
        }

        return redirect()
            ->route('dashboard.orders')
            ->with(
                'order_success',
                sprintf(
                    'Payment successful. Order #%d is now confirmed.',
                    (int) $result['order_id']
                )
            );
    }

    private function resolvePaidOrderFallback(string $sessionId, int $userId): ?int
    {
        try {
            return $this->paymentService->findPaidOrderIdForUserByStripeSessionId($sessionId, $userId);
        } catch (\Throwable $exception) {
            Log::warning('Unable to resolve paid order fallback from Stripe session.', [
                'session_id' => $sessionId,
                'user_id' => $userId,
                'exception' => $exception,
            ]);

            return null;
        }
    }

    private function buildPaidRedirect(int $orderId): RedirectResponse
    {
        return redirect()
            ->route('dashboard.orders')
            ->with(
                'order_success',
                sprintf(
                    'Payment successful. Order #%d is now confirmed.',
                    $orderId
                )
            );
    }

    public function cancel(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $orderId = isset($validated['order_id']) ? (int) $validated['order_id'] : null;
        if ($orderId !== null) {
            try {
                $this->paymentService->markStripeCheckoutCancelled($orderId, (int) $request->user()->user_id);
            } catch (\Throwable $exception) {
                Log::warning('Stripe checkout cancel handling failed: ' . $exception->getMessage(), [
                    'order_id' => $orderId,
                    'exception' => $exception,
                ]);
            }
        }

        return redirect()
            ->route('dashboard.orders')
            ->with('order_error', 'Online payment was cancelled. Your order is still pending payment.');
    }

    public function webhook(Request $request): JsonResponse
    {
        try {
            $this->paymentService->handleStripeWebhook(
                (string) $request->getContent(),
                (string) $request->header('Stripe-Signature', '')
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'received' => false,
                'message' => $exception->getMessage(),
            ], 400);
        } catch (\Throwable $exception) {
            Log::error('Stripe webhook handling failed: ' . $exception->getMessage(), [
                'exception' => $exception,
            ]);

            return response()->json([
                'received' => false,
                'message' => 'Webhook processing failed.',
            ], 500);
        }

        return response()->json(['received' => true]);
    }
}
