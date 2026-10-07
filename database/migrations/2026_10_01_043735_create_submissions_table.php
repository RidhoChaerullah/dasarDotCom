<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assignment_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('submission_category_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('file_path')->unique();
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type');
            $table->dateTime('uploaded_at');
            $table->dateTime('deadline_at_upload');
            $table->string('status', 20);
            $table->timestamps();

            $table->unique(['assignment_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
