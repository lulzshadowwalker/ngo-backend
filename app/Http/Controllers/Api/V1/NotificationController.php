<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\V1\NotificationResource;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends ApiController
{
    /**
     * List Notifications
     *
     * List all notifications for the authenticated user.
     *
     * @group Notifications
     *
     * @authenticated
     */
    public function index()
    {
        return NotificationResource::collection(Auth::user()->notifications);
    }

    /**
     * Get Notification
     *
     * Get a specific notification by its ID.
     *
     * @group Notifications
     *
     * @authenticated
     *
     * @urlParam notification string required The ID of the notification. Example: "8f4c4a7-6f48-4b3a-8b1e-5b9a1b3b7e3a"
     */
    public function show(DatabaseNotification $notification)
    {
        return NotificationResource::make($notification);
    }

    /**
     * Mark Notification as Read
     *
     * Mark a specific notification as read.
     *
     * @group Notifications
     *
     * @authenticated
     *
     * @urlParam notification string required The ID of the notification. Example: "8f4c4a7-6f48-4b3a-8b1e-5b9a1b3b7e3a"
     */
    public function markAsRead(DatabaseNotification $notification)
    {
        $notification->markAsRead();

        return NotificationResource::make($notification);
    }

    /**
     * Mark All as Read
     *
     * Mark all unread notifications as read.
     *
     * @group Notifications
     *
     * @authenticated
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return NotificationResource::collection(Auth::user()->notifications);
    }

    /**
     * Delete Notification
     *
     * Delete a specific notification.
     *
     * @group Notifications
     *
     * @authenticated
     *
     * @urlParam notification string required The ID of the notification. Example: "8f4c4a7-6f48-4b3a-8b1e-5b9a1b3b7e3a"
     */
    public function destroy(DatabaseNotification $notification)
    {
        $notification->delete();

        return $this->response
            ->message('notification deleted successfully')
            ->build();
    }

    /**
     * Delete All Notifications
     *
     * Delete all notifications for the authenticated user.
     *
     * @group Notifications
     *
     * @authenticated
     */
    public function destroyAll()
    {
        //  NOTE: not entirely sure if this is required but doesn't hurt to have it
        return DB::transaction(function () {
            Auth::user()->notifications()->delete();

            return $this->response
                ->message('all notifications deleted successfully')
                ->build();
        });
    }
}
