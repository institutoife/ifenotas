<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminPhone = env('ADMIN_PHONE');

        if (! $adminPhone) {
            return;
        }

        User::updateOrCreate(
            ['phone' => $adminPhone],
            [
                'name' => 'Administrador Principal',
                'email' => 'admin@ifenotes.com',
                'password' => Hash::make('#ifenotas26*'),
                'is_admin' => true,
                'is_follower_unlocked' => true,
            ]
        );
    }
}
