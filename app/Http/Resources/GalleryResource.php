<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GalleryResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_published' => $this->is_published,
            'cover_image' => $this->cover_image,
            'order' => $this->order,
            'media' => MediaResource::collection($this->whenLoaded('media', $this->getMedia('gallery_images'))),
        ];
    }
}
