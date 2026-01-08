<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Stores the conversation session
        Schema::create('conversation_contexts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('context_name')->nullable(); // e.g., "Physics Help"
            $table->timestamps();
        });

        // 2. Stores individual messages (Q&A)
        Schema::create('ai_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('conversation_id')->constrained('conversation_contexts')->onDelete('cascade');
            $table->text('user_query');
            $table->longText('ai_response');
            $table->decimal('confidence_score', 5, 2)->default(0.0);
            $table->json('sources')->nullable(); // Stores IDs of files used
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_responses');
        Schema::dropIfExists('conversation_contexts');
    }
};
