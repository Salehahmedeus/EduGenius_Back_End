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
}
