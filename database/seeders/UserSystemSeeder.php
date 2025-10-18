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


        $manager = User::create([
            'id' => 1,
            'name' => 'Manager 1',
            'email' => 'manager@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $manager->assignRole('manager');


        $users = [
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
        ];

        foreach ($users as $user) {
            $user = User::create($user);
            $user->assignRole('user');
        }

    }
}
