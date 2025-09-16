<?php

namespace App\Notifications;

use App\Contracts\PushNotificationService;
use App\Services\FirebasePushNotification\UserNotificationStrategy;
use App\Support\PushNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Push notification channel.
 */
class PushChannel
{
    public function __construct(protected PushNotificationService $service)
    {
        //
    }

    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        /**
         * @var PushNotification $message
         */
        $message = $notification->toPush($notifiable);

        Log::info('Sending push notification', [
            'title' => $message->title,
            'body' => $message->body,
        ]);

        // TODO: Refactor PushNotificationService to deal with Object $notifiable instead of User $notifiable
        (new UserNotificationStrategy)->send($message, $notifiable);
    }
}
