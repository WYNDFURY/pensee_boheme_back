<?php

namespace App\Console\Commands;

use App\Services\StoreInstagramMedias\StartStoringInstagramMediaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchAndStoreInstagramMedias extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fns-instagram-medias';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fetch instagram medias';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $this->info('Fetching and storing Instagram media...');
        $fetchAndStoreService = app(StartStoringInstagramMediaService::class);
        $fetchAndStoreService->startStoringInstagramMedia();
        $this->info('Instagram media fetched and stored successfully.');
        Log::info('Instagram media fetched and stored successfully.');
    }
}
