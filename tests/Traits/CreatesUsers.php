<?php

namespace Tests\Traits;

use App\Models\User;
use App\Models\Role;

trait CreatesUsers
{
    protected function createAdminUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'is_admin' => true,
            'is_active' => true,
        ], $attributes));
    }

    protected function createRegularUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'is_admin' => false,
            'is_active' => true,
        ], $attributes));
    }

    protected function createInactiveUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'is_admin' => false,
            'is_active' => false,
        ], $attributes));
    }

    protected function createUserWithRole(string $roleName, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $role = Role::firstOrCreate(['name' => $roleName]);
        $user->roles()->attach($role);
        
        return $user;
    }
}
