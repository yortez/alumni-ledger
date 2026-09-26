<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Notifications\Notification;

class AnnouncementPublished extends Notification
{
    public function __construct(public Announcement $announcement)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->announcement->title,
            'message' => $this->announcement->body,
            'announcement_id' => $this->announcement->id,
            'published_at' => $this->announcement->published_at?->toDateTimeString(),
            'type' => 'announcement',
        ];
    }
}
