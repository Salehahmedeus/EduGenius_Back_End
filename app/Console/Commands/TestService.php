<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Authentication\Services\AuthenticationService;
use App\Modules\Authentication\Models\User;

class TestService extends Command
{
    protected $signature = 'test:service';
    protected $description = 'Test Authentication Service';

    public function handle(AuthenticationService $service)
    {
        $this->info('Starting Service Test...');

        $email = 'service_test@example.com';
        $password = 'password123';

        // Cleanup
        User::where('email', $email)->delete();

        // 1. Test Registration
        try {
            $response = $service->registerUser([
                'name' => 'Service Test User',
                'email' => $email,
                'password' => $password
            ]);

            $this->info('Registration Successful.');
            $this->info('Token: ' . substr($response['access_token'], 0, 10) . '...');
        } catch (\Exception $e) {
            $this->error('Registration Failed: ' . $e->getMessage());
            $this->error($e->getFile() . ':' . $e->getLine());
            return;
        }

        // 2. Test Login
        $loginResponse = $service->authenticateUser($email, $password);
        if ($loginResponse) {
            $this->info('Login Successful.');
        } else {
            $this->error('Login Failed.');
        }
    }
}
