<?php

namespace App\Modules\Authentication\Services;

use App\Modules\Authentication\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;
use App\Mail\OTPMail;
use App\Jobs\SendOtpEmailJob;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;

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

    public function sendOTP(string $email)
    {
        $user = $this->userRepo->findByEmail($email);

        if (!$user) {
            throw new \Exception("User not found");
        }

        // 1. Generate 6 digit code
        $otp = rand(100000, 999999);

        // 2. Save to DB
        $this->userRepo->saveOTP($user, $otp);

        // 3. Send Email
        SendOtpEmailJob::dispatch($user->email, $otp);

        return ['message' => 'OTP sent to email'];
    }

    public function loginWithOTP(string $email, string $otp)
    {
        $user = $this->userRepo->findByEmail($email);

        if (!$user || !$this->userRepo->verifyOTP($user, $otp)) {
            return null; // Invalid OTP
        }

        // Generate Token
        $token = auth('api')->login($user);

        return $this->formatTokenResponse($token);
    }

    /**
     * Send Reset Link
     */
    public function sendResetLink($email)
    {
        // Laravel's built-in broker handles the token generation & email
        $status = Password::broker()->sendResetLink(['email' => $email]);

        return $status === Password::RESET_LINK_SENT
            ? ['status' => true, 'message' => __($status)]
            : ['status' => false, 'message' => __($status)];
    }

    /**
     * Reset the Password
     */
    public function resetPassword($email, $password, $passwordConfirmation, $token)
    {
        $status = Password::broker()->reset(
            [
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $passwordConfirmation,
                'token' => $token
            ],
            function ($user, $password) {
                // Determine which column to update based on your schema
                $user->password_hash = Hash::make($password);
                $user->save();
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? ['status' => true, 'message' => __($status)]
            : ['status' => false, 'message' => __($status)];
    }
}
