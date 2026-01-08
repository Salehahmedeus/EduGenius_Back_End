<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use \App\Modules\AILearning\Models\AITutor;
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

        AITutor::create([
            'name' => 'EduGenius Main',
            'model_version' => 'gemini-2.5-flash', // Or 'gemini-pro'
            'system_prompt' => 'You are EduGenius, an AI Tutor. Answer based on the student notes provided. If the answer is not in the notes, use your general knowledge but mention that it is not from the notes.',
            'is_active' => true
        ]);
    }
}
