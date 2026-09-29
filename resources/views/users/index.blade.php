@extends('layouts.app')

@section('title', 'Users — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1>Users</h1>
            <p class="text-sm text-text/70">System Administrator user management.</p>
        </div>
        <a href="{{ route('users.create') }}" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white no-underline hover:bg-secondary/90">
            New User
        </a>
    </div>

    @php
        $columnMap = [
            'name' => 'Name',
            'email' => 'Email',
            'role' => 'Role',
            'status' => 'Status',
            'created_at' => 'Created',
        ];
        $visible = collect($columns)
            ->filter(fn ($key) => isset($columnMap[$key]))
            ->mapWithKeys(fn ($key) => [$key => $columnMap[$key]])
            ->put('actions', 'Actions')
            ->all();
    @endphp

    <x-data-table
        :columns="$visible"
        :has-rows="$users->count() > 0"
        :paginator="$users"
        :sort="$sort"
        :direction="$direction"
        :sortable="['name', 'email', 'created_at']"
        empty="No users found."
    >
        @foreach ($users as $index => $user)
            <tr @class(['border-t border-black/10', 'bg-page/60' => $index % 2 === 1, 'hover:bg-secondary/5'])>
                @foreach ($columns as $key)
                    @continue(! isset($columnMap[$key]))
                    <td class="px-3 py-3">
                        @switch($key)
                            @case('role')
                                {{ $user->role?->name ?? '—' }}
                                @break
                            @case('status')
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                                @if ($user->isLocked())
                                    <span class="text-error">(Locked)</span>
                                @endif
                                @break
                            @case('created_at')
                                {{ $user->created_at?->toDateString() ?? '—' }}
                                @break
                            @default
                                {{ data_get($user, $key, '—') }}
                        @endswitch
                    </td>
                @endforeach
                <td class="px-3 py-3">
                    <a href="{{ route('users.edit', $user) }}" class="text-sm">Edit</a>
                </td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
