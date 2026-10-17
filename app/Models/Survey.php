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
    public const QUESTION_TYPES = [
        'short_text' => 'Short Text',
        'long_text' => 'Long Text',
        'radio' => 'Multiple Choice (Radio)',
        'checkbox' => 'Checkboxes',
        'dropdown' => 'Dropdown',
    ];

    public const CHOICE_QUESTION_TYPES = ['radio', 'checkbox', 'dropdown'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    /**
     * @return list<array{prompt: string, section_label: string, type: string, required: bool, options: list<string>}>
     */
    public function normalizedQuestions(): array
    {
        return array_values(array_map(static function (mixed $question): array {
            if (is_string($question)) {
                return [
                    'prompt' => $question,
                    'section_label' => '',
                    'type' => 'long_text',
                    'required' => true,
                    'options' => [],
                ];
            }

            return [
                'prompt' => (string) ($question['prompt'] ?? $question['question'] ?? ''),
                'section_label' => (string) ($question['section_label'] ?? ''),
                'type' => (string) ($question['type'] ?? 'long_text'),
                'required' => (bool) ($question['required'] ?? true),
                'options' => array_values($question['options'] ?? []),
            ];
        }, $this->questions ?? []));
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

        if ($graduate->program_code !== null && $graduate->program_code !== '') {
            $query->where(function (Builder $query) use ($graduate): void {
                $query->whereJsonLength('target_programs', 0)
                    ->orWhereJsonContains('target_programs', $graduate->program_code);
            });
        } else {
            $query->whereJsonLength('target_programs', 0);
        }

        if ($graduate->batch_year !== null) {
            $query->where(function (Builder $query) use ($graduate): void {
                $query->whereJsonLength('target_graduation_years', 0)
                    ->orWhereJsonContains('target_graduation_years', $graduate->batch_year);
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
