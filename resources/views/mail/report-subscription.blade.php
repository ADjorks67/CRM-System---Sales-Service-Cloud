<x-mail::message>
# Report subscription

Hello {{ $notifiable->name }},

Your subscribed report **{{ $report?->name ?? 'Report' }}** is attached as CSV.

Frequency: {{ $subscription->frequency }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
