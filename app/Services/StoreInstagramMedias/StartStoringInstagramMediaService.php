<?php

namespace App\Services\StoreInstagramMedias;

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
        $this->FetchService->fetchInstagramMedias();
        $fetchedMedias = $this->FetchService->fetchInstagramMedias();
        $this->StoreService->storeInstagramMedia($fetchedMedias);

        return response()->json([
            'message' => 'Instagram media stored successfully',
        ], 200);
    }
}
