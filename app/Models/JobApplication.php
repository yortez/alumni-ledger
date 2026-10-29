<?php

namespace App\Models;

use Database\Factories\JobApplicationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['job_id', 'user_id', 'cover_letter', 'status', 'submitted_at'])]
class JobApplication extends Model
{
    /** @use HasFactory<JobApplicationFactory> */
    use HasFactory;

    public const STATUS_LABELS = [
        'submitted' => 'Submitted',
        'reviewing' => 'Under review',
        'interview_scheduled' => 'Interview scheduled',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }
}
