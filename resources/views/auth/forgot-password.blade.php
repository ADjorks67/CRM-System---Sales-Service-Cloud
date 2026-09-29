@extends('layouts.guest')

@section('title', 'Forgot password — '.config('app.name'))

@section('content')
    <h1 class="mb-4">Forgot password</h1>
    <p class="mb-6 text-sm text-text/70">We will email a reset link that expires in one hour.</p>

    <form method="post" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <x-form-field name="email" label="Email" type="email" :value="old('email')" required autocomplete="username" />

        <div class="flex flex-wrap items-center gap-3 pt-2">
            <button type="submit" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white hover:bg-secondary/90">
                Send reset link
            </button>
            <a href="{{ route('login') }}" class="text-sm">Back to sign in</a>
        </div>
    </form>
@endsection
