<?php

namespace App\Models;

use Database\Factories\JobFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'created_by', 'title', 'company', 'location', 'employment_type', 'description',
    'requirements', 'application_deadline', 'status',
])]
class Job extends Model
{
    protected $table = 'job_postings';

    /** @use HasFactory<JobFactory> */
    use HasFactory;

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(function (Builder $query): void {
                $query->whereNull('application_deadline')
                    ->orWhereDate('application_deadline', '>=', today());
            });
    }

    protected function casts(): array
    {
        return ['application_deadline' => 'date'];
    }
}
