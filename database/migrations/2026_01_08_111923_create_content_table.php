<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Stores info about the file (name, size, type)
        Schema::create('uploaded_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type'); // pdf, docx
            $table->integer('file_size'); // in bytes
            $table->enum('upload_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamps();
        });

        // 2. Stores the actual extracted text for the AI to read
        Schema::create('knowledge_base', function (Blueprint $table) {
            $table->id();
            // Links to the uploaded material
            $table->foreignId('material_id')->constrained('uploaded_materials')->onDelete('cascade');
            $table->longText('content_text'); // The extracted text
            $table->json('vectors')->nullable(); // Reserved for future AI embeddings
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_base');
        Schema::dropIfExists('uploaded_materials');
    }
};
