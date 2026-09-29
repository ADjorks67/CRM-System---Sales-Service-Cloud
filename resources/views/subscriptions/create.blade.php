@php
    $sub = $subscription ?? null;
    $timeValue = $sub?->time_of_day
        ? \Illuminate\Support\Str::of($sub->time_of_day)->substr(0, 5)->toString()
        : '07:00';
@endphp

@extends('layouts.app')

@section('title', ($sub ? 'Edit' : 'Create').' Subscription — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <p class="text-sm"><a href="{{ route('subscriptions.index') }}" class="text-secondary no-underline">My subscriptions</a></p>
        <h1>{{ $sub ? 'Edit' : 'Subscribe to' }} {{ $savedReport->name }}</h1>
    </div>

    <form method="post" action="{{ $sub ? route('subscriptions.update', $sub) : route('subscriptions.store', $savedReport) }}" class="max-w-xl space-y-4 rounded bg-card p-6 shadow-[var(--shadow-card)]">
        @csrf
        @if ($sub)
            @method('PUT')
        @endif

        <label class="block text-sm">
            <span class="mb-1 block font-medium">Frequency</span>
            <select name="frequency" id="frequency" class="min-h-11 w-full rounded border border-black/20 bg-page px-3 py-2 text-sm" required>
                @foreach (['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('frequency', $sub?->frequency ?? 'daily') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label class="block text-sm" id="dow-wrap">
            <span class="mb-1 block font-medium">Day of week</span>
            <select name="day_of_week" class="min-h-11 w-full rounded border border-black/20 bg-page px-3 py-2 text-sm">
                @foreach (['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $i => $day)
                    <option value="{{ $i }}" @selected((int) old('day_of_week', $sub?->day_of_week ?? 1) === $i)>{{ $day }}</option>
                @endforeach
            </select>
        </label>

        <label class="block text-sm" id="dom-wrap">
            <span class="mb-1 block font-medium">Day of month (1–28)</span>
            <input type="number" name="day_of_month" min="1" max="28" value="{{ old('day_of_month', $sub?->day_of_month ?? 1) }}" class="min-h-11 w-full rounded border border-black/20 bg-page px-3 py-2 text-sm">
        </label>

        <label class="block text-sm">
            <span class="mb-1 block font-medium">Time of day</span>
            <input type="time" name="time_of_day" value="{{ old('time_of_day', $timeValue) }}" required class="min-h-11 w-full rounded border border-black/20 bg-page px-3 py-2 text-sm">
        </label>

        <button type="submit" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white">Save subscription</button>
    </form>
@endsection

@push('scripts')
<script>
(() => {
    const freq = document.getElementById('frequency');
    const dow = document.getElementById('dow-wrap');
    const dom = document.getElementById('dom-wrap');
    const sync = () => {
        dow.style.display = freq.value === 'weekly' ? '' : 'none';
        dom.style.display = freq.value === 'monthly' ? '' : 'none';
    };
    freq.addEventListener('change', sync);
    sync();
})();
</script>
@endpush
