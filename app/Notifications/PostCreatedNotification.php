<?php

namespace App\Notifications;

use App\Models\Post;
use App\Support\PushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostCreatedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Post $post)
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
        return $notifiable->preferences->notificationChannels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $language = $notifiable->preferences->language->value;
        $organization = $this->post->organization->name;
        $title = trans('notifications.post-created.title', ['organization' => $organization], $language);
        $body = trans('notifications.post-created.body', ['organization' => $organization, 'title' => $this->post->getTranslation('title', $language)], $language);

        return (new MailMessage)
            ->subject($title)
            ->line($body);
    }

    public function toPush(object $notifiable): PushNotification
    {
        $language = $notifiable->preferences->language->value;
        $organization = $this->post->organization->name;
        $title = trans('notifications.post-created.title', ['organization' => $organization], $language);
        $body = trans('notifications.post-created.body', ['organization' => $organization, 'title' => $this->post->getTranslation('title', $language)], $language);

        return new PushNotification(
            title: $title,
            body: $body,
        );
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $language = $notifiable->preferences->language->value;
        $organization = $this->post->organization->name;
        return [
            "title" => trans('notifications.post-created.title', ['organization' => $organization], $language),
            "body" => trans('notifications.post-created.body', ['organization' => $organization, 'title' => $this->post->getTranslation('title', $language)], $language),
        ];
    }
}
