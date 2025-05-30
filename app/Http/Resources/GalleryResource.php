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
        // If this is an index request, limit media to 3 items
        $media = $this->getMedia('gallery_images');
        if ($request->routeIs('api.galleries.index')) {
            $media = $media->take(3);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_published' => $this->is_published,
            'cover_image' => $this->cover_image,
            'order' => $this->order,
            'media' => MediaResource::collection($media),
        ];
    }
}
