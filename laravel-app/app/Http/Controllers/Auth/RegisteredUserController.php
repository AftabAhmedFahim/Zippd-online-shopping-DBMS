<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'gender' => ['required', 'string', 'in:Male,Female,Other'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Register user using MS SQL queries via AuthService
        $result = $this->authService->registerWithMsSQL([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'password' => $request->password,
        ]);

        // If registration failed
        if (!$result) {
            return redirect()->back()->withErrors(['email' => 'Email already exists or registration failed.']);
        }

        // Retrieve the registered user to fire the Registered event
        $user = User::find($result['user_id']);
        event(new Registered($user));

        return redirect(route('dashboard', absolute: false));
    }
}
