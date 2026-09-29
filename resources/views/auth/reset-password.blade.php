@extends('layouts.guest')

@section('title', 'Reset password — '.config('app.name'))

@section('content')
    <h1 class="mb-4">Reset password</h1>
    <p class="mb-6 text-sm text-text/70">Choose a new password (min. 8 characters, upper, lower, and a number). You cannot reuse your last 5 passwords.</p>

    <form method="post" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-form-field name="email" label="Email" type="email" :value="old('email', $email)" required autocomplete="username" />
        <x-form-field name="password" label="New password" type="password" required autocomplete="new-password" />
        <x-form-field name="password_confirmation" label="Confirm password" type="password" required autocomplete="new-password" />

        <div class="flex flex-wrap items-center gap-3 pt-2">
            <button type="submit" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white hover:bg-secondary/90">
                Reset password
            </button>
            <a href="{{ route('login') }}" class="text-sm">Back to sign in</a>
        </div>
    </form>
@endsection
