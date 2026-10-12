@extends('layouts.guest')

@section('title', 'Forgot password — '.config('app.name'))

@section('content')
    <h1 class="mb-4">Forgot password</h1>
    <p class="mb-6 text-sm text-muted">We will email a reset link that expires in one hour.</p>

    <form method="post" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <x-form-field name="email" label="Email" type="email" :value="old('email')" required autocomplete="username" />

        <div class="flex flex-wrap items-center gap-3 pt-2">
            <button type="submit" class="crm-btn-primary">
                Send reset link
            </button>
            <x-ui.back :href="route('login')" label="Back to sign in" />
        </div>
    </form>
@endsection
