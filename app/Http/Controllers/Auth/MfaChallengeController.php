<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMfaChallengeRequest;
use App\Models\User;
use App\Services\MfaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MfaChallengeController extends Controller
{
    public function __construct(private readonly MfaService $mfa) {}

    public function create(Request $request): View|RedirectResponse
    {
        if ($this->mfa->pendingUserId() === null) {
            return redirect()->route('login');
        }

        return view('auth.mfa-challenge');
    }

    public function store(StoreMfaChallengeRequest $request): RedirectResponse
    {
        $userId = $this->mfa->pendingUserId();
        if ($userId === null) {
            return redirect()->route('login');
        }

        /** @var User|null $user */
        $user = User::query()->find($userId);
        if ($user === null || ! $user->is_active) {
            $this->mfa->clearChallenge();

            return redirect()->route('login')->withErrors(['email' => 'Unable to complete sign-in.']);
        }

        $code = $request->string('code')->toString();
        $valid = $this->mfa->verifyOtp($code) || $this->mfa->consumeBackupCode($user, $code);

        if (! $valid) {
            return back()->withErrors(['code' => 'Invalid or expired verification code.']);
        }

        $remember = (bool) session(MfaService::SESSION_REMEMBER, false);
        $this->mfa->clearChallenge();

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function resend(Request $request): RedirectResponse
    {
        $userId = $this->mfa->pendingUserId();
        if ($userId === null) {
            return redirect()->route('login');
        }

        $user = User::query()->findOrFail($userId);
        $remember = (bool) session(MfaService::SESSION_REMEMBER, false);
        $this->mfa->issueChallenge($user, $remember);

        return back()->with('success', 'A new verification code was sent.');
    }
}
