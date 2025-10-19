<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TasksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'title' => 'do your homework',
                'description' => 'do your homework description',
                'date' => '2025-10-15',
                'assignee_id' => 3,
            ],
            [
                'id' => 2,
                'title' => 'Go to School',
                'description' => 'Go to School description',
                'date' => '2025-10-15',
                'assignee_id' => 2,
            ],
            [
                'id' => 3,
                'title' => 'wash your hands',
                'description' => 'wash your hands description',
                'date' => '2025-10-16',
            ],
            [
                'id' => 4,
                'title' => 'clean the floor',
                'description' => 'clean the floor description',
                'date' => '2025-10-18',
            ],
        ];

        foreach ($data as $task) {
            Task::create($task);
        }
    }
}
