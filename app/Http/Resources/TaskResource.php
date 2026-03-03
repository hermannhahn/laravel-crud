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
            'status_label' => ucfirst(str_replace('_', ' ', $this->status)),
            'company' => [
                'id' => $this->company_id,
                'name' => $this->company?->name,
            ],
            'profession' => [
                'id' => $this->profession_id,
                'name' => $this->profession?->name,
            ],
            'service' => [
                'id' => $this->service_id,
                'title' => $this->service?->title,
                'payout' => $this->service?->payout,
            ],
            'professional' => [
                'id' => $this->professional_id,
                'name' => $this->professional?->name,
            ],
            'due_date' => $this->due_date?->format('Y-m-d'),
            'due_date_formatted' => $this->due_date?->format('M d, Y'),
            'completed_at' => $this->completed_at?->format('Y-m-d H:i:s'),
            'can' => [
                'update' => $user?->isAdmin() || ($user && $user->id === $this->company_id),
                'respond' => $user?->isAdmin() || ($user && $user->id === $this->professional_id),
                'delete' => $user?->isAdmin() || ($user && $user->id === $this->company_id),
            ]
        ];
    }
}
