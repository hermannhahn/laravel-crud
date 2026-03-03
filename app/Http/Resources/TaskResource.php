<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'status_label' => match($this->status) {
                'pending' => 'Pending',
                'in_progress' => 'In Progress',
                'completed' => 'Completed',
                default => 'Unknown',
            },
            'company' => [
                'id' => $this->company_id,
                'name' => $this->company?->name,
            ],
            'area' => [
                'id' => $this->task_area_id,
                'name' => $this->area?->name,
            ],
            'service' => [
                'id' => $this->service_id,
                'title' => $this->service?->title,
                'price' => $this->service?->price,
            ],
            'professional' => [
                'id' => $this->professional_id,
                'name' => $this->professional?->name,
            ],
            'due_date' => $this->due_date?->format('Y-m-d'),
            'due_date_formatted' => $this->due_date?->format('M d, Y'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'can' => [
                'update' => $user?->isAdmin() || ($user && $user->id === $this->company_id),
                'respond' => $user?->isAdmin() || ($user && $user->id === $this->professional_id),
                'delete' => $user?->isAdmin() || ($user && $user->id === $this->company_id),
            ]
        ];
    }
}
