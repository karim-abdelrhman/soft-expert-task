<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $users = [
            [
                'id' => 1,
                'name' => 'Manager 1',
                'email' => 'manager@gmail.com',
                'password' => Hash::make('password'),
                'is_manager' => true,
            ],
            [
                'id' => 2,
                'name' => 'User 1',
                'email' => 'user1@gmail.com',
                'password' => Hash::make('password'),
            ],
            [
                'id' => 3,
                'name' => 'User 2',
                'email' => 'user2@gmail.com',
                'password' => Hash::make('password'),
            ],
            [
                'id' => 4,
                'name' => 'User 3',
                'email' => 'user3@gmail.com',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
