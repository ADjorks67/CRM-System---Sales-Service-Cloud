<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\PasswordHistoryService;
use App\Support\ListQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly PasswordHistoryService $passwordHistory) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $applied = ListQuery::apply(
            User::query()->with('role'),
            $request,
            allowedSorts: ['name', 'email', 'created_at'],
            defaultSort: 'name',
            defaultColumns: ['name', 'email', 'role', 'status'],
            availableColumns: ['name', 'email', 'role', 'status', 'created_at'],
        );

        return view('users.index', [
            'users' => ListQuery::paginate($applied),
            'columns' => $applied['columns'],
            'sort' => $applied['sort'],
            'direction' => $applied['direction'],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.create', [
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $plainPassword = $data['password'];
        $data['is_active'] = $request->boolean('is_active', true);

        $user = User::query()->create($data);
        $this->passwordHistory->store($user, $plainPassword);

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);
        $user->load('role');

        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('users.edit', [
            'user' => $user,
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        unset($data['clear_lock']);

        if ($request->boolean('clear_lock')) {
            $user->clearLoginFailures();
        }

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $this->passwordHistory->store($user, $data['password']);
        }

        $data['is_active'] = $request->boolean('is_active');

        $user->update($data);

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
