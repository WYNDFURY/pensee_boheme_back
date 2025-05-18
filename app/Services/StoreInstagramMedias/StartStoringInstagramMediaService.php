<?php

namespace App\Services\StoreInstagramMedias;

use App\Models\InstagramMedia;

class StartStoringInstagramMediaService
{
    protected $FetchService;

    protected $StoreService;

    public function __construct(FetchInstagramMediaService $fetchService, StoreInstagramMediaService $storeService)
    {
        $this->FetchService = $fetchService;
        $this->StoreService = $storeService;
    }

    public function startStoringInstagramMedia()
    {
        InstagramMedia::query()->delete();
        $this->FetchService->fetchInstagramMedias();
        $fetchedMedias = $this->FetchService->fetchInstagramMedias();
        $this->StoreService->storeInstagramMedia($fetchedMedias);

        return response()->json([
            'message' => 'Instagram media stored successfully',
        ], 200);
    }
}
