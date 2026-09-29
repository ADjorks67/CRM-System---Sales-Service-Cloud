<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\MfaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(private readonly MfaService $mfa) {}

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $this->ensureIsNotRateLimited($request);

        $user = User::query()->where('email', $request->string('email')->toString())->first();

        if ($user?->isLocked()) {
            throw ValidationException::withMessages([
                'email' => 'This account is temporarily locked after too many failed sign-in attempts. Try again later or contact an administrator.',
            ]);
        }

        if ($user && ! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'This account is inactive.',
            ]);
        }

        $remember = $request->boolean('remember');

        if (! Auth::attempt($request->only('email', 'password'), false)) {
            RateLimiter::hit($this->throttleKey($request), 60);

            $user?->registerFailedLogin();

            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        /** @var User $authenticated */
        $authenticated = Auth::user();
        $authenticated->clearLoginFailures();

        if ($authenticated->mfa_enabled) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $this->mfa->issueChallenge($authenticated, $remember);

            return redirect()->route('mfa.challenge');
        }

        if ($remember) {
            Auth::login($authenticated, true);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function ensureIsNotRateLimited(LoginRequest $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 10)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => "Too many sign-in attempts. Please try again in {$seconds} seconds.",
        ]);
    }

    private function throttleKey(LoginRequest $request): string
    {
        return strtolower($request->string('email')->toString()).'|'.$request->ip();
    }
}
