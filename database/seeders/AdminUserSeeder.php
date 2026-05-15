<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['phone' => '+59170000000'],
            [
                'name' => 'Administrador Principal',
                'email' => 'admin@ifenotes.com',
                'password' => Hash::make('ife123456'),
                'is_admin' => true,
                'is_follower_unlocked' => true,
            ]
        );
    }
}
