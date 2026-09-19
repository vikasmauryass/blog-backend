<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
class MediaResource extends JsonResource
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
            'type' => $this->type,
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            // 'url' => Storage::disk('public')->url($this->file_path),
            'url' => asset('storage/' . $this->file_path),
            'alt_text' => $this->alt_text,
            'caption' => $this->caption,
            'created_at' => $this->created_at,
        ];
    }
}
