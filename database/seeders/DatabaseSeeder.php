<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Modules\Authentication\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',

        ]);

        User::factory(10)->create();
    }
}
