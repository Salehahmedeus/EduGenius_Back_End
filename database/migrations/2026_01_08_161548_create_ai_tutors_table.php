<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_tutors', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "EduGenius"
            $table->string('model_version'); // e.g., "gemini-1.5-flash"
            $table->text('system_prompt'); // e.g., "You are a helpful tutor..."
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_tutors');
    }
};
