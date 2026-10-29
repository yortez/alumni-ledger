<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class AdminWorkspaceLinks extends Widget
{
    protected static ?bool $isLazyLoaded = false;

    protected string $view = 'filament.widgets.admin-workspace-links';

    /**
     * @return array<int, array{title: string, detail: string, route: string}>
     */
    public function getWorkspaces(): array
    {
        $user = Filament::auth()->user();

        if (! $user instanceof User) {
            return [];
        }

        $adminRole = $user->adminRole();

        if ($adminRole === null) {
            return [];
        }

        $workspaceDefinitions = [
            'manage_admins' => ['title' => 'Administrator access', 'detail' => 'Create administrators and assign their roles.', 'route' => 'admin.users.index'],
            'manage_roles' => ['title' => 'Role definitions', 'detail' => 'Create roles and choose their module permissions.', 'route' => 'admin.roles.index'],
            'graduates' => ['title' => 'Open master list', 'detail' => 'Search, update, and import graduate records.', 'route' => 'admin.graduates.index'],
            'announcements' => ['title' => 'Open content manager', 'detail' => 'Create and publish announcements for alumni.', 'route' => 'admin.announcements.index'],
            'surveys' => ['title' => 'Manage surveys', 'detail' => 'Set audiences, review responses, and close surveys.', 'route' => 'admin.surveys.index'],
            'jobs' => ['title' => 'Manage jobs', 'detail' => 'Publish opportunities and review applications.', 'route' => 'admin.jobs.index'],
        ];
        $workspaces = [];

        foreach ($workspaceDefinitions as $permission => $workspace) {
            if ($adminRole->hasPermission($permission)) {
                $workspaces[] = $workspace;
            }
        }

        return $workspaces;
    }
}