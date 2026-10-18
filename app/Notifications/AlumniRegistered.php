<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;

class AlumniRegistered extends Notification
{
    public function __construct(public User $alumni)
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
            'title' => 'New alumni registered',
            'message' => $this->alumni->name.' ('.$this->alumni->student_number.') has registered.',
            'student_number' => $this->alumni->student_number,
            'type' => 'alumni_registered',
        ];
    }
}
