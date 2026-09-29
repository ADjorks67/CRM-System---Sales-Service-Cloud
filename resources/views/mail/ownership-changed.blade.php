@component('mail::message')
# Record ownership changed

Hello {{ $notifiable->name }},

**{{ $changedBy->name }}** assigned **{{ $label }}** ({{ $objectType }}) to you.

@if ($history->notes)
Notes: {{ $history->notes }}
@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent
