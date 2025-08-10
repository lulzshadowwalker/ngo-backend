<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\NotificationResource;
use App\Models\User;
use App\Notifications\FakeDatabaseNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification as FacadesNotification;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $accessToken = $this->user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$accessToken}");
        $this->actingAs($this->user);
    }

    public function test_it_returns_all_notifications()
    {
        FacadesNotification::send($this->user, new FakeDatabaseNotification);
        $resource = NotificationResource::collection($this->user->notifications);
        $request = Request::create(route('api.v1.notifications.index'), 'get');

        $this->getJson(route('api.v1.notifications.index'))
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson(
                $resource->response($request)->getData(true),
            );
    }

    public function test_it_returns_a_single_notification()
    {
        FacadesNotification::send($this->user, new FakeDatabaseNotification);
        $notification = $this->user->notifications->first();
        $resource = NotificationResource::make($notification);
        $request = Request::create(route('api.v1.notifications.show', [
            'notification' => $notification
        ]), 'get');

        $this->getJson(route('api.v1.notifications.show', [
            'notification' => $notification
        ]))
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson(
                $resource->response($request)->getData(true),
            );
    }

    public function test_it_marks_a_notification_as_read()
    {
        FacadesNotification::send($this->user, new FakeDatabaseNotification);
        $notification = $this->user->notifications->first();

        $readNotification = clone $notification;
        $readNotification->markAsRead();

        $resource = NotificationResource::make($readNotification);
        $request = Request::create(route('api.v1.notifications.mark-as-read', [
            'notification' => $notification
        ]), 'patch');


        $this->patchJson(route('api.v1.notifications.mark-as-read', [
            'notification' => $notification
        ]))
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson(
                $resource->response($request)->getData(true),
            );
    }

    public function test_it_marks_all_notifications_as_read()
    {
        FacadesNotification::send($this->user, new FakeDatabaseNotification);
        $resource = NotificationResource::collection($this->user->notifications);
        $request = Request::create(route('api.v1.notifications.mark-all-as-read'), 'patch');

        $this->patchJson(route('api.v1.notifications.mark-all-as-read'))
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson(
                $resource->response($request)->getData(true),
            );
    }

    public function test_it_deletes_a_single_notification()
    {
        FacadesNotification::send($this->user, new FakeDatabaseNotification);
        $notification = $this->user->notifications->first();

        $request = Request::create(route('api.v1.notifications.destroy.single', [
            'notification' => $notification
        ]), 'delete');

        $this->deleteJson(route('api.v1.notifications.destroy.single', [
            'notification' => $notification
        ]))
            ->assertStatus(Response::HTTP_OK);
    }

    public function test_it_deletes_all_notifications()
    {
        FacadesNotification::send($this->user, new FakeDatabaseNotification);
        $request = Request::create(route('api.v1.notifications.destroy.all'), 'delete');

        $this->deleteJson(route('api.v1.notifications.destroy.all'))
            ->assertStatus(Response::HTTP_OK);
    }
}
