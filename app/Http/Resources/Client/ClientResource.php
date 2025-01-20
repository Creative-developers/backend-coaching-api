<?php

namespace App\Http\Resources\Client;

use App\Http\Resources\Auth\AuthResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'phone_number' => $this->phone_number,
            'date_of_birth' => $this->date_of_birth,
            'notes' => $this->notes,
            'user' => new AuthResource($this->user),
        ];
    }
}
