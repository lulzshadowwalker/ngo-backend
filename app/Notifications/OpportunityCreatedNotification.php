<?php

namespace App\Notifications;

use App\Models\Opportunity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OpportunityCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Opportunity $opportunity)
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
        return $notifiable->preferences->notificationChannels ?? ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $language = $notifiable->preferences->language->value ?? 'en';
        $organization = $this->opportunity->organization;
        $program = $this->opportunity->program;
        $programTitle = $program?->getTranslation('title', $language) ?? '';
        $opportunityTitle = $this->opportunity->getTranslation('title', $language);
        $title = trans('notifications.opportunity-created.title', [
            'organization' => $organization->name,
            'program' => $programTitle,
        ], $language);
        $body = trans('notifications.opportunity-created.body', [
            'organization' => $organization->name,
            'program' => $programTitle,
            'title' => $opportunityTitle,
        ], $language);

        return (new MailMessage)
            ->subject($title)
            ->line($body);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $language = $notifiable->preferences->language->value ?? 'en';
        $organization = $this->opportunity->organization;
        $program = $this->opportunity->program;
        $programTitle = $program?->getTranslation('title', $language) ?? '';
        $opportunityTitle = $this->opportunity->getTranslation('title', $language);

        return [
            'title' => trans('notifications.opportunity-created.title', [
                'organization' => $organization->name,
                'program' => $programTitle,
            ], $language),
            'body' => trans('notifications.opportunity-created.body', [
                'organization' => $organization->name,
                'program' => $programTitle,
                'title' => $opportunityTitle,
            ], $language),
        ];
    }
}
