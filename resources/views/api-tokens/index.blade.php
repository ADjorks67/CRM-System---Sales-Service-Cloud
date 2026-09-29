@extends('layouts.app')

@section('title', 'API Tokens — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <h1>API Tokens</h1>
        <p class="text-sm text-text/70">Personal Bearer tokens for REST API v1 (SRS §8.3). See <a href="{{ route('api.docs') }}" class="text-secondary">API docs</a>.</p>
    </div>

    @if (! empty($plainToken))
        <div class="mb-4 rounded border border-warning/40 bg-warning/10 p-4 text-sm">
            <p class="mb-1 font-semibold">Copy your token now:</p>
            <code class="break-all">{{ $plainToken }}</code>
        </div>
    @endif

    <form method="post" action="{{ route('api-tokens.store') }}" class="mb-6 flex flex-wrap items-end gap-2 rounded bg-card p-4 shadow-[var(--shadow-card)]">
        @csrf
        <label class="block min-w-[12rem] flex-1 text-sm">
            <span class="mb-1 block font-medium">Name</span>
            <input type="text" name="name" required maxlength="120" class="min-h-11 w-full rounded border border-black/20 bg-page px-3 py-2 text-sm">
        </label>
        <label class="block text-sm">
            <span class="mb-1 block font-medium">Expires (optional)</span>
            <input type="datetime-local" name="expires_at" class="min-h-11 rounded border border-black/20 bg-page px-3 py-2 text-sm">
        </label>
        <button type="submit" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white">Create token</button>
    </form>

    <div class="overflow-x-auto rounded bg-card shadow-[var(--shadow-card)]">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-black/10 bg-black/[0.03]">
                <tr>
                    <th class="px-3 py-2 font-semibold">Name</th>
                    <th class="px-3 py-2 font-semibold">Created</th>
                    <th class="px-3 py-2 font-semibold">Last used</th>
                    <th class="px-3 py-2 font-semibold">Expires</th>
                    <th class="px-3 py-2 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tokens as $token)
                    <tr class="border-b border-black/5">
                        <td class="px-3 py-2">{{ $token->name }}</td>
                        <td class="px-3 py-2">{{ $token->created_at?->toDateTimeString() }}</td>
                        <td class="px-3 py-2">{{ $token->last_used_at?->toDateTimeString() ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $token->expires_at?->toDateTimeString() ?? 'Never' }}</td>
                        <td class="px-3 py-2">
                            <form method="post" action="{{ route('api-tokens.destroy', $token) }}" onsubmit="return confirm('Revoke this token?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-error">Revoke</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td class="px-3 py-4 text-text/70" colspan="5">No tokens yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
