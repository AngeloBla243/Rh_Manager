<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@rh-manager.com'],
            [
                'name'     => 'Administrateur',
                'email'    => 'admin@rh-manager.com',
                'password' => Hash::make('Admin@2025!'),
                'role'     => 'admin',
            ]
        );
    }
}
