<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public shape of a user for the mobile app. Deliberately excludes password,
 * remember_token, timestamps and anything else the client doesn't need.
 *
 * NOTE: the field names here form a contract with the Flutter `User.fromJson`
 * model — keep the two in sync.
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'status' => $this->status,
            'company' => [
                'id' => $this->company?->id,
                'name' => $this->company?->name,
            ],
        ];
    }
}
