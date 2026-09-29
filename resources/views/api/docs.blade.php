@extends('layouts.app')

@section('title', 'API Documentation — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <h1>REST API v1</h1>
        <p class="text-sm text-text/70">OpenAPI reference for Leads, Accounts, Contacts, Opportunities, and Cases (SRS §8.3).</p>
    </div>

    <div class="space-y-4 rounded bg-card p-6 text-sm shadow-[var(--shadow-card)]">
        <p>Authenticate with <code>Authorization: Bearer &lt;token&gt;</code>. Create tokens at <a href="{{ route('api-tokens.index') }}" class="text-secondary">API Tokens</a>.</p>
        <p>Base path: <code>/api/v1</code></p>
        <p>Rate limit: 60 requests per minute per token/IP.</p>
        <p>Pagination: <code>per_page</code> max 200. Filter: <code>filter[field]=value</code>. Sort: <code>sort</code> + <code>direction</code>.</p>
        <p><a href="{{ route('api.docs.openapi') }}" class="text-secondary" target="_blank" rel="noopener">Download openapi.yaml</a></p>
        <pre class="overflow-x-auto rounded bg-page p-3 text-xs">{{ file_get_contents(base_path('docs/openapi.yaml')) }}</pre>
    </div>
@endsection
