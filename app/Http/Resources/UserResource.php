<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'email'          => $this->email,
            'full_name'      => $this->full_name,
            'phone'          => $this->phone,
            'gender'         => $this->gender,
            'avatar'         => $this->avatar,
            'card_number'    => $this->card_number,
            'e_number'       => $this->e_number,
            'state'          => $this->state,
            'role_id'        => $this->role_id,
            'branch_id'      => $this->branch_id,
            'role'           => $this->whenLoaded('role'),
            'branch'         => $this->whenLoaded('branch'),
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
