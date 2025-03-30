<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Page;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define all pages and their categories
        $pagesWithCategories = [
            'ateliers-creatifs' => [
                [
                    'name' => 'Ateliers Créatifs',
                    'slug' => 'ateliers-creatifs',
                    'description' => 'Ateliers créatifs pour tous les âges',
                ],
            ],
            'accessoires-fleurs-sechees' => [
                [
                    'name' => 'Femmes',
                    'slug' => 'femmes',
                    'description' => 'Accessoires pour femmes',
                ],
                [
                    'name' => 'Hommes',
                    'slug' => 'hommes',
                    'description' => 'Accessoires pour hommes',
                ],
                [
                    'name' => 'Enfants',
                    'slug' => 'enfants',
                    'description' => 'Accessoires pour enfants',
                ],
            ],
            'cadeaux-invites' => [
                [
                    'name' => 'Détails Personnalisés',
                    'slug' => 'details-personnalises',
                    'description' => 'Détails personnalisés pour vos invités',
                ],
                [
                    'name' => 'Cadeaux invités',
                    'slug' => 'cadeaux-invites',
                    'description' => 'Cadeaux pour vos invités',
                ],
            ],
            'locations' => [
                [
                    'name' => 'Arches de cérémonie',
                    'slug' => 'arches-de-ceremonie',
                    'description' => 'Arches décoratives pour cérémonies',
                ],
                [
                    'name' => 'Vases et contenants',
                    'slug' => 'vases-et-contenants',
                    'description' => 'Vases et contenants décoratifs',
                ],
                [
                    'name' => 'Décorations personnalisées',
                    'slug' => 'decorations-personnalisees',
                    'description' => 'Décorations personnalisées pour événements',
                ],
                [
                    'name' => 'Éléments décoratifs',
                    'slug' => 'elements-decoratifs',
                    'description' => 'Éléments décoratifs divers',
                ],
            ],
        ];

        // Create categories for each page
        foreach ($pagesWithCategories as $pageSlug => $categories) {
            $page = Page::where('slug', $pageSlug)->first();

            // If the page doesn't exist, notify and skip
            if (! $page) {
                $this->command->error("Page with slug \"$pageSlug\" not found. Skipping related categories.");

                continue;
            }

            // Create categories for this page
            foreach ($categories as $categoryData) {
                Category::create([
                    'name' => $categoryData['name'],
                    'slug' => $categoryData['slug'],
                    'description' => $categoryData['description'],
                    'order' => 0,
                    'page_id' => $page->id,
                ]);
            }

            $this->command->info('Created '.count($categories)." categories for page: $pageSlug");
        }
    }
}
