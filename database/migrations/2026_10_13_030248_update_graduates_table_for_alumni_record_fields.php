<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('graduates', function (Blueprint $table): void {
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->enum('latin_honor', ['none', 'cum_laude', 'magna_cum_laude', 'summa_cum_laude'])->default('none');
            $table->renameColumn('program', 'program_code');
            $table->renameColumn('graduation_year', 'batch_year');
        });

        DB::table('graduates')->select('id', 'name')->orderBy('id')->chunkById(500, function (Collection $graduates): void {
            foreach ($graduates as $graduate) {
                $nameParts = preg_split('/\s+/u', trim($graduate->name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $firstName = array_shift($nameParts);
                $lastName = $nameParts === [] ? null : array_pop($nameParts);

                DB::table('graduates')->where('id', $graduate->id)->update([
                    'first_name' => $firstName,
                    'middle_name' => $nameParts === [] ? null : implode(' ', $nameParts),
                    'last_name' => $lastName,
                ]);
            }
        });

        Schema::table('graduates', function (Blueprint $table): void {
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('graduates', function (Blueprint $table): void {
            $table->string('name')->nullable();
            $table->renameColumn('program_code', 'program');
            $table->renameColumn('batch_year', 'graduation_year');
        });

        DB::table('graduates')->select('id', 'first_name', 'middle_name', 'last_name')->orderBy('id')->chunkById(500, function (Collection $graduates): void {
            foreach ($graduates as $graduate) {
                $name = implode(' ', array_filter([
                    $graduate->first_name,
                    $graduate->middle_name,
                    $graduate->last_name,
                ], static fn (?string $part): bool => $part !== null && $part !== ''));

                DB::table('graduates')->where('id', $graduate->id)->update(['name' => $name]);
            }
        });

        Schema::table('graduates', function (Blueprint $table): void {
            $table->dropColumn(['first_name', 'middle_name', 'last_name', 'latin_honor']);
        });

        Schema::table('graduates', function (Blueprint $table): void {
            $table->string('name')->nullable(false)->change();
        });
    }
};
