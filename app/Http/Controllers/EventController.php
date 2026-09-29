<?php

namespace App\Http\Controllers;

use App\Http\Requests\RescheduleEventRequest;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\User;
use App\Support\PicklistOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', Event::class);

        return redirect()->route('calendar.index');
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Event::class);

        return view('events.create', $this->formLookups($request));
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $data = $this->eventPayload($request->validated());
        $data['owner_id'] = $data['owner_id'] ?? $request->user()->id;

        $event = Event::query()->create($data);

        if ($request->input('save_action') === 'save_new') {
            return redirect()
                ->route('events.create')
                ->with('status', 'Event created. Add another.');
        }

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'Event created.');
    }

    public function show(Event $event): View
    {
        $this->authorize('view', $event);

        $event->load(['owner', 'related', 'nameContact']);

        return view('events.show', [
            'event' => $event,
            'showAsOptions' => PicklistOptions::options('event_show_as'),
        ]);
    }

    public function edit(Request $request, Event $event): View
    {
        $this->authorize('update', $event);

        return view('events.edit', array_merge(
            $this->formLookups($request),
            ['event' => $event->load(['owner', 'related', 'nameContact'])],
        ));
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $event->update($this->eventPayload($request->validated()));

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'Event updated.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        $event->delete();

        return redirect()
            ->route('calendar.index')
            ->with('status', 'Event deleted.');
    }

    public function reschedule(RescheduleEventRequest $request, Event $event): JsonResponse
    {
        $validated = $request->validated();

        $event->update([
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'is_all_day' => $validated['is_all_day'] ?? $event->is_all_day,
        ]);

        return response()->json([
            'ok' => true,
            'event' => $event->fresh()->toFullCalendar(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function eventPayload(array $validated): array
    {
        unset($validated['save_action']);

        if (empty($validated['related_type']) || empty($validated['related_id'])) {
            $validated['related_type'] = null;
            $validated['related_id'] = null;
        }

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    private function formLookups(Request $request): array
    {
        $user = $request->user();

        return [
            'owners' => User::query()->orderBy('name')->get(['id', 'name']),
            'showAsOptions' => PicklistOptions::options('event_show_as'),
            'accounts' => Account::query()->visibleTo($user)->orderBy('name')->limit(200)->get(['id', 'name']),
            'contacts' => Contact::query()->visibleTo($user)->orderBy('last_name')->limit(200)->get(['id', 'first_name', 'last_name']),
            'leads' => Lead::query()->visibleTo($user)->orderBy('last_name')->limit(200)->get(['id', 'first_name', 'last_name', 'company']),
            'opportunities' => Opportunity::query()->visibleTo($user)->notArchived()->orderBy('name')->limit(200)->get(['id', 'name']),
            'relatedTypes' => [
                'account' => 'Account',
                'contact' => 'Contact',
                'lead' => 'Lead',
                'opportunity' => 'Opportunity',
            ],
            'prefill' => [
                'starts_at' => $request->query('starts_at'),
                'ends_at' => $request->query('ends_at'),
                'is_all_day' => $request->boolean('all_day'),
            ],
        ];
    }
}
