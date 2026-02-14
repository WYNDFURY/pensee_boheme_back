<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@pensee-boheme.fr')],
            [
                'first_name' => env('ADMIN_FIRST_NAME', 'Admin'),
                'last_name' => env('ADMIN_LAST_NAME', 'Pensée Bohème'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
            ]
        );
    }
}
