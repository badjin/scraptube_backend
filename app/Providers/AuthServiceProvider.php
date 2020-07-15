<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('delete-users', function ($user){
            return $user->hasRole('admin');
        });

        Gate::define('edit-users', function ($user){
            return $user->hasAnyRoles(['admin']);
        });

        Gate::define('edit-content', function ($user){
            return $user->hasAnyRoles(['admin', 'staff']);
        });

        Passport::routes();
        Passport::personalAccessTokensExpireIn(now()->addHours(6));
        Passport::refreshTokensExpireIn(now()->addDays(30));
    }
}
