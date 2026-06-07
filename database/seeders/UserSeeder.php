<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin Workaura',
            'email' => 'admin@workaura.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '+212600000000'
        ]);
        
        User::create([
            'name' => 'John Doe',
            'email' => 'user@workaura.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'phone' => '+212600000001'
        ]);
    }
}