<?php

namespace App\Http\Requests\Auth;

use App\Services\AuthService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login_id' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials using MS SQL queries.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        // Use AuthService to authenticate with MS SQL queries
        $authService = app(AuthService::class);
        $authenticatedAs = $authService->authenticateWithMsSQL(
            $this->login_id,
            $this->password
        );

        if (! $authenticatedAs) {
            throw ValidationException::withMessages([
                'login_id' => trans('auth.failed'),
            ]);
        }

        $this->attributes->set('authenticated_as', $authenticatedAs);
    }


}
