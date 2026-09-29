<?php

namespace Database\Seeders;

use App\Enums\RoleSlug;
use App\Enums\SharingAccessLevel;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SharingDefault;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /** @var list<string> */
    private array $entities = [
        'leads',
        'accounts',
        'contacts',
        'opportunities',
        'cases',
        'tasks',
        'events',
        'reports',
        'dashboards',
        'users',
    ];

    /** @var list<string> */
    private array $actions = ['view', 'create', 'update', 'delete'];

    public function run(): void
    {
        $permissions = collect();

        foreach ($this->entities as $entity) {
            foreach ($this->actions as $action) {
                $name = "{$entity}.{$action}";
                $permissions->push(Permission::query()->updateOrCreate(
                    ['name' => $name],
                    ['label' => ucfirst($action).' '.ucfirst($entity)],
                ));
            }
        }

        $permissionIds = $permissions->pluck('id', 'name');

        foreach (RoleSlug::cases() as $slug) {
            $role = Role::query()->updateOrCreate(
                ['slug' => $slug->value],
                ['name' => $slug->label()],
            );

            $names = $this->permissionsFor($slug);
            $role->permissions()->sync(
                collect($names)->map(fn (string $name) => $permissionIds[$name])->all()
            );
        }

        foreach (['lead', 'account', 'contact', 'opportunity', 'case', 'task', 'event'] as $objectType) {
            SharingDefault::query()->updateOrCreate(
                ['object_type' => $objectType],
                ['access_level' => SharingAccessLevel::Private],
            );
        }
    }

    /**
     * @return list<string>
     */
    private function permissionsFor(RoleSlug $slug): array
    {
        $all = $this->allPermissionNames();

        return match ($slug) {
            RoleSlug::SystemAdministrator => $all,
            RoleSlug::SalesManager => $this->except($all, [
                'users.create', 'users.update', 'users.delete',
            ]),
            RoleSlug::SalesRepresentative => array_merge(
                $this->crud(['leads', 'accounts', 'contacts', 'opportunities', 'tasks', 'events']),
                $this->viewOnly(['cases', 'reports', 'dashboards']),
            ),
            RoleSlug::ServiceRepresentative => array_merge(
                $this->crud(['cases', 'accounts', 'contacts', 'tasks', 'events']),
                $this->viewOnly(['leads', 'opportunities', 'reports', 'dashboards']),
            ),
            RoleSlug::ReadOnlyUser => $this->viewOnly($this->entities),
        };
    }

    /**
     * @return list<string>
     */
    private function allPermissionNames(): array
    {
        $names = [];
        foreach ($this->entities as $entity) {
            foreach ($this->actions as $action) {
                $names[] = "{$entity}.{$action}";
            }
        }

        return $names;
    }

    /**
     * @param  list<string>  $entities
     * @return list<string>
     */
    private function crud(array $entities): array
    {
        $names = [];
        foreach ($entities as $entity) {
            foreach ($this->actions as $action) {
                $names[] = "{$entity}.{$action}";
            }
        }

        return $names;
    }

    /**
     * @param  list<string>  $entities
     * @return list<string>
     */
    private function viewOnly(array $entities): array
    {
        return array_map(fn (string $entity) => "{$entity}.view", $entities);
    }

    /**
     * @param  list<string>  $all
     * @param  list<string>  $exclude
     * @return list<string>
     */
    private function except(array $all, array $exclude): array
    {
        return array_values(array_diff($all, $exclude));
    }
}
