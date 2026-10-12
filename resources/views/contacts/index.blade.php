@extends('layouts.app')

@section('title', 'Contacts — '.config('app.name'))

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3 border-b border-border pb-4">
        <div>
            <h1 class="font-headline">Contacts</h1>
            <p class="text-sm text-muted">Recently viewed contacts (FR-CONT-001).</p>
        </div>
        @can('create', App\Models\Contact::class)
            <a href="{{ route('contacts.create') }}" class="crm-btn-primary">
                New Contact
            </a>
        @endcan
    </div>

    <form method="get" action="{{ route('contacts.index') }}" class="mb-4 flex flex-wrap gap-2">
        <label for="contact-search" class="sr-only">Search contacts</label>
        <input id="contact-search" type="search" name="q" value="{{ request('q') }}" placeholder="Search contacts…" class="crm-input min-w-[14rem] flex-1">
        <button type="submit" class="crm-btn-secondary">Search</button>
    </form>

    @php
        $columnMap = [
            'name' => 'Name',
            'account' => 'Account',
            'title' => 'Title',
            'email' => 'Email',
            'phone' => 'Phone',
            'owner' => 'Owner',
            'updated_at' => 'Last Modified',
        ];
        $visible = collect($columns)
            ->filter(fn ($key) => isset($columnMap[$key]))
            ->mapWithKeys(fn ($key) => [$key => $columnMap[$key]])
            ->all();
        $visible = ['select' => ''] + $visible + ['actions' => 'Actions'];
    @endphp

    <form method="post" action="{{ route('contacts.bulk') }}">
        @csrf
        <div class="mb-3 flex flex-wrap items-end gap-2">
            <x-form-field name="action" label="Bulk action" :options="['change_owner' => 'Change Owner', 'delete' => 'Delete']" class="min-w-[12rem]" />
            <x-form-field name="owner_id" label="New owner" :options="$owners->mapWithKeys(fn ($u) => [$u->id => $u->name])->all()" class="min-w-[12rem]" />
            <button type="submit" class="crm-btn-secondary" onclick="return confirm('Apply bulk action to selected contacts?')">Apply</button>
        </div>

        <x-data-table
            :columns="$visible"
            :has-rows="$contacts->count() > 0"
            :paginator="$contacts"
            :sort="$sort"
            :direction="$direction"
            :sortable="['last_name', 'first_name', 'email', 'updated_at']"
            empty="No contacts yet."
        >
            @foreach ($contacts as $index => $contact)
                <tr @class(['border-t border-border', 'bg-page/80' => $index % 2 === 1, 'hover:bg-page'])>
                    <td class="px-3 py-3">
                        <input type="checkbox" name="ids[]" value="{{ $contact->id }}" class="size-4 rounded border-border" aria-label="Select {{ $contact->displayName() }}">
                    </td>
                    @foreach ($columns as $key)
                        @continue(! isset($columnMap[$key]))
                        <td class="px-3 py-3">
                            @switch($key)
                                @case('name')
                                    <a href="{{ route('contacts.show', $contact) }}" class="font-medium">{{ $contact->displayName() }}</a>
                                    @break
                                @case('account')
                                    @if ($contact->account)
                                        <a href="{{ route('accounts.show', $contact->account) }}">{{ $contact->account->name }}</a>
                                    @else
                                        —
                                    @endif
                                    @break
                                @case('owner')
                                    {{ $contact->owner?->name ?? '—' }}
                                    @break
                                @case('updated_at')
                                    {{ $contact->updated_at?->toDateString() ?? '—' }}
                                    @break
                                @default
                                    {{ data_get($contact, $key) ?: '—' }}
                            @endswitch
                        </td>
                    @endforeach
                    <td class="px-3 py-3">
                        @can('update', $contact)
                            <a href="{{ route('contacts.edit', $contact) }}" class="text-sm">Edit</a>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    </form>
@endsection
