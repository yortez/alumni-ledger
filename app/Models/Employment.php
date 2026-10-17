<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'alumni_profile_id', 'job_title', 'employer', 'location', 'start_date', 'end_date',
    'is_current', 'description', 'employment_status', 'sector', 'industry', 'job_level',
    'monthly_income', 'country', 'months_to_employment', 'course_relevance',
])]
class Employment extends Model
{
    public const EMPLOYMENT_STATUSES = [
        'employed_local' => 'Employed (Local)',
        'employed_abroad' => 'Employed (Abroad)',
        'self_employed' => 'Self-employed',
        'unemployed' => 'Unemployed',
        'studying' => 'Further studies',
        'other' => 'Other',
    ];

    public const SECTORS = [
        'private' => 'Private',
        'public' => 'Public / Government',
        'non_profit' => 'Non-profit / NGO',
        'self_employed' => 'Self-employed',
        'other' => 'Other',
    ];

    public const JOB_LEVELS = [
        'rank_file' => 'Rank & File',
        'supervisory' => 'Supervisory',
        'managerial' => 'Managerial',
        'executive' => 'Executive',
        'self_employed' => 'Self-employed',
        'other' => 'Other',
    ];

    public const MONTHLY_INCOME_RANGES = [
        'prefer_not_to_say' => 'Prefer not to say',
        'below_10000' => 'Below PHP 10,000',
        '10000_19999' => 'PHP 10,000 to 19,999',
        '20000_29999' => 'PHP 20,000 to 29,999',
        '30000_49999' => 'PHP 30,000 to 49,999',
        '50000_99999' => 'PHP 50,000 to 99,999',
        '100000_plus' => 'PHP 100,000 and above',
    ];

    public const COURSE_RELEVANCE_OPTIONS = [
        'not_specified' => 'Not specified',
        'highly_relevant' => 'Highly relevant',
        'somewhat_relevant' => 'Somewhat relevant',
        'not_relevant' => 'Not relevant',
    ];

    public function alumniProfile(): BelongsTo
    {
        return $this->belongsTo(AlumniProfile::class);
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
        ];
    }
}
