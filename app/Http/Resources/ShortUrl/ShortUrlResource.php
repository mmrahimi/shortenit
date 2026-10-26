<?php

namespace App\Http\Resources\ShortUrl;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShortUrlResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'short_code' => $this->short_code,
            'short_url' => route('redirect', $this->short_code),
            'original_url' => $this->original_url,
            'clicks' => $this->clicks,
            'created_at' => $this->created_at,
        ];
    }
}
