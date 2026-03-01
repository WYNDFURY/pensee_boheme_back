<?php

namespace App\Console\Commands;

use App\Services\StoreInitialToken\StoreInitialTokenService;
use Illuminate\Console\Command;

class StoreToken extends Command
{
    protected $signature = 'app:store-token {token : The short-lived token from Meta Developer Dashboard}';

    protected $description = 'Exchange a short-lived Meta token for a long-lived token and store it in the database';

    public function handle(StoreInitialTokenService $service): int
    {
        $this->info('Exchanging token for long-lived token...');

        $success = $service->storeToken($this->argument('token'));

        if (! $success) {
            $this->error('Failed to exchange or store the token. Check the logs for details.');

            return Command::FAILURE;
        }

        $this->info('Token stored successfully. It will expire in ~60 days.');
        $this->info('The scheduler will auto-refresh it before expiry.');

        return Command::SUCCESS;
    }
}
