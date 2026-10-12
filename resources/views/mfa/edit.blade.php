@extends('layouts.app')

@section('title', 'MFA Settings — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <h1>Multi-factor authentication</h1>
        <p class="text-sm text-muted">Optional email one-time codes (NFR-SEC-002).</p>
    </div>

    <div class="max-w-xl space-y-4 crm-card p-6">
        <p class="text-sm">Status: <strong>{{ $user->mfa_enabled ? 'Enabled' : 'Disabled' }}</strong></p>

        @if (! $user->mfa_enabled)
            <form method="post" action="{{ route('mfa.enable') }}">
                @csrf
                <button type="submit" class="crm-btn-primary">Enable MFA</button>
            </form>
        @else
            <form method="post" action="{{ route('mfa.disable') }}" onsubmit="return confirm('Disable MFA?')">
                @csrf
                <button type="submit" class="crm-btn-secondary">Disable MFA</button>
            </form>
            <form method="post" action="{{ route('mfa.regenerate') }}" class="inline" onsubmit="return confirm('Regenerate backup codes? Previous codes will stop working.')">
                @csrf
                <button type="submit" class="crm-btn-secondary">Regenerate backup codes</button>
            </form>
        @endif

        @if (! empty($backupCodes))
            <div class="rounded border border-warning/40 bg-warning/10 p-4 text-sm">
                <p class="mb-2 font-semibold">Store these backup codes now:</p>
                <ul class="font-mono">
                    @foreach ($backupCodes as $code)
                        <li>{{ $code }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endsection
