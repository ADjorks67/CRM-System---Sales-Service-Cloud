@extends('layouts.app')

@section('title', $user->name.' — '.config('app.name'))

@section('content')
    <h1 class="mb-2">{{ $user->name }}</h1>
    <p class="mb-4 text-sm text-text/70">{{ $user->email }} · {{ $user->role?->name ?? 'No role' }}</p>
    <a href="{{ route('users.edit', $user) }}" class="text-sm">Edit</a>
@endsection
