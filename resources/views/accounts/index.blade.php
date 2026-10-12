@extends('layouts.app')

@section('title', 'Accounts — '.config('app.name'))

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3 border-b border-border pb-4">
        <div>
            <h1 class="font-headline">Accounts</h1>
            <p class="text-sm text-muted">Recently viewed accounts (FR-ACCT-001).</p>
        </div>
        @can('create', App\Models\Account::class)
            <a href="{{ route('accounts.create') }}" class="crm-btn-primary">
                New Account
            </a>
        @endcan
    </div>

    <form method="get" action="{{ route('accounts.index') }}" class="mb-4 flex flex-wrap gap-2">
        <label for="account-search" class="sr-only">Search accounts</label>
        <input id="account-search" type="search" name="q" value="{{ request('q') }}" placeholder="Search accounts…" class="crm-input min-w-[14rem] flex-1">
        <button type="submit" class="crm-btn-secondary">Search</button>
    </form>

    @php
        $columnMap = [
            'name' => 'Account Name',
            'type' => 'Type',
            'industry' => 'Industry',
            'phone' => 'Phone',
            'website' => 'Website',
            'owner' => 'Owner',
            'updated_at' => 'Last Modified',
        ];
        $visible = collect($columns)
            ->filter(fn ($key) => isset($columnMap[$key]))
            ->mapWithKeys(fn ($key) => [$key => $columnMap[$key]])
            ->all();
        $visible = ['select' => ''] + $visible + ['actions' => 'Actions'];
        $types = \App\Support\PicklistOptions::options('account_type');
        $industries = \App\Support\PicklistOptions::options('industry');
    @endphp

    <form method="post" action="{{ route('accounts.bulk') }}" id="accounts-bulk-form">
        @csrf
        <div class="mb-3 flex flex-wrap items-end gap-2">
            <x-form-field name="action" label="Bulk action" :options="['change_owner' => 'Change Owner', 'delete' => 'Delete']" class="min-w-[12rem]" />
            <x-form-field name="owner_id" label="New owner" :options="$owners->mapWithKeys(fn ($u) => [$u->id => $u->name])->all()" class="min-w-[12rem]" />
            <button type="submit" class="crm-btn-primary" data-confirm="Apply bulk action to selected accounts?">Apply</button>
        </div>

        <x-data-table
            :columns="$visible"
            :has-rows="$accounts->count() > 0"
            :paginator="$accounts"
            :sort="$sort"
            :direction="$direction"
            :sortable="['name', 'type', 'industry', 'updated_at']"
            empty="No accounts yet."
        >
            @foreach ($accounts as $index => $account)
                <tr @class(['border-t border-border', 'bg-page/80' => $index % 2 === 1, 'hover:bg-page'])>
                    <td class="px-3 py-3">
                        <input type="checkbox" name="ids[]" value="{{ $account->id }}" class="size-4 rounded border-border" aria-label="Select {{ $account->name }}">
                    </td>
                    @foreach ($columns as $key)
                        @continue(! isset($columnMap[$key]))
                        <td class="px-3 py-3">
                            @switch($key)
                                @case('name')
                                    <a href="{{ route('accounts.show', $account) }}" class="font-medium">{{ $account->name }}</a>
                                    @break
                                @case('type')
                                    {{ $types[$account->type] ?? ($account->type ?: '—') }}
                                    @break
                                @case('industry')
                                    {{ $industries[$account->industry] ?? ($account->industry ?: '—') }}
                                    @break
                                @case('owner')
                                    {{ $account->owner?->name ?? '—' }}
                                    @break
                                @case('updated_at')
                                    {{ $account->updated_at?->toDateString() ?? '—' }}
                                    @break
                                @default
                                    {{ data_get($account, $key) ?: '—' }}
                            @endswitch
                        </td>
                    @endforeach
                    <td class="px-3 py-3">
                        @can('update', $account)
                            <a href="{{ route('accounts.edit', $account) }}" class="text-sm">Edit</a>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    </form>
@endsection
