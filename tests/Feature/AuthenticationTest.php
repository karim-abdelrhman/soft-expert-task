<?php

namespace Tests\Feature;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_register_with_valid_data()
    {
        $userData = [
            'name' => 'User',
            'email' => 'user@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [

                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'User registered successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'user',
            'email' => 'user@gmail.com',
        ]);

        $user = User::where('email', 'user@gmail.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));


        $this->assertCount(1, $user->tokens);
    }

    #[Test]
    public function user_cannot_register_with_existing_email()
    {
        User::factory()->create(['email' => 'existing@gmail.com']);

        $userData = [
            'name' => 'user',
            'email' => 'existing@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function user_cannot_register_with_invalid_data()
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    #[Test]
    public function user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'manager@gmail.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'manager@gmail.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [],
            ])
            ->assertJson([
                'message' => 'Successfully logged in',
            ]);

        $this->assertCount(1, $user->fresh()->tokens);
    }

    #[Test]
    public function user_cannot_login_with_invalid_email()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid_email@gmail.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials',
            ]);
    }

    #[Test]
    public function user_cannot_login_with_invalid_password()
    {
        User::factory()->create([
            'email' => 'test@gmail.com',
            'password' => Hash::make('valid_password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@gmail.com',
            'password' => 'invalid_password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials',
            ]);
    }

    #[Test]
    public function authenticated_user_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('authToken')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully logged out',
            ]);

        $this->assertCount(0, $user->fresh()->tokens);
    }
}
