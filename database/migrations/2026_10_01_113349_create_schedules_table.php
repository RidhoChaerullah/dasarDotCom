<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('course_name', 100);
            $table->string('class_name', 50);
            $table->string('semester', 50);
            $table->string('room', 50);
            $table->string('instructor', 150);
            $table->unsignedTinyInteger('day_of_week');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(
                ['semester', 'room', 'day_of_week'],
                'schedules_room_day_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
