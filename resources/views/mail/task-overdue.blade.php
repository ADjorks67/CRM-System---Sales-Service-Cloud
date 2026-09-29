@component('mail::message')
# Overdue tasks

Hello {{ $notifiable->name }},

You have **{{ $tasks->count() }}** overdue task(s):

@foreach ($tasks as $task)
- [{{ $task->subject }}]({{ route('tasks.show', $task) }}) — due {{ $task->due_date?->toDateString() }}
@endforeach

@component('mail::button', ['url' => $url])
View overdue tasks
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
