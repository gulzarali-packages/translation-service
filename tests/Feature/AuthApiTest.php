<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_login_with_valid_credentials()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        // Attempt login
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'device_name' => 'test-device'
        ]);

        // Assert response
        $response->assertStatus(200);
        $response->assertJsonStructure(['token']);
        
        // Verify token was created in database
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
            'name' => 'test-device'
        ]);
    }

    /** @test */
    public function it_returns_error_for_invalid_credentials()
    {
        // Create a user
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        // Attempt login with wrong password
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
            'device_name' => 'test-device'
        ]);

        // Assert response
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_validates_required_fields_for_login()
    {
        // Attempt login without required fields
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com'
        ]);

        // Assert response
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password', 'device_name']);
    }

    /** @test */
    public function it_can_logout_user()
    {
        // Create and authenticate a user
        $user = User::factory()->create();
        $token = $user->createToken('test-device');
        
        // Make logout request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->postJson('/api/logout');

        // Assert response
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Logged out successfully']);
        
        // Verify token was deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id
        ]);
    }

    /** @test */
    public function it_can_get_authenticated_user()
    {
        // Create and authenticate a user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);
        
        Sanctum::actingAs($user);

        // Make request
        $response = $this->getJson('/api/user');

        // Assert response
        $response->assertStatus(200);
        $response->assertJson([
            'id' => $user->id,
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);
    }

    /** @test */
    public function unauthenticated_users_cannot_access_protected_endpoints()
    {
        // Attempt to access protected endpoint
        $response = $this->getJson('/api/user');
        
        // Assert unauthorized response
        $response->assertStatus(401);
    }
} 