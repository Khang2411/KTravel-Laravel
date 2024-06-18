<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use App\Models\Permission;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {

        ResetPassword::createUrlUsing(function (User $user, string $token) {
            if (!$user->role_id) {
                return env("CLIENT_APP_URL") . '/reset-password?token=' . $token . "&email=" . urlencode($user->email);
            } else {
                return env("APP_URL") . "/reset-password/$token" . "?email=" . urlencode($user->email);
            }
        });

        $permissions = Permission::all();
        foreach ($permissions as $permission) {
            Gate::define($permission->slug, function (User $user) use ($permission) {
                return $user->hasPermission($permission->slug);
            });
        }
    }
}
