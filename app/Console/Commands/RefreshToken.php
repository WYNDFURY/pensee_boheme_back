<?php

namespace App\Console\Commands;

use App\Services\RefreshLongLivedToken\StartRefreshingLongLivedTokenService;
use Illuminate\Console\Command;

class RefreshToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh the Instagram access token';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Refreshing Instagram access token...');

        // Here you would typically call a service to refresh the token
        // For example:
        // $tokenService = app(InstagramTokenService::class);
        // $tokenService->refresh();

        $refreshToken = app(StartRefreshingLongLivedTokenService::class);
        $refreshToken->StartRefreshingLongLivedToken();

        $this->info('Instagram access token refreshed successfully.');
    }
}
