<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User;
use App\Policies\UserPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Khai báo Policy cho mô hình User
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Khởi tạo các gates 
        $this->registerGates();
    }

    protected function registerGates(): void
    {
        // Gate cho phép admin
        Gate::define('admin-access', function (User $user) {
            return $user->is_admin ;
        });

        // Gate cho phép admin hoặc chính người dùng đó 
        Gate::define('admin-or-self', function (User $user, User $model) {
            return $user->is_admin || $user->id === $model->id;
        });

    }
}
