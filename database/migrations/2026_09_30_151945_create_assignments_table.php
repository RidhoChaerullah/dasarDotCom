<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('submission_category_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('title', 150);
            $table->string('course_name', 100);
            $table->string('class_name', 50);
            $table->string('semester', 50);
            $table->text('instructions')->nullable();
            $table->dateTime('due_at')->index();
            $table->boolean('is_open')->default(true);
            $table->boolean('allow_late')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
