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
        Schema::table('alumni_profiles', function (Blueprint $table): void {
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('suffix', 32)->nullable();
            $table->date('birthdate')->nullable();
            $table->string('sex', 32)->nullable();
            $table->string('civil_status', 32)->nullable();
            $table->text('current_address')->nullable();
            $table->text('permanent_address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumni_profiles', function (Blueprint $table): void {
            $table->dropColumn([
                'first_name',
                'middle_name',
                'last_name',
                'suffix',
                'birthdate',
                'sex',
                'civil_status',
                'current_address',
                'permanent_address',
            ]);
        });
    }
};
