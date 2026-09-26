<?php

namespace App\Notifications;

use App\Models\Survey;
use Illuminate\Notifications\Notification;

class SurveyPublished extends Notification
{
    public function __construct(public Survey $survey)
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
            'title' => $this->survey->title,
            'message' => 'A new survey is now available for you to complete.',
            'survey_id' => $this->survey->id,
            'type' => 'survey',
        ];
    }
}
