@extends('layouts.app')

@section('title', 'Verify sign-in — '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-md rounded bg-card p-6 shadow-[var(--shadow-card)]">
        <h1 class="mb-2 text-xl font-semibold">Email verification</h1>
        <p class="mb-4 text-sm text-text/70">Enter the 6-digit code we emailed you, or a backup code.</p>

        <form method="post" action="{{ route('mfa.challenge.store') }}" class="space-y-4">
            @csrf
            <label class="block text-sm">
                <span class="mb-1 block font-medium">Code</span>
                <input type="text" name="code" required autocomplete="one-time-code" class="min-h-11 w-full rounded border border-black/20 bg-page px-3 py-2 text-sm" autofocus>
            </label>
            @error('code')
                <p class="text-sm text-error">{{ $message }}</p>
            @enderror
            <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white">Verify</button>
        </form>

        <form method="post" action="{{ route('mfa.challenge.resend') }}" class="mt-3">
            @csrf
            <button type="submit" class="text-sm text-secondary">Resend code</button>
        </form>
    </div>
@endsection
