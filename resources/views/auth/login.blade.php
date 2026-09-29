@extends('layouts.guest')

@section('title', 'Sign in — '.config('app.name'))

@section('content')
    <h1 class="mb-4">Sign in</h1>
    <p class="mb-6 text-sm text-text/70">Use your CRM credentials to continue.</p>

    <form method="post" action="{{ route('login.store') }}" class="space-y-4">
        @csrf

        <x-form-field name="email" label="Email" type="email" :value="old('email')" required autocomplete="username" />

        <x-form-field name="password" label="Password" type="password" required autocomplete="current-password" />

        <label class="flex min-h-11 items-center gap-2 text-sm">
            <input type="checkbox" name="remember" value="1" class="size-4 rounded border-black/20" @checked(old('remember'))>
            Remember me for 30 days
        </label>

        <div class="flex flex-wrap items-center gap-3 pt-2">
            <button type="submit" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white hover:bg-secondary/90">
                Sign in
            </button>
            <a href="{{ route('password.request') }}" class="text-sm">Forgot password?</a>
        </div>
    </form>
@endsection
