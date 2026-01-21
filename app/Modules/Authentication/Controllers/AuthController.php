<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Authentication\Services\AuthenticationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    // public function register(Request $request)
    // {
    //     // 1. Validation (Presentation Layer)
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'password' => 'required|string|min:6',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 400);
    //     }

    //     // 2. Call Service (Business Logic Layer)
    //     $result = $this->authService->registerUser($request->only(['name', 'email', 'password']));

    //     return response()->json($result, 201);
    // }

    public function register(Request $request)
    {
        // CHECKPOINT 1: Does the route hit the controller?
        // return response()->json(['debug' => 'Checkpoint 1: Controller reached']);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // CHECKPOINT 2: Did Validation pass?
        // return response()->json(['debug' => 'Checkpoint 2: Validation passed']);

        try {
            // CHECKPOINT 3: Attempting DB Insert
            $result = $this->authService->registerUser($request->only(['name', 'email', 'password']));

            if (!$result) {
                return response()->json(['debug' => 'Checkpoint 3 Failed: User is null']);
            }
            // return response()->json(['debug' => 'Checkpoint 3: User Created', 'user_id' => $user->id]);

            // CHECKPOINT 4: Attempting Token Generation

            if (!$result['token']) {
                return response()->json(['debug' => 'Checkpoint 4 Failed: Token is null']);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            // 👇 THIS IS THE MOST IMPORTANT PART
            // If it crashes, this will catch it and show us the REAL error
            return response()->json([
                'debug_error' => true,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $result = $this->authService->authenticateUser(
            $request->input('email'),
            $request->input('password')
        );

        if (!$result) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json($result);
    }

    public function logout()
    {
        $this->authService->logoutUser();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            $this->authService->sendOTP($request->email);
            return response()->json(['message' => 'OTP sent successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6'
        ]);

        $result = $this->authService->loginWithOTP($request->email, $request->otp);

        if (!$result) {
            return response()->json(['error' => 'Invalid or expired OTP'], 401);
        }

        return response()->json($result);
    }

    /**
     * Endpoint: POST /api/password/email
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Call the new OTP method
        $result = $this->authService->sendPasswordResetOtp($request->email);

        if ($result['status']) {
            return response()->json(['message' => $result['message']]);
        } else {
            return response()->json(['error' => $result['message']], 400);
        }
    }

    /**
     * Endpoint: POST /api/password/reset
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp'   => 'required|digits:6', //  Changed from 'token' to 'otp'
            'password' => 'required|min:6|confirmed', // expects 'password_confirmation'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Call the new OTP method
        $result = $this->authService->resetPasswordWithOtp(
            $request->email,
            $request->otp,
            $request->password
        );

        if ($result['status']) {
            return response()->json(['message' => $result['message']]);
        } else {
            return response()->json(['error' => $result['message']], 400);
        }
    }
}
