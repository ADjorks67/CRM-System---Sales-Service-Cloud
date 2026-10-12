@extends('layouts.app')

@section('title', 'Hierarchy — '.$account->name.' — '.config('app.name'))

@section('content')
    <x-breadcrumbs :items="[
        ['label' => 'Accounts', 'url' => route('accounts.index')],
        ['label' => $account->name, 'url' => route('accounts.show', $account)],
        ['label' => 'Hierarchy'],
    ]" />

    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1>Account Hierarchy</h1>
            <p class="text-sm text-muted">
                Subgraph rooted at <strong>{{ $root->name }}</strong>
                (viewing from <a href="{{ route('accounts.show', $account) }}">{{ $account->name }}</a>).
            </p>
        </div>
        <x-ui.back :href="route('accounts.show', $account)" label="Back to account" />
    </div>

    @include('accounts.partials.hierarchy', [
        'account' => $account,
        'hierarchyTree' => $hierarchyTree,
        'hierarchyRollUp' => $hierarchyRollUp,
    ])
@endsection
