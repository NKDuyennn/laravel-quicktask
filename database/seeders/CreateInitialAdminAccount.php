<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; // Assuming you have a User model

class CreateInitialAdminAccount extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::unguard();

        User::create([
            'email' => 'admin.account@sun-asterisk.com',
            'password' => bcrypt('admin123'), // Ensure to hash the password
            'first_name' => 'Admin',
            'last_name' => 'Account',
            'is_active' => true, 
            'username' => 'admin-account',
            'is_admin' => true,
        ]);
    }
}
