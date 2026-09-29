<?php

namespace Database\Seeders;

use App\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use App\Services\PasswordHistoryService;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = 'Password1!';
        $history = app(PasswordHistoryService::class);

        $users = [
            ['email' => 'admin@crm.test', 'name' => 'System Administrator', 'slug' => RoleSlug::SystemAdministrator],
            ['email' => 'sales.manager@crm.test', 'name' => 'Sales Manager', 'slug' => RoleSlug::SalesManager],
            ['email' => 'sales.rep@crm.test', 'name' => 'Sales Representative', 'slug' => RoleSlug::SalesRepresentative],
            ['email' => 'service.rep@crm.test', 'name' => 'Service Representative', 'slug' => RoleSlug::ServiceRepresentative],
            ['email' => 'readonly@crm.test', 'name' => 'Read-Only User', 'slug' => RoleSlug::ReadOnlyUser],
        ];

        foreach ($users as $row) {
            $role = Role::query()->where('slug', $row['slug']->value)->firstOrFail();

            $user = User::query()->updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => $password,
                    'role_id' => $role->id,
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'failed_login_attempts' => 0,
                    'locked_until' => null,
                ],
            );

            if ($user->passwordHistories()->count() === 0) {
                $history->store($user, $password);
            }
        }
    }
}
