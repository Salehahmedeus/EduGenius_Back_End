<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Enum as defined in PDF: 'quiz', 'ai_tutor', 'upload', 'dashboard'
            $table->enum('activity_type', ['quiz', 'ai_tutor', 'upload', 'dashboard']);

            $table->string('topic')->nullable(); // e.g. "Physics"
            $table->integer('time_spent')->default(0); // in seconds
            $table->json('metadata')->nullable(); // Extra details
            $table->date('date'); // The PDF asks for a specific Date column
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_history');
    }
};
