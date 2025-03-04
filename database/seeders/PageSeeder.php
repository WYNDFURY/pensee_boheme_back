<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create specific pages
        $pages = [
            [
                'slug' => 'accessoires-fleurs-sechees',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'cadeaux-invites',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'ateliers-creatifs',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'locations',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($pages as $page) {
            Page::firstOrCreate(
                ['slug' => $page['slug']],
                $page
            );
            $this->command->info("Page created: {$page['slug']}");
        }
    }
}
