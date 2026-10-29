<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Notification types in the order they appear as filters.
     *
     * @var array<string, array{label: string, singular: string}>
     */
    private const TYPES = [
        'announcement' => ['label' => 'Announcements', 'singular' => 'Announcement'],
        'survey' => ['label' => 'Surveys', 'singular' => 'Survey'],
        'job_application' => ['label' => 'Applications', 'singular' => 'Application'],
        'alumni_registered' => ['label' => 'Registrations', 'singular' => 'Registration'],
    ];

    public function index(Request $request): View
    {
        $user = $request->user();
        $activeType = $this->activeType($request);

        $notifications = $user->notifications()
            ->when($activeType !== null, fn ($query) => $query->where('data->type', $activeType))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $typeCounts = $this->typeCounts($user);

        return view('notifications.index', [
            'notifications' => $notifications,
            'types' => self::TYPES,
            'activeType' => $activeType,
            'typeCounts' => $typeCounts,
            'totalCount' => array_sum($typeCounts),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, string $notification): JsonResponse
    {
        $request->user()->notifications()
            ->whereKey($notification)
            ->first()
            ?->markAsRead();

        return response()->json([
            'unread' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return redirect()
            ->route('notifications.index', array_filter($request->only('type')))
            ->with('status', 'All notifications marked as read.');
    }

    private function activeType(Request $request): ?string
    {
        $type = $request->string('type')->toString();

        return array_key_exists($type, self::TYPES) ? $type : null;
    }

    /**
     * Count the signed-in user's notifications per type.
     *
     * @return array<string, int>
     */
    private function typeCounts(User $user): array
    {
        $counts = array_fill_keys(array_keys(self::TYPES), 0);

        foreach ($user->notifications()->toBase()->select('data')->pluck('data') as $data) {
            $type = json_decode((string) $data, true)['type'] ?? null;

            if (is_string($type) && isset($counts[$type])) {
                $counts[$type]++;
            }
        }

        return $counts;
    }
}
