<?php

namespace App\Modules\Authentication\Services;

use App\Modules\Authentication\Repositories\UserRepository;

class AuthenticationService
{
    public function __construct(private UserRepository $userRepository)
    {
    }
}
