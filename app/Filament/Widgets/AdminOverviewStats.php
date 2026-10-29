<?php

namespace App\Filament\Widgets;

use App\Models\Graduate;
use App\Models\Job;
use App\Models\Survey;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminOverviewStats extends StatsOverviewWidget
{
    protected static ?bool $isLazyLoaded = false;

    protected function getStats(): array
    {
        $user = Filament::auth()->user();

        if (! $user instanceof User) {
            return [];
        }

        $adminRole = $user->adminRole();

        if ($adminRole === null) {
            return [];
        }

        $stats = [];

        if ($adminRole->hasPermission('graduates')) {
            $stats[] = Stat::make('Graduate records', Graduate::query()->count())
                ->description('Entries in the master list')
                ->color('info');
            $stats[] = Stat::make('Accounts claimed', User::query()->whereNotNull('student_number')->count())
                ->description('Graduates with an account')
                ->color('success');
        }

        if ($adminRole->hasPermission('surveys')) {
            $openSurveyCount = Survey::query()
                ->where('is_active', true)
                ->where(fn ($query) => $query->whereNull('closes_at')->orWhere('closes_at', '>', now()))
                ->count();

            $stats[] = Stat::make('Open surveys', $openSurveyCount)
                ->description('Accepting responses')
                ->color('warning');
        }

        if ($adminRole->hasPermission('jobs')) {
            $stats[] = Stat::make('Active jobs', Job::published()->count())
                ->description('Currently accepting applications')
                ->color('danger');
        }

        return $stats;
    }
}
