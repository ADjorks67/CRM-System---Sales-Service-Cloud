@extends('layouts.app')

@section('title', 'Privacy (GDPR) — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <nav class="text-sm text-text/70" aria-label="Breadcrumb">
            <ol class="flex flex-wrap gap-1">
                <li><a href="{{ route('home') }}" class="text-secondary no-underline hover:underline">Home</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('users.index') }}" class="text-secondary no-underline hover:underline">Users</a></li>
                <li aria-hidden="true">/</li>
                <li class="text-text">Privacy (GDPR)</li>
            </ol>
        </nav>
        <h1 class="mt-2">Privacy — data subject tools</h1>
        <p class="text-sm text-text/70">NFR-SEC-005: admin-only export (portability) and anonymize (erasure). Hard deletes are avoided when related CRM history must remain.</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded border border-black/10 bg-card p-4" aria-labelledby="gdpr-export-heading">
            <h2 id="gdpr-export-heading" class="text-lg font-semibold">Export personal data</h2>
            <p class="mt-1 text-sm text-text/70">Download a JSON file for the selected Contact, Lead, or User.</p>
            <form method="post" action="{{ route('gdpr.export') }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label for="export_subject_type" class="block text-sm font-medium">Subject type</label>
                    <select id="export_subject_type" name="subject_type" required class="mt-1 w-full rounded border border-black/20 px-3 py-2 text-sm">
                        <option value="contact" @selected(old('subject_type') === 'contact')>Contact</option>
                        <option value="lead" @selected(old('subject_type') === 'lead')>Lead</option>
                        <option value="user" @selected(old('subject_type') === 'user')>User</option>
                    </select>
                </div>
                <div>
                    <label for="export_subject_id" class="block text-sm font-medium">Subject ID</label>
                    <input id="export_subject_id" type="number" name="subject_id" min="1" required value="{{ old('subject_id') }}" class="mt-1 w-full rounded border border-black/20 px-3 py-2 text-sm">
                </div>
                <button type="submit" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white hover:bg-secondary/90">
                    Download JSON
                </button>
            </form>
        </section>

        <section class="rounded border border-black/10 bg-card p-4" aria-labelledby="gdpr-erase-heading">
            <h2 id="gdpr-erase-heading" class="text-lg font-semibold">Anonymize personal data</h2>
            <p class="mt-1 text-sm text-text/70">Redacts PII in place. This cannot be undone. You cannot anonymize your own user account.</p>
            <form method="post" action="{{ route('gdpr.anonymize') }}" class="mt-4 space-y-3" onsubmit="return confirm('Anonymize this subject? PII will be permanently redacted.');">
                @csrf
                <div>
                    <label for="erase_subject_type" class="block text-sm font-medium">Subject type</label>
                    <select id="erase_subject_type" name="subject_type" required class="mt-1 w-full rounded border border-black/20 px-3 py-2 text-sm">
                        <option value="contact">Contact</option>
                        <option value="lead">Lead</option>
                        <option value="user">User</option>
                    </select>
                </div>
                <div>
                    <label for="erase_subject_id" class="block text-sm font-medium">Subject ID</label>
                    <input id="erase_subject_id" type="number" name="subject_id" min="1" required class="mt-1 w-full rounded border border-black/20 px-3 py-2 text-sm">
                </div>
                <button type="submit" class="inline-flex min-h-11 items-center rounded bg-error px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                    Anonymize subject
                </button>
            </form>
        </section>
    </div>
@endsection
