@extends('layouts.app')

@section('title', 'Edit Task — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <x-ui.back :href="route('tasks.show', $task)" label="Back to Task" />
        <h1 class="mt-2">Edit Task</h1>
    </div>

    <form method="post" action="{{ route('tasks.update', $task) }}" class="max-w-4xl space-y-4 crm-card p-6">
        @csrf
        @method('PUT')
        @include('tasks._form')

        <div class="flex flex-wrap gap-2 pt-2">
            <button type="submit" name="save_action" value="save" class="crm-btn-primary">Save</button>
            <a href="{{ route('tasks.show', $task) }}" class="crm-btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
