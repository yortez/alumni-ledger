<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'student_number', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function profile(): HasOne
    {
        return $this->hasOne(AlumniProfile::class);
    }

    public function jobPostings(): HasMany
    {
        return $this->hasMany(Job::class, 'created_by');
    }

    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function surveyResponses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function assignedAdminRole(): BelongsTo
    {
        return $this->belongsTo(AdminRole::class, 'role_id');
    }

    public function adminRole(): ?AdminRole
    {
        if (! $this->is_admin) {
            return null;
        }

        if ($this->role_id !== null) {
            return $this->relationLoaded('assignedAdminRole')
                ? $this->getRelation('assignedAdminRole')
                : $this->assignedAdminRole()->first();
        }

        return AdminRole::query()
            ->where('type', $this->admin_role ?? 'super_admin')
            ->first();
    }

    public function hasAdminPermission(string ...$permissions): bool
    {
        if (! $this->is_admin) {
            return false;
        }

        return $this->adminRole()?->hasPermission(...$permissions) ?? false;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin'
            && $this->is_admin
            && $this->adminRole() !== null;
    }

    /**
     * @param  list<string>  $permissions
     */
    public function canDelegateAdminPermissions(array $permissions): bool
    {
        $assignedPermissions = $this->adminRole()?->permissions ?? [];

        return in_array('*', $assignedPermissions, true)
            || array_diff($permissions, $assignedPermissions) === [];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }
}
