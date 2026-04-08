<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminOrdersService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class AdminOrdersController extends Controller
{
    public function __construct(private readonly AdminOrdersService $adminOrdersService)
    {
    }

    public function index(Request $request): View
    {
        $admin = $request->user('admin');
        abort_if($admin === null, 404, 'Admin information not found.');

        $initialSearchQuery = trim((string) $request->query('q', ''));

        return view('admin.orders', [
            'adminInfo' => [
                'admin_id' => $admin->admin_id,
                'full_name' => $admin->full_name,
                'email' => $admin->email,
            ],
            'activeTab' => 'orders',
            'orders' => $this->adminOrdersService->getOrdersForAdminView($initialSearchQuery),
            'initialSearchQuery' => $initialSearchQuery,
        ]);
    }

    public function updateStatus(Request $request, int $orderId): RedirectResponse
    {
        $validated = $request->validateWithBag('orderStatusUpdate', [
            'order_status' => ['required', 'string', 'in:pending,confirmed,shipped,delivered'],
            'return_q' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->adminOrdersService->updateOrderStatusForAdmin(
                $orderId,
                (string) $validated['order_status']
            );
        } catch (Throwable $exception) {
            return $this->buildRedirectWithSearchQuery(
                $validated['return_q'] ?? null,
                'admin_orders_error',
                $this->resolveOrderErrorMessage($exception, 'Unable to update the order status right now.')
            );
        }

        return $this->buildRedirectWithSearchQuery(
            $validated['return_q'] ?? null,
            'admin_orders_success',
            'Order status updated successfully.'
        );
    }

    public function update(Request $request, int $orderId): RedirectResponse
    {
        $validated = $request->validateWithBag('orderUpdate', [
            'order_status' => ['required', 'string', 'in:pending,confirmed,shipped,delivered'],
            'is_paid' => ['required', 'in:0,1'],
            'return_q' => ['nullable', 'string', 'max:255'],
            'edit_order_id' => ['nullable', 'integer'],
        ]);

        try {
            $this->adminOrdersService->updateOrderForAdmin(
                $orderId,
                (string) $validated['order_status'],
                ((int) $validated['is_paid']) === 1
            );
        } catch (Throwable $exception) {
            return $this->buildRedirectWithSearchQuery(
                $validated['return_q'] ?? null,
                'admin_orders_error',
                $this->resolveOrderErrorMessage($exception, 'Unable to update this order right now.')
            )->with('admin_orders_open_edit_id', $orderId);
        }

        return $this->buildRedirectWithSearchQuery(
            $validated['return_q'] ?? null,
            'admin_orders_success',
            'Order updated successfully.'
        );
    }

    public function updatePayment(Request $request, int $orderId): RedirectResponse
    {
        $validated = $request->validateWithBag('orderPaymentUpdate', [
            'is_paid' => ['required', 'in:0,1'],
            'return_q' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->adminOrdersService->updateOrderPaymentForAdmin(
                $orderId,
                ((int) $validated['is_paid']) === 1
            );
        } catch (Throwable $exception) {
            return $this->buildRedirectWithSearchQuery(
                $validated['return_q'] ?? null,
                'admin_orders_error',
                $this->resolveOrderErrorMessage($exception, 'Unable to update payment status right now.')
            );
        }

        return $this->buildRedirectWithSearchQuery(
            $validated['return_q'] ?? null,
            'admin_orders_success',
            'Payment status updated successfully.'
        );
    }

    private function buildRedirectWithSearchQuery(?string $searchQuery, string $flashKey, string $flashMessage): RedirectResponse
    {
        $normalizedQuery = trim((string) $searchQuery);
        $params = $normalizedQuery !== '' ? ['q' => $normalizedQuery] : [];

        return redirect()
            ->route('admin.orders', $params)
            ->with($flashKey, $flashMessage);
    }

    private function resolveOrderErrorMessage(Throwable $exception, string $fallback): string
    {
        if ($exception instanceof RuntimeException) {
            return $exception->getMessage();
        }

        \Log::error('Admin orders action failed: ' . $exception->getMessage(), [
            'exception' => $exception,
        ]);

        return $fallback;
    }
}
