@extends('layouts.app')

@section('title', 'Accounts — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1>Accounts</h1>
            <p class="text-sm text-text/70">Phase 1 placeholder. Full Accounts CRUD arrives in Phase 2 (FR-ACCT-001+).</p>
        </div>
    </div>

    <x-data-table
        :columns="['name' => 'Account Name', 'industry' => 'Industry', 'owner' => 'Owner']"
        :has-rows="false"
        empty="No accounts yet. Create accounts in Phase 2."
    />

    <div class="mt-6">
        <x-related-list title="Related Contacts" empty="Related lists will appear when Accounts and Contacts are implemented." />
    </div>
@endsection
