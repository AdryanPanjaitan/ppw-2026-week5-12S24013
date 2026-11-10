<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'pustakawan@del.ac.id'],
            ['name' => 'Pustakawan SIPUS-Del', 'password' => 'password123']
        );
    }
}
