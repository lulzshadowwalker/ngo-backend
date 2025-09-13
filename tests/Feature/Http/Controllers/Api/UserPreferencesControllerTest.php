<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Enums\Language;
use App\Http\Resources\V1\UserPreferencesResource;
use App\Models\User;
use App\Models\UserPreferences;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class UserPreferencesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_user_preferences()
    {
        $user = User::factory()
            ->has(UserPreferences::factory(), 'preferences')
            ->create();
        $resource = UserPreferencesResource::make($user->preferences);
        $request = Request::create(route('api.v1.profile.preferences.index'), 'get');
        $this->actingAs($user);

        $this->getJson(route('api.v1.profile.preferences.index'))
            ->assertOk()
            ->assertExactJson(
                $resource->response($request)->getData(true),
            );
    }

    public function test_it_updates_preferences()
    {
        $user = User::factory()
            ->has(UserPreferences::factory()->state([
                'language' => Language::en,
                'email_notifications' => true,
                'push_notifications' => true,
            ]), 'preferences')
            ->create();

        $this->actingAs($user);

        $this->assertEquals($user->preferences->language->value, 'en');
        $this->patchJson(route('api.v1.profile.preferences.update'), [
            'data' => [
                'attributes' => [
                    'language' => 'ar',
                    'emailNotifications' => false,
                    'pushNotifications' => false,
                ]
            ]
        ])->assertOk();

        $user->refresh();
        $this->assertEquals('ar', $user->preferences->language->value);
        $this->assertFalse($user->preferences->email_notifications);
        $this->assertFalse($user->preferences->push_notifications);
    }
}
