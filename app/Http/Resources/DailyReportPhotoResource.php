<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** A daily-report photo for the app; the URLs need the API bearer token. */
class DailyReportPhotoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'caption' => $this->caption,
            'url' => route('api.daily-report-photos.file', ['photo' => $this->id]),
            'thumb_url' => route('api.daily-report-photos.file', ['photo' => $this->id, 'size' => 'thumb']),
            'uploaded_by' => $this->user?->name,
            'taken_at' => $this->taken_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'can_delete' => $request->user() ? $this->canBeDeletedBy($request->user()) : false,
        ];
    }
}
