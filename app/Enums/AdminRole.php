<?php

namespace App\Enums;

enum AdminRole: string
{
    case SuperAdmin = 'super_admin';
    case ContentAdmin = 'content_admin';
    case RecordsAdmin = 'records_admin';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super administrator',
            self::ContentAdmin => 'Content administrator',
            self::RecordsAdmin => 'Records administrator',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Manage all modules and administrator accounts.',
            self::ContentAdmin => 'Manage announcements, surveys, jobs, and applications.',
            self::RecordsAdmin => 'Manage the graduate master list and applicant records.',
        };
    }
}
