<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'description', 'questions', 'target_programs', 'target_graduation_years', 'is_active', 'closes_at'])]
class Survey extends Model
{
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    #[Scope]
    protected function availableTo(Builder $query, User $user): Builder
    {
        $graduate = Graduate::query()
            ->where('student_number', $user->student_number)
            ->first();

        if ($user->is_admin || $graduate === null) {
            return $query->whereKey(-1);
        }

        $query->where('is_active', true)
            ->where(function (Builder $query): void {
                $query->whereNull('closes_at')->orWhere('closes_at', '>', now());
            })
            ->whereDoesntHave('responses', fn (Builder $responses): Builder => $responses->where('user_id', $user->id));

        if ($graduate->program !== null && $graduate->program !== '') {
            $query->where(function (Builder $query) use ($graduate): void {
                $query->whereJsonLength('target_programs', 0)
                    ->orWhereJsonContains('target_programs', $graduate->program);
            });
        } else {
            $query->whereJsonLength('target_programs', 0);
        }

        if ($graduate->graduation_year !== null) {
            $query->where(function (Builder $query) use ($graduate): void {
                $query->whereJsonLength('target_graduation_years', 0)
                    ->orWhereJsonContains('target_graduation_years', $graduate->graduation_year);
            });
        } else {
            $query->whereJsonLength('target_graduation_years', 0);
        }

        return $query;
    }

    protected function casts(): array
    {
        return [
            'questions' => 'array',
            'target_programs' => 'array',
            'target_graduation_years' => 'array',
            'is_active' => 'boolean',
            'closes_at' => 'datetime',
        ];
    }
}
