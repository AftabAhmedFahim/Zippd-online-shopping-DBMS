<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\ReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReturnController extends Controller
{
    public function __construct(
        private readonly ReturnService $returnService,
        private readonly CartService $cartService
    ) {
    }

    public function create(Request $request, int $orderId, int $productId): View|RedirectResponse
    {
        $userId = (int) $request->user()->user_id;
        $item = $this->returnService->getReturnableItem($userId, $orderId, $productId);

        if ($item === null) {
            return redirect()
                ->route('dashboard.orders')
                ->with('order_error', 'The selected order item could not be found.');
        }

        if (($item['can_return'] ?? false) !== true) {
            return redirect()
                ->route('dashboard.orders')
                ->with('order_error', 'This item is not eligible for a new return request.');
        }

        return view('returns.create', [
            'item' => $item,
            'cartSummary' => $this->cartService->getCartSummary($request),
            'returnReasons' => $this->returnService->getReturnReasons(),
            'refundDestinations' => $this->returnService->getRefundDestinations(),
        ]);
    }

    public function store(Request $request, int $orderId, int $productId): RedirectResponse
    {
        $refundOptions = array_column($this->returnService->getRefundDestinations(), 'value');

        $validated = $request->validate([
            'return_reason' => ['required', 'string', Rule::in($this->returnService->getReturnReasons())],
            'comments' => ['required', 'string', 'max:2000'],
            'refund_to' => ['required', 'string', Rule::in($refundOptions)],
        ]);

        $userId = (int) $request->user()->user_id;
        $result = $this->returnService->submitReturn(
            $userId,
            $orderId,
            $productId,
            (string) $validated['return_reason'],
            trim((string) $validated['comments']),
            (string) $validated['refund_to']
        );

        return redirect()
            ->route('dashboard.orders')
            ->with('order_success', 'Return request submitted successfully. Track it with ID ' . $result['return_code'] . '.');
    }

    public function show(Request $request, int $returnId): View|RedirectResponse
    {
        $userId = (int) $request->user()->user_id;
        $return = $this->returnService->getReturnDetails($userId, $returnId);

        if ($return === null) {
            return redirect()
                ->route('dashboard.orders')
                ->with('order_error', 'The requested return could not be found.');
        }

        return view('returns.show', [
            'return' => $return,
            'cartSummary' => $this->cartService->getCartSummary($request),
        ]);
    }

    public function destroy(Request $request, int $returnId): RedirectResponse
    {
        $userId = (int) $request->user()->user_id;
        $deleted = $this->returnService->cancelReturn($userId, $returnId);

        if (! $deleted) {
            return redirect()
                ->route('dashboard.orders')
                ->with('order_error', 'The return request could not be cancelled.');
        }

        return redirect()
            ->route('dashboard.orders')
            ->with('order_success', 'Return request cancelled successfully.');
    }
}
