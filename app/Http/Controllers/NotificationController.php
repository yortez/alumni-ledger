<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);

        foreach ($notifications as $notification) {
            if ($notification->read_at === null) {
                $notification->markAsRead();
            }
        }

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }
}
