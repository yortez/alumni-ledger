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
        Schema::create('alumni_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('phone', 40)->nullable();
            $table->string('job_title')->nullable();
            $table->string('employer')->nullable();
            $table->string('industry')->nullable();
            $table->string('employment_status')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->text('bio')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_profiles');
    }
};
