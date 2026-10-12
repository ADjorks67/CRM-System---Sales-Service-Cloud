@extends('layouts.app')

@section('title', 'Verify sign-in — '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-md crm-card p-6">
        <h1 class="mb-2 text-xl font-semibold">Email verification</h1>
        <p class="mb-4 text-sm text-muted">Enter the 6-digit code we emailed you, or a backup code.</p>

        <form method="post" action="{{ route('mfa.challenge.store') }}" class="space-y-4">
            @csrf
            <label class="block text-sm">
                <span class="mb-1 block font-medium">Code</span>
                <input type="text" name="code" required autocomplete="one-time-code" class="min-h-11 w-full rounded border border-border bg-page px-3 py-2 text-sm" autofocus>
            </label>
            @error('code')
                <p class="text-sm text-error">{{ $message }}</p>
            @enderror
            <button type="submit" class="crm-btn-primary w-full">Verify</button>
        </form>

        <form method="post" action="{{ route('mfa.challenge.resend') }}" class="mt-3">
            @csrf
            <button type="submit" class="text-sm text-secondary">Resend code</button>
        </form>
    </div>
@endsection
