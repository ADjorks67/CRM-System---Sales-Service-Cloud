@component('mail::message')
# Daily task digest

Hello {{ $notifiable->name }},

You have **{{ $tasks->count() }}** task(s) due today:

@foreach ($tasks as $task)
- [{{ $task->subject }}]({{ route('tasks.show', $task) }}) @if($task->due_date)— {{ $task->due_date->toDateString() }}@endif
@endforeach

@component('mail::button', ['url' => $url])
View today's tasks
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
