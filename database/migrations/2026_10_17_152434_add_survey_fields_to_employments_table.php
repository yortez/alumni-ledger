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
        Schema::table('employments', function (Blueprint $table): void {
            $table->string('employment_status', 32)->nullable();
            $table->string('sector', 32)->nullable();
            $table->string('industry')->nullable();
            $table->string('job_level', 32)->nullable();
            $table->string('monthly_income', 32)->nullable();
            $table->string('country', 120)->nullable();
            $table->unsignedSmallInteger('months_to_employment')->nullable();
            $table->string('course_relevance', 32)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employments', function (Blueprint $table): void {
            $table->dropColumn([
                'employment_status',
                'sector',
                'industry',
                'job_level',
                'monthly_income',
                'country',
                'months_to_employment',
                'course_relevance',
            ]);
        });
    }
};
