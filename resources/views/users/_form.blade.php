@php
    $roleOptions = $roles->mapWithKeys(fn ($role) => [$role->id => $role->name])->all();
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <x-form-field name="name" label="Name" :value="old('name', $user->name ?? '')" required />
    <x-form-field name="email" label="Email" type="email" :value="old('email', $user->email ?? '')" required autocomplete="username" />
    <x-form-field
        name="role_id"
        label="Role"
        :value="old('role_id', $user->role_id ?? '')"
        :options="$roleOptions"
        required
    />
    <x-form-field
        name="password"
        label="Password"
        type="password"
        :required="! isset($user)"
        autocomplete="new-password"
        help="{{ isset($user) ? 'Leave blank to keep the current password.' : 'Min. 8 characters with upper, lower, and a number.' }}"
    />
    <x-form-field name="password_confirmation" label="Confirm password" type="password" :required="! isset($user)" autocomplete="new-password" />
</div>

<label class="mt-2 flex min-h-11 items-center gap-2 text-sm">
    <input
        type="checkbox"
        name="is_active"
        value="1"
        class="size-4 rounded border-black/20"
        @checked(old('is_active', $user->is_active ?? true))
    >
    Active
</label>
