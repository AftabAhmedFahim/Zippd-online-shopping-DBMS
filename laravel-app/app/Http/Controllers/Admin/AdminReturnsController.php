<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminReturnsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class AdminReturnsController extends Controller
{
    public function __construct(private readonly AdminReturnsService $adminReturnsService)
    {
    }

    public function index(Request $request): View
    {
        $admin = $request->user('admin');
        abort_if($admin === null, 404, 'Admin information not found.');

        $initialSearchQuery = trim((string) $request->query('q', ''));

        return view('admin.returns', [
            'adminInfo' => [
                'admin_id' => $admin->admin_id,
                'full_name' => $admin->full_name,
                'email' => $admin->email,
            ],
            'activeTab' => 'returns',
            'returns' => $this->adminReturnsService->getReturnsForAdminView($initialSearchQuery),
            'initialSearchQuery' => $initialSearchQuery,
        ]);
    }

    public function update(Request $request, int $returnId): RedirectResponse
    {
        $validated = $request->validateWithBag('returnUpdate', [
            'status' => ['required', 'string', 'in:pending,in progress,approved,returned successfully,rejected,cancelled'],
            'return_q' => ['nullable', 'string', 'max:255'],
            'edit_return_id' => ['nullable', 'integer'],
        ]);

        try {
            $this->adminReturnsService->updateReturnForAdmin(
                $returnId,
                (string) $validated['status']
            );
        } catch (Throwable $exception) {
            return $this->buildRedirectWithSearchQuery(
                $validated['return_q'] ?? null,
                'admin_returns_error',
                $this->resolveReturnErrorMessage($exception, 'Unable to update this return request right now.')
            )->with('admin_returns_open_edit_id', $returnId);
        }

        return $this->buildRedirectWithSearchQuery(
            $validated['return_q'] ?? null,
            'admin_returns_success',
            'Return request updated successfully.'
        );
    }

    private function buildRedirectWithSearchQuery(?string $searchQuery, string $flashKey, string $flashMessage): RedirectResponse
    {
        $normalizedQuery = trim((string) $searchQuery);
        $params = $normalizedQuery !== '' ? ['q' => $normalizedQuery] : [];

        return redirect()
            ->route('admin.returns', $params)
            ->with($flashKey, $flashMessage);
    }

    private function resolveReturnErrorMessage(Throwable $exception, string $fallback): string
    {
        if ($exception instanceof RuntimeException) {
            return $exception->getMessage();
        }

        \Log::error('Admin returns action failed: ' . $exception->getMessage(), [
            'exception' => $exception,
        ]);

        return $fallback;
    }
}
