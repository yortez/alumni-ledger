<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['student_number', 'first_name', 'middle_name', 'last_name', 'program_code', 'batch_year', 'latin_honor'])]
class Graduate extends Model
{
    public const LATIN_HONORS = ['none', 'cum_laude', 'magna_cum_laude', 'summa_cum_laude'];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'student_number', 'student_number');
    }

    public function fullName(): string
    {
        return implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ], static fn (?string $part): bool => $part !== null && $part !== ''));
    }
}
