<?php

namespace App\Notifications;

use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Notifications\Notification;

class JobApplicationStatusUpdated extends Notification
{
    public function __construct(public Job $job, public JobApplication $application)
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
        $labels = JobApplication::STATUS_LABELS;

        return [
            'title' => $labels[$this->application->status] ?? 'Application updated',
            'message' => 'Your application for '.$this->job->title.' has been updated to '.$labels[$this->application->status].'.',
            'job_id' => $this->job->id,
            'type' => 'job_application',
        ];
    }
}
