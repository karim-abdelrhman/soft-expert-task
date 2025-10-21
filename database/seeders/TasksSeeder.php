<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
                'assignee_id' => 2,
                'status' => 'pending'
            ],
            [
                'id' => 2,
                'title' => 'Go to School',
                'description' => 'Go to School description',
                'date' => '2025-10-15',
                'assignee_id' => 2,
                'status' => 'pending'
            ],
            [
                'id' => 3,
                'title' => 'wash your hands',
                'description' => 'wash your hands description',
                'date' => '2025-10-16',
                'assignee_id' => 3,
                'status' => 'pending'
            ],
            [
                'id' => 4,
                'title' => 'clean the floor',
                'description' => 'clean the floor description',
                'date' => '2025-10-18',
                'assignee_id' => 3,
                'status' => 'completed'
            ],
            [
                'id' => 5,
                'title' => 'Solve problem',
                'description' => 'Solve problem description',
                'date' => '2025-10-21',
                'status' => 'cancelled'
            ],
        ];

        foreach ($data as $task) {
            Task::create($task);
        }

        DB::table('task_dependencies')->insert([
            [
                'task_id' => 1,
                'depends_on' => 2
            ],
            [
                'task_id' => 3,
                'depends_on' => 4
            ]
        ]);
    }
}
