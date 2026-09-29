<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MfaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MfaSettingsController extends Controller
{
    public function __construct(private readonly MfaService $mfa) {}

    public function edit(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('mfa.edit', [
            'user' => $user,
            'backupCodes' => session('mfa.plain_backup_codes'),
        ]);
    }

    public function enable(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->forceFill(['mfa_enabled' => true])->save();
        $codes = $this->mfa->generateBackupCodes($user);

        return redirect()
            ->route('mfa.edit')
            ->with('success', 'MFA enabled. Store your backup codes now — they will not be shown again.')
            ->with('mfa.plain_backup_codes', $codes);
    }

    public function disable(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->mfa->disable($user);

        return redirect()
            ->route('mfa.edit')
            ->with('success', 'MFA disabled.');
    }

    public function regenerate(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user->mfa_enabled, 403);

        $codes = $this->mfa->generateBackupCodes($user);

        return redirect()
            ->route('mfa.edit')
            ->with('success', 'New backup codes generated. Store them now.')
            ->with('mfa.plain_backup_codes', $codes);
    }

    public function adminDisable(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);
        $this->mfa->disable($user);

        return redirect()
            ->route('users.edit', $user)
            ->with('success', 'MFA disabled for this user.');
    }
}
