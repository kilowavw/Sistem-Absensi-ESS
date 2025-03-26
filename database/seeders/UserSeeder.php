<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Buat admin default
        User::create([
            'name' => 'Admin ESS',
            'email' => 'admin@ess.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'profile_picture' => null,
        ]);

        // Buat user biasa
        User::create([
            'name' => 'User ESS',
            'email' => 'user@ess.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'profile_picture' => null,
        ]);
    }
}
