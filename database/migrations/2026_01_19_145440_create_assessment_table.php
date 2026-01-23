<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. The Quiz Session
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('topic')->nullable(); // e.g. "Physics Chapter 1"
            $table->integer('difficulty')->default(1); // 1=Easy, 2=Medium, 3=Hard
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });

        // 2. The Questions
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->text('question_text');
            $table->json('options'); // ["A", "B", "C", "D"]
            $table->string('correct_answer'); // The correct string (e.g. "Mitochondria")
            $table->text('explanation')->nullable();
            $table->timestamps();
        });

        // 3. The Results (To track score)
        Schema::create('quiz_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->integer('score'); // e.g. 80 (percent)
            $table->integer('total_questions');
            $table->integer('correct_answers');
            $table->json('attempt_details')->nullable(); // Stores user's answers and correctness
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_results');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('quizzes');
    }
};
