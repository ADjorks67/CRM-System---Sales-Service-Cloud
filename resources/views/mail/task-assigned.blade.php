@component('mail::message')
# Task assigned to you

Hello {{ $notifiable->name }},

**{{ $assignedBy->name }}** assigned you a task:

**{{ $task->subject }}**

@if ($task->due_date)
- Due: {{ $task->due_date->toDateString() }}
@endif
- Priority: {{ $task->priority }}
- Status: {{ $task->status }}

@component('mail::button', ['url' => $url])
Open task
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
