<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title', 180);
            $table->string('company', 180);
            $table->string('location', 180);
            $table->string('employment_type', 80);
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->date('application_deadline')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamps();

            $table->index(['status', 'application_deadline']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
