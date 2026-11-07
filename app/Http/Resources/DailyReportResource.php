<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** A daily site report for the app. Keep in sync with the Flutter `DailyReport` model. */
class DailyReportResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'report_date' => $this->report_date?->format('Y-m-d'),
            'project' => $this->project ? [
                'id' => $this->project->id,
                'name' => $this->project->name,
                'code' => $this->project->project_code,
            ] : null,
            'work_done' => $this->work_done,
            'labour_count' => $this->labour_count,
            'labour' => $this->labour_breakdown ?? [],
            'notes' => $this->notes,
            'reported_by' => $this->user?->name,
            'editable' => $request->user()?->can('update', $this->resource) ?? false,
            'photos' => DailyReportPhotoResource::collection($this->whenLoaded('photos')),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
