<?php

namespace App\Modules\Authentication\Repositories;

use App\Modules\Authentication\Models\User;

class UserRepository
{
    /**
     * Create a new user.
     * Expects data to already be prepared/hashed by the Service.
     */
    public function createUser(array $preparedData): User
    {
        // Simply create the record. No business logic here.
        return User::create($preparedData);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function saveOTP($user, $otp)
    {
        $user->otp_code = $otp;
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();
    }

    public function verifyOTP($user, $inputOtp)
    {
        if ($user->otp_code === $inputOtp && now()->lessThan($user->otp_expires_at)) {
            // Clear OTP after success
            $user->otp_code = null;
            $user->otp_expires_at = null;
            if ($user->email_verified_at === null) {
                $user->email_verified_at = now();
            }
            $user->save();
            return true;
        }
        return false;
    }

    public function updateUserName($user, $name)
    {
        $user->name = $name;
        $user->save();
    }
}
