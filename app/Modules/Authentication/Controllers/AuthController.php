<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Traits\ApiResponseTrait;
use App\Modules\Authentication\Services\AuthenticationService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private AuthenticationService $authenticationService)
    {
    }

    public function register(Request $request)
    {
    }

    public function login(Request $request)
    {
    }
}
