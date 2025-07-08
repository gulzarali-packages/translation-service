<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Authenticate a user and return a token.
     *
     * @param string $email
     * @param string $password
     * @param string $deviceName
     * @return string
     * @throws ValidationException
     */
    public function authenticate(string $email, string $password, string $deviceName): string
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke existing tokens for this device if any
        $user->tokens()->where('name', $deviceName)->delete();

        // Create new token
        return $user->createToken($deviceName)->plainTextToken;
    }

    /**
     * Logout a user by revoking all tokens or a specific token.
     *
     * @param User $user
     * @param string|null $tokenId
     * @return void
     */
    public function logout(User $user, ?string $tokenId = null): void
    {
        if ($tokenId) {
            $user->tokens()->where('id', $tokenId)->delete();
        } else {
            $user->tokens()->delete();
        }
    }

    /**
     * Get authenticated user.
     *
     * @param int $id
     * @return User|null
     */
    public function getAuthenticatedUser(int $id): ?User
    {
        return User::find($id);
    }
} 