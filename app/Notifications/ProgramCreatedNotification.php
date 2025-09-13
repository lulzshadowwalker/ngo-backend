<?php

namespace App\Notifications;

use App\Models\Program;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProgramCreatedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Program $program)
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
        $organization = $this->program->organization;
        $title = trans('notifications.program-created.title', ['organization' => $organization->name], $language);
        $body = trans('notifications.program-created.body', ['organization' => $organization->name, 'title' => $this->program->getTranslation('title', $language)], $language);

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
        $organization = $this->program->organization;
        return [
            "title" => trans('notifications.program-created.title', ['organization' => $organization->name], $language),
            "body" => trans('notifications.program-created.body', ['organization' => $organization->name, 'title' => $this->program->getTranslation('title', $language)], $language),
        ];
    }
}
