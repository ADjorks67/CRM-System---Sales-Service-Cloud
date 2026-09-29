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
            <p class="text-sm text-text/70">
                Subgraph rooted at <strong>{{ $root->name }}</strong>
                (viewing from <a href="{{ route('accounts.show', $account) }}">{{ $account->name }}</a>).
            </p>
        </div>
        <a href="{{ route('accounts.show', $account) }}" class="inline-flex min-h-11 items-center rounded border border-black/20 px-4 py-2 text-sm no-underline">Back to account</a>
    </div>

    @include('accounts.partials.hierarchy', [
        'account' => $account,
        'hierarchyTree' => $hierarchyTree,
        'hierarchyRollUp' => $hierarchyRollUp,
    ])
@endsection
