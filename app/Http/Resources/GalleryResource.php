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
        // Get all media for count
        $allMedia = $this->getMedia('gallery_images');
        $imagesCount = $allMedia->count();

        // If this is an index request, limit media to 3 items
        $media = $allMedia;
        if ($request->routeIs('api.galleries.index')) {
            $media = $media->take(3);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'photographer' => $this->photographer,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_published' => $this->is_published,
            'cover_image' => $this->cover_image,
            'order' => $this->order,
            'images_count' => $imagesCount,
            'media' => MediaResource::collection($media),
        ];
    }
}
