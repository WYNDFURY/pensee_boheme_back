<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'urls' => [
                'thumb' => $this->getUrlSafely('thumb'),
                'medium' => $this->getUrlSafely('medium'),
                'large' => $this->getUrlSafely('large'),
                'original' => $this->getUrl(),
            ],
        ];
    }

    private function getUrlSafely(string $conversion): string
    {
        if ($this->hasGeneratedConversion($conversion)) {
            return $this->getUrl($conversion);
        }

        return $this->getUrl();
    }
}
