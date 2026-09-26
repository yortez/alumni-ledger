<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'phone', 'job_title', 'employer', 'industry', 'employment_status', 'city',
    'country', 'linkedin_url', 'bio', 'completed_at',
])]
class AlumniProfile extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
