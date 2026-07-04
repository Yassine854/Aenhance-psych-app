<?php

namespace Database\Seeders;

use App\Models\User; 
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user. Use different email depending on environment
        $email = (app()->environment('prod')) ? 'aenhance@prod.com' : 'aenhance@test.com';

        User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin User',
                'password' => Hash::make($email),
                'role' => 'ADMIN',
                'is_active' => true,
            ]
        );

        // Psychologist
        // User::create([
        //     'name' => 'Psychologist User',
        //     'email' => 'psychologist@example.com',
        //     'password' => Hash::make('password'),
        //     'role' => 'PSYCHOLOGIST',
        //     'is_active' => true,
        // ]);

        // Patient
        // User::create([
        //     'name' => 'Patient User',
        //     'email' => 'patient@example.com',
        //     'password' => Hash::make('password'),
        //     'role' => 'PATIENT',
        //     'is_active' => true,
        // ]);
    }
}
