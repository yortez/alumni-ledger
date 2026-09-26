<?php

namespace App\Models;

use Database\Factories\AdminRoleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'type', 'description', 'permissions'])]
class AdminRole extends Model
{
    /** @use HasFactory<AdminRoleFactory> */
    use HasFactory;

    public const PERMISSIONS = [
        'graduates' => 'Graduate and applicant records',
        'announcements' => 'Announcements',
        'surveys' => 'Surveys and responses',
        'jobs' => 'Job postings and applications',
        'manage_admins' => 'Administrator accounts',
        'manage_roles' => 'Role definitions',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function hasPermission(string ...$permissions): bool
    {
        $assignedPermissions = $this->permissions ?? [];

        return in_array('*', $assignedPermissions, true)
            || array_intersect($permissions, $assignedPermissions) !== [];
    }

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_system' => 'boolean',
        ];
    }
}
