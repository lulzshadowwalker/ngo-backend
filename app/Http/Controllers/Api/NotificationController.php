<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\NotificationResource;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends ApiController
{
    /**
     * List user notifications
     * 
     * Retrieve all notifications for the authenticated user, including both
     * read and unread notifications. Notifications are returned in chronological order.
     *
     * @group Notifications
     * @authenticated
     * 
     * @response 200 scenario="Success" {
     *   "data": [
     *     {
     *       "id": "9d785c8a-1234-5678-9abc-123456789012",
     *       "type": "App\\Notifications\\PostLiked",
     *       "data": {
     *         "message": "Your post was liked by John Doe",
     *         "post_id": 1,
     *         "user_name": "John Doe"
     *       },
     *       "read_at": null,
     *       "created_at": "2024-01-15T10:00:00.000000Z",
     *       "updated_at": "2024-01-15T10:00:00.000000Z"
     *     }
     *   ]
     * }
     * 
     * @response 401 scenario="Unauthenticated" {
     *   "message": "Unauthenticated."
     * }
     */
    public function index()
    {
        return NotificationResource::collection(Auth::user()->notifications);
    }

    /**
     * Get notification details
     * 
     * Retrieve detailed information about a specific notification.
     * The notification must belong to the authenticated user.
     *
     * @group Notifications
     * @authenticated
     * 
     * @urlParam notification string required The UUID of the notification. Example: 9d785c8a-1234-5678-9abc-123456789012
     * 
     * @response 200 scenario="Success" {
     *   "data": {
     *     "id": "9d785c8a-1234-5678-9abc-123456789012",
     *     "type": "App\\Notifications\\PostLiked",
     *     "data": {
     *       "message": "Your post was liked by John Doe",
     *       "post_id": 1,
     *       "user_name": "John Doe"
     *     },
     *     "read_at": null,
     *     "created_at": "2024-01-15T10:00:00.000000Z",
     *     "updated_at": "2024-01-15T10:00:00.000000Z"
     *   }
     * }
     * 
     * @response 401 scenario="Unauthenticated" {
     *   "message": "Unauthenticated."
     * }
     * 
     * @response 404 scenario="Notification not found" {
     *   "message": "Notification not found"
     * }
     */
    public function show(DatabaseNotification $notification)
    {
        return NotificationResource::make($notification);
    }

    /**
     * Mark notification as read
     * 
     * Mark a specific notification as read. The notification must belong to
     * the authenticated user.
     *
     * @group Notifications
     * @authenticated
     * 
     * @urlParam notification string required The UUID of the notification to mark as read. Example: 9d785c8a-1234-5678-9abc-123456789012
     * 
     * @response 200 scenario="Success" {
     *   "data": {
     *     "id": "9d785c8a-1234-5678-9abc-123456789012",
     *     "type": "App\\Notifications\\PostLiked",
     *     "data": {
     *       "message": "Your post was liked by John Doe",
     *       "post_id": 1,
     *       "user_name": "John Doe"
     *     },
     *     "read_at": "2024-01-15T10:30:00.000000Z",
     *     "created_at": "2024-01-15T10:00:00.000000Z",
     *     "updated_at": "2024-01-15T10:30:00.000000Z"
     *   }
     * }
     * 
     * @response 401 scenario="Unauthenticated" {
     *   "message": "Unauthenticated."
     * }
     * 
     * @response 404 scenario="Notification not found" {
     *   "message": "Notification not found"
     * }
     */
    public function markAsRead(DatabaseNotification $notification)
    {
        $notification->markAsRead();

        return NotificationResource::make($notification);
    }

    /**
     * Mark all notifications as read
     * 
     * Mark all unread notifications for the authenticated user as read.
     * Returns the updated list of all user notifications.
     *
     * @group Notifications
     * @authenticated
     * 
     * @response 200 scenario="Success" {
     *   "data": [
     *     {
     *       "id": "9d785c8a-1234-5678-9abc-123456789012",
     *       "type": "App\\Notifications\\PostLiked",
     *       "data": {
     *         "message": "Your post was liked by John Doe",
     *         "post_id": 1,
     *         "user_name": "John Doe"
     *       },
     *       "read_at": "2024-01-15T10:30:00.000000Z",
     *       "created_at": "2024-01-15T10:00:00.000000Z",
     *       "updated_at": "2024-01-15T10:30:00.000000Z"
     *     }
     *   ]
     * }
     * 
     * @response 401 scenario="Unauthenticated" {
     *   "message": "Unauthenticated."
     * }
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return NotificationResource::collection(Auth::user()->notifications);
    }

    /**
     * Delete a notification
     * 
     * Permanently delete a specific notification. The notification must belong to
     * the authenticated user.
     *
     * @group Notifications
     * @authenticated
     * 
     * @urlParam notification string required The UUID of the notification to delete. Example: 9d785c8a-1234-5678-9abc-123456789012
     * 
     * @response 200 scenario="Success" {
     *   "message": "notification deleted successfully"
     * }
     * 
     * @response 401 scenario="Unauthenticated" {
     *   "message": "Unauthenticated."
     * }
     * 
     * @response 404 scenario="Notification not found" {
     *   "message": "Notification not found"
     * }
     */
    public function destroy(DatabaseNotification $notification)
    {
        $notification->delete();

        return $this->response
            ->message('notification deleted successfully')
            ->build();
    }

    /**
     * Delete all notifications
     * 
     * Permanently delete all notifications for the authenticated user.
     * This action cannot be undone.
     *
     * @group Notifications
     * @authenticated
     * 
     * @response 200 scenario="Success" {
     *   "message": "all notifications deleted successfully"
     * }
     * 
     * @response 401 scenario="Unauthenticated" {
     *   "message": "Unauthenticated."
     * }
     */
    public function destroyAll()
    {
        //  NOTE: not entirely sure if this is required but doesn't hurt to have it
        return  DB::transaction(function () {
            Auth::user()->notifications()->delete();

            return $this->response
                ->message('all notifications deleted successfully')
                ->build();
        });
    }
}
