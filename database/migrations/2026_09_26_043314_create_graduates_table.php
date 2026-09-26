<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('graduates', function (Blueprint $table): void {
            $table->id();
            $table->string('student_number')->unique();
            $table->string('name');
            $table->string('program')->nullable();
            $table->unsignedSmallInteger('graduation_year')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graduates');
    }
};
