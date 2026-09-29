<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Contact;
use App\Models\CrmCase;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Auth\SessionGuard;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Relation::enforceMorphMap([
            'account' => Account::class,
            'contact' => Contact::class,
            'lead' => Lead::class,
            'opportunity' => Opportunity::class,
            'case' => CrmCase::class,
            'user' => User::class,
        ]);

        // FR-AUTH-001: optional remember-me lasts 30 days.
        Auth::resolved(function ($auth): void {
            $guard = $auth->guard('web');

            if ($guard instanceof SessionGuard) {
                $guard->setRememberDuration(60 * 24 * 30);
            }
        });

        Gate::before(function (User $user, string $ability): ?bool {
            if (str_contains($ability, '.')) {
                return $user->hasPermission($ability) ?: null;
            }

            return null;
        });

        Gate::define('permission', function (User $user, string $permission): bool {
            return $user->hasPermission($permission);
        });
    }
}
