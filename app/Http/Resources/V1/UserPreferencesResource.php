<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserPreferencesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'user-preferences',
            'id' => (string) $this->id,
            'attributes' => [
                'language' => $this->language,
                'emailNotifications' => $this->email_notifications,
                'pushNotifications' => $this->push_notifications,
                'profileVisibility' => Auth::user()->individual?->individualPreferences?->profile_visibility?->value,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
        ];
    }
}
