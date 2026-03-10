<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(protected UserService $userService)
    {
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:2000'],
            'current_password' => ['required', 'string'],
            'password' => ['nullable', Password::defaults(), 'confirmed'],
        ]);

        $userId = (int) $request->user()->user_id;

        if (!$this->userService->verifyCurrentPassword($userId, $validated['current_password'])) {
            return Redirect::back()
                ->withErrors(['current_password' => 'The current password is incorrect.'])
                ->withInput();
        }

        $profileData = [
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $profileData['password_hash'] = Hash::make($validated['password']);
        }

        $updated = $this->userService->updateUserProfile($userId, $profileData);

        if (!$updated) {
            return Redirect::back()
                ->withErrors(['profile' => 'Unable to update your profile right now. Please try again.'])
                ->withInput();
        }

        $request->user()->refresh();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'string'],
            'delete_confirmation' => ['required', 'string', 'in:Confirm'],
        ]);

        $userId = (int) $request->user()->user_id;

        if (!$this->userService->verifyCurrentPassword($userId, (string) $request->input('password'))) {
            return Redirect::back()->withErrors([
                'password' => 'The current password is incorrect.',
            ], 'userDeletion');
        }

        $deleted = $this->userService->deleteUser($userId);

        if (!$deleted) {
            return Redirect::back()->withErrors([
                'delete' => 'Unable to delete your account. Remove dependent records first and try again.',
            ], 'userDeletion');
        }

        // Preserve debug query logs through session invalidation so they can be shown after redirect.
        $debugEntries = [];
        if (app()->isLocal() && config('app.debug')) {
            $debugEntries = (array) $request->session()->get('mssql_console_debug', []);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if (!empty($debugEntries)) {
            $request->session()->put('mssql_console_debug', $debugEntries);
        }

        return Redirect::to('/');
    }
}
