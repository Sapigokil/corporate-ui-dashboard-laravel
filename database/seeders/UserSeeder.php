<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Admin
        $admin = User::create([
            'name' => 'Admin Erapor',
            'email' => 'admin@erapor.test',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('Admin');

        // Guru
        $guru = User::create([
            'name' => 'Guru Erapor',
            'email' => 'guru@erapor.test',
            'password' => Hash::make('password'),
        ]);
        $guru->assignRole('Guru');

        // Wali Murid
        $wali = User::create([
            'name' => 'Wali Murid',
            'email' => 'wali@erapor.test',
            'password' => Hash::make('password'),
        ]);
        $wali->assignRole('Wali Murid');
    }
}
