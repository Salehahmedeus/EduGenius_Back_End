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
     * Step 1: Generate OTP and email it.
     */
    public function sendPasswordResetOtp($email)
    {
        $user = $this->userRepo->findByEmail($email);

        if (!$user) {
            // For security, standard practice is to say "If email exists...", 
            // but for your project, returning an error is fine.
            return ['status' => false, 'message' => 'User not found'];
        }

        // 1. Generate 6-digit code
        $otp = rand(100000, 999999);

        // 2. Save to DB (Reuse the repository method you made earlier)
        // This sets otp_code and otp_expires_at (10 mins)
        $this->userRepo->saveOTP($user, $otp);

        // 3. Send Email (Reuse your existing OTPMail class)
        try {
            Mail::to($user->email)->send(new OTPMail($otp));
            return ['status' => true, 'message' => 'OTP code sent to your email'];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'Failed to send email: ' . $e->getMessage()];
        }
    }

    /**
     * Step 2: Verify OTP and Change Password.
     */
    public function resetPasswordWithOtp($email, $otp, $newPassword)
    {
        $user = $this->userRepo->findByEmail($email);

        if (!$user) {
            return ['status' => false, 'message' => 'User not found'];
        }

        // 1. Check if OTP is valid (Reuse repository logic)
        // This checks if code matches AND if time < expires_at
        $isValid = $this->userRepo->verifyOTP($user, $otp);

        if (!$isValid) {
            return ['status' => false, 'message' => 'Invalid or expired OTP'];
        }

        // 2. Update Password (The manual fix for password_hash)
        $user->password_hash = Hash::make($newPassword);

        // 3. Clear OTP fields (Repository verifyOTP does this, but good to be safe)
        $user->otp_code = null;
        $user->otp_expires_at = null;

        $user->save();

        return ['status' => true, 'message' => 'Password has been reset successfully'];
    }


    public function change_user_name($name)
    {
        $user = auth('api')->user();
        $this->userRepo->updateUserName($user, $name);
        return ['status' => true, 'message' => 'Name changed successfully'];
    }
}
