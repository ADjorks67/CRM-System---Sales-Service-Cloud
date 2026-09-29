@component('mail::message')
# Task reminder

Hello {{ $notifiable->name }},

Reminder for your task:

**{{ $task->subject }}**

@if ($task->due_date)
Due: {{ $task->due_date->toDateString() }}
@endif

@component('mail::button', ['url' => $url])
Open task
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
