<?php

namespace App\Modules\Authentication\Services;

use App\Modules\Authentication\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticationService
{
    protected $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /**
     * Handle the registration logic.
     * 
     * Responsibilities (per PDF 2.8.3.3):
     * - Password Hashing
     * - User Registration Logic
     * - JWT Token Generation
     */
    public function registerUser(array $data)
    {
        // 1. Hash the password here (Business Logic)
        // We do NOT send the raw password to the repository anymore.
        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => Hash::make($data['password']), // Hashing moved here
        ];

        // 2. Call Repository to save to DB
        $user = $this->userRepo->createUser($payload);

        // 3. Generate Token
        $token = auth('api')->login($user);

        return $this->formatTokenResponse($token);
    }

    /**
     * Handle login logic.
     */
    public function authenticateUser(string $email, string $password)
    {
        $credentials = [
            'email' => $email,
            'password' => $password
        ];

        // Attempt to verify credentials and create a token
        if (! $token = auth('api')->attempt($credentials)) {
            return null; // or throw exception
        }

        return $this->formatTokenResponse($token);
    }

    /**
     * Handle logout.
     */
    public function logoutUser()
    {
        auth('api')->logout();
    }

    /**
     * Helper to format the token array.
     */
    protected function formatTokenResponse($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => auth('api')->user()
        ];
    }
}
