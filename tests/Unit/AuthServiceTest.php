<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    /** @test */
    public function it_can_authenticate_user_with_valid_credentials()
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        // Authenticate the user
        $token = $this->authService->authenticate('test@example.com', 'password123', 'test-device');

        // Assert token was created
        $this->assertNotEmpty($token);
        $this->assertIsString($token);
        
        // Verify token exists in database
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
            'name' => 'test-device'
        ]);
    }

    /** @test */
    public function it_throws_exception_for_invalid_credentials()
    {
        // Create a test user
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        // Expect validation exception for wrong password
        $this->expectException(ValidationException::class);
        $this->authService->authenticate('test@example.com', 'wrong-password', 'test-device');
    }

    /** @test */
    public function it_throws_exception_for_nonexistent_user()
    {
        // Expect validation exception for non-existent user
        $this->expectException(ValidationException::class);
        $this->authService->authenticate('nonexistent@example.com', 'password123', 'test-device');
    }

    /** @test */
    public function it_can_logout_user_from_all_devices()
    {
        // Create a test user
        $user = User::factory()->create();
        
        // Create tokens for the user
        $user->createToken('device-1');
        $user->createToken('device-2');
        
        // Verify tokens exist
        $this->assertEquals(2, $user->tokens()->count());
        
        // Logout from all devices
        $this->authService->logout($user);
        
        // Verify all tokens were deleted
        $this->assertEquals(0, $user->tokens()->count());
    }

    /** @test */
    public function it_can_logout_user_from_specific_device()
    {
        // Create a test user
        $user = User::factory()->create();
        
        // Create tokens for the user
        $token1 = $user->createToken('device-1');
        $user->createToken('device-2');
        
        // Get token ID
        $tokenId = $token1->accessToken->id;
        
        // Verify tokens exist
        $this->assertEquals(2, $user->tokens()->count());
        
        // Logout from specific device
        $this->authService->logout($user, $tokenId);
        
        // Verify only one token was deleted
        $this->assertEquals(1, $user->tokens()->count());
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenId
        ]);
    }

    /** @test */
    public function it_can_get_authenticated_user()
    {
        // Create a test user
        $user = User::factory()->create();
        
        // Get the user
        $result = $this->authService->getAuthenticatedUser($user->id);
        
        // Verify the correct user was returned
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
    }

    /** @test */
    public function it_returns_null_for_nonexistent_user()
    {
        // Get a non-existent user
        $result = $this->authService->getAuthenticatedUser(999);
        
        // Verify null was returned
        $this->assertNull($result);
    }
} 