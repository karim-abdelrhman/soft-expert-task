<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * A basic feature test example.
     */
    #[Test]
    public function manager_can_create_a_task(): void
    {
        $user = User::factory()->create(
            [
                'name' => 'Test Manager',
                'email' => 'test@gmail.com',
                'password' => Hash::make('password'),
                'is_manager' => true,
            ]
        );

        $response = $this->actingAs($user)
            ->postJson('/api/tasks', [
                    'title' => 'Test Task',
                    'description' => 'Test Description',
                    'date' => "2025-10-19",
                    'status' => 'pending',
                ]
            );

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => []
        ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'date' => '2025-10-19',
            'status' => 'pending',
        ]);
    }

    #[Test]
    public function manager_can_update_a_task()
    {
        $user = User::factory()->create([
                'name' => 'Test Manager',
                'email' => 'test@gmail.com',
                'password' => Hash::make('password'),
                'is_manager' => true,
            ]);
        $task = Task::factory()->create();
        $date = now()->format('Y-m-d');
        $response = $this->actingAs($user)
            ->putJson("/api/tasks/{$task->id}", [
                'title' => 'Test Task updated',
                'description' => 'Test Description updated',
                'date' => $date,
                'status' => 'pending',
            ]);
        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task updated',
            'description' => 'Test Description updated',
            'date' => $date,
        ]);

    }
    #[Test]
    public function manager_can_assign_a_task_to_user()
    {
        $task = Task::factory()->create();
        $user = User::factory()->create();
        $manager = User::factory()->create(['is_manager' => true]);

        $response = $this->actingAs($manager)
            ->postJson("/api/tasks/{$task->id}/assign", [
                'user_id' => $user->id,
            ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'assignee_id' => $user->id,
        ]);
    }

    #[Test]
    public function user_throw_authorization_exception_when_assign_a_task()
    {
        $task = Task::factory()->create();
        $user = User::factory()->create();
        $another_user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/tasks/{$task->id}/assign", [
                'user_id' => $another_user->id
            ]);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response->assertJson([
            'message' => 'You do not have permission to assign tasks.'
        ]);
    }

    #[Test]
    public function user_can_not_create_a_task(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/tasks', [
                    'title' => 'Test Task',
                    'description' => 'Test Description',
                    'date' => "2025-10-19",
                    'status' => 'pending',
                ]
            );

        $response->assertStatus(Response::HTTP_FORBIDDEN);

        $response->assertJson([
            'message' => 'You do not have permission to create tasks.',
        ]);
    }

    #[Test]
    public function user_can_update_only_status_of_task_assigned_to_him()
    {
        $user = User::factory()->create();

        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'date' => "2025-10-19",
            'status' => 'pending',
            'assignee_id' => $user->id
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/tasks/{$task->id}/update-status", [
                    'status' => 'completed',
                ]
            );

        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 1, // completed
        ]);
    }

    #[Test]
    public function user_throw_authorization_exception_when_try_to_update_a_task_does_not_assigned_to_him()
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create(
            [
                'name' => 'invalid user',
                'email' => 'user2@gmail.com',
                'password' => Hash::make('password'),
            ]
        );

        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'date' => "2025-10-19",
            'status' => 'pending',
            'assignee_id' => $user->id
        ]);

        $response = $this->actingAs($anotherUser)
            ->postJson("/api/tasks/{$task->id}/update-status", [
                'status' => 'completed',
            ]);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response->assertJson([
            'message' => 'You can only update tasks that assigned to you.',
        ]);
    }

    #[Test]
    public function user_can_retrieve_only_tasks_assigned_to_them()
    {
        $user = User::factory()->create();
        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'date' => "2025-10-19",
            'status' => 'pending',
            'assignee_id' => $user->id
        ]);

        $user2 = User::factory()->create();
        $task2 = Task::create([
            'title' => 'Test Task2',
            'description' => 'Test Description2',
            'date' => "2025-10-19",
            'status' => 'pending',
            'assignee_id' => $user2->id
        ]);

        $response = $this->actingAs($user)
            ->getJson("api/tasks");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertSee($task->title);
        $response->assertDontSee($task2->title);
    }
}
