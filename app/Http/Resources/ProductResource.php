<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'price_formatted' => $this->has_price ? number_format($this->price, 2).' €' : null,
            'is_active' => $this->is_active,
            'has_price' => $this->has_price,
            'category_name' => $this->category ? $this->category->name : null,
            'media' => MediaResource::collection($this->whenLoaded('media', $this->getMedia('product_images'))),
            'options' => $this->when($this->relationLoaded('options') && $this->options->isNotEmpty(),
                ProductOptionResource::collection($this->options)
            ),
        ];
    }
}
