<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A task photo for the mobile app. The image URLs need the same bearer token
 * as the rest of the API. Keep in sync with the Flutter `TaskPhoto` model.
 */
class TaskPhotoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'caption' => $this->caption,
            'url' => route('api.task-photos.file', ['photo' => $this->id]),
            'thumb_url' => route('api.task-photos.file', ['photo' => $this->id, 'size' => 'thumb']),
            'width' => $this->width,
            'height' => $this->height,
            'uploaded_by' => $this->user?->name,
            'taken_at' => $this->taken_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'can_delete' => $request->user() ? $this->canBeDeletedBy($request->user()) : false,
        ];
    }
}
