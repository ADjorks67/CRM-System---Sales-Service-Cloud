<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Event::class);

        $showMine = $request->boolean('my_events', true);
        $showPublic = $request->boolean('public_team', true);
        $view = $request->string('view')->toString();
        if (! in_array($view, ['dayGridMonth', 'timeGridWeek', 'timeGridDay', 'listWeek'], true)) {
            $view = 'dayGridMonth';
        }

        $feedQuery = http_build_query([
            'my_events' => $showMine ? '1' : '0',
            'public_team' => $showPublic ? '1' : '0',
        ]);

        return view('calendar.index', [
            'eventsUrl' => route('calendar.feed').'?'.$feedQuery,
            'createUrl' => route('events.create'),
            'rescheduleUrlTemplate' => url('/events/__ID__/reschedule'),
            'showMine' => $showMine,
            'showPublic' => $showPublic,
            'view' => $view,
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Event::class);

        $user = $request->user();
        $showMine = $request->boolean('my_events', true);
        $showPublic = $request->boolean('public_team', true);

        $start = Carbon::parse($request->string('start')->toString() ?: now()->startOfMonth()->toIso8601String());
        $end = Carbon::parse($request->string('end')->toString() ?: now()->endOfMonth()->toIso8601String());

        $query = Event::query()
            ->visibleTo($user)
            ->between($start, $end);

        if ($showMine && ! $showPublic) {
            $query->ownedBy($user);
        } elseif ($showPublic && ! $showMine) {
            $query->publicTeam()->where('owner_id', '!=', $user->id);
        } elseif (! $showMine && ! $showPublic) {
            return response()->json([]);
        }

        $events = $query->orderBy('starts_at')->get()->map->toFullCalendar()->values();

        return response()->json($events);
    }
}
