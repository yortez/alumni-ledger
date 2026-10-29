<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'first_name', 'middle_name', 'last_name', 'suffix', 'birthdate', 'sex',
    'civil_status', 'phone', 'current_address', 'permanent_address', 'profile_photo_path',
    'diploma_document_path', 'job_title',
    'employer', 'industry', 'employment_status', 'city', 'country', 'linkedin_url',
    'bio', 'completed_at',
])]
class AlumniProfile extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employments(): HasMany
    {
        return $this->hasMany(Employment::class);
    }

    /**
     * The role the alumnus currently holds, which is the only employment data the app displays.
     */
    public function currentEmployment(): HasOne
    {
        return $this->hasOne(Employment::class)
            ->where('is_current', true)
            ->orderByDesc('start_date')
            ->orderByDesc('id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
        ];
    }
}
