<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define products by category slug
        $productsByCategory = [
            'femmes' => [
                ['name' => 'Pince à chignon', 'price' => 8.00],
                ['name' => 'Mini peigne', 'price' => 20.00],
                ['name' => 'Peigne', 'price' => 28.00],
                ['name' => 'Peigne double', 'price' => 40.00],
                ['name' => 'Couronne', 'price' => 65.00],
                ['name' => 'Serre-Tête', 'price' => 45.00],
                ['name' => 'Boucles d\'oreille', 'price' => 30.00],
                ['name' => 'Bracelet', 'price' => 20.00],
            ],
            'hommes' => [
                ['name' => 'Boutonnière', 'price' => 12.00],
                ['name' => 'Pochette fleurie', 'price' => 20.00],
                ['name' => 'Noeud Papillon', 'price' => 30.00],
            ],
            'enfants' => [
                ['name' => 'Bandeau', 'price' => 25.00],
                ['name' => 'Barrette', 'price' => 10.00],
                ['name' => 'Couronne Enfant', 'price' => 45.00],
                ['name' => 'Serre-Tête Enfant', 'price' => 30.00],
            ],
            'details-personnalises' => [
                ['name' => 'Couronne murale', 'price' => 35.00, 'description' => 'Couronne murale - à partir de 35,00€ (dimension sur demande)'],
                ['name' => 'Porte Alliance', 'price' => 30.00],
                ['name' => 'Couronne XL Prénoms + date', 'price' => 80.00],
                ['name' => 'Initiales fleuries', 'price' => 35.00],
                ['name' => 'Écriture et Signalétique', 'price' => 0.00, 'description' => 'Écriture et Signalétique - tarif sur demande'],
            ],
            'cadeaux-invites' => [
                ['name' => 'Cartes fleuries', 'price' => 5.00],
                ['name' => 'Bougies artisanales', 'price' => 8.00],
                ['name' => 'Mini bouquets', 'price' => 6.50],
                ['name' => 'Mini composition', 'price' => 7.00],
                ['name' => 'Sachets de graines', 'price' => 1.90],
            ],
            'arches-de-ceremonie' => [
                ['name' => 'Arche en macramé', 'price' => 80.00, 'description' => 'Arche en macramé (L180cm x H230cm)'],
                ['name' => 'Arche triangulaire', 'price' => 50.00, 'description' => 'Arche triangulaire (L130cm x H225cm)'],
                ['name' => 'Arche rectangulaire métallique', 'price' => 40.00, 'description' => 'Arche rectangulaire métallique (L120cm x H180cm)'],
                ['name' => 'Arche rectangulaire en bois naturel', 'price' => 80.00, 'description' => 'Arche rectangulaire en bois naturel (L160cm x H220cm)'],
            ],
            'vases-et-contenants' => [
                ['name' => 'Soliflore', 'price' => 1.00],
                ['name' => 'Contenant sur pied', 'price' => 3.00],
                ['name' => 'Contenant blanc', 'price' => 2.00],
                ['name' => 'Dames-jeanne', 'price' => 6.00],
                ['name' => 'Bouteilles en grès', 'price' => 2.00],
                ['name' => 'Vase divers', 'price' => 1.00],
                ['name' => 'Vase centre de table', 'price' => 1.00],
                ['name' => 'Vase à suspendre', 'price' => 1.00],
            ],
            'decorations-personnalisees' => [
                ['name' => 'Panneau de bienvenue', 'price' => 35.00],
                ['name' => 'Panneau plan de table', 'price' => 80.00],
                ['name' => 'Backdrop 3 pans', 'price' => 60.00],
                ['name' => 'Présentoir de bienvenue', 'price' => 40.00],
            ],
            'elements-decoratifs' => [
                ['name' => 'Support métallique', 'price' => 10.00],
                ['name' => 'Caisses en bois', 'price' => 4.00],
                ['name' => 'Chevalet', 'price' => 10.00],
                ['name' => 'Échelle de peintre', 'price' => 20.00],
                ['name' => 'Valises vintages', 'price' => 6.00],
                ['name' => 'Tapis bohème', 'price' => 4.00],
                ['name' => 'Rondins de bois', 'price' => 1.00],
                ['name' => 'Set en cordage', 'price' => 1.00],
                ['name' => 'Bureau d\'écolier', 'price' => 30.00],
                ['name' => 'Présentoir métallique doré', 'price' => 20.00],
                ['name' => 'Guirlande lumineuse LED (100m)', 'price' => 15.00],
                ['name' => 'Néon "Love is in the air"', 'price' => 10.00],
            ],
            'ateliers-creatifs' => [
                ['name' => 'La Couronne Fleurie', 'price' => 50.00, 'description' => 'Compter 2 heures d\'atelier. Un must have pour la mariée ! Si elle est assez manuelle, je recommande ce type d\'accessoire.'],
                ['name' => 'Le Bracelet Fleuri', 'price' => 20.00, 'description' => 'Compter 1 heure d\'atelier pour ce type d\'accessoire. Idéal pour que le cortège ait un élément commun !'],
                ['name' => 'Le Peigne Fleuri', 'price' => 30.00, 'description' => 'Compter 1 heures d\'atelier pour cet accessoire. Ce peigne sublimera vos coiffures le jour du mariage!'],
                ['name' => 'La Couronne Murale', 'price' => 55.00, 'description' => 'Compter 2 heures d\'atelier pour réaliser cette couronne. Elle peut être une alternative si vous pensez que la mariée préfèrera une décoration pour son lieu de réception plutôt qu\'un accessoire à porter le jour du mariage'],
            ],
        ];

        $totalProducts = 0;

        // Create products for each category
        foreach ($productsByCategory as $categorySlug => $products) {
            $category = Category::where('slug', $categorySlug)->first();

            if (! $category) {
                $this->command->error("Category with slug \"$categorySlug\" not found. Skipping related products.");

                continue;
            }

            foreach ($products as $productData) {
                $slug = Str::slug($productData['name']);
                $description = isset($productData['description'])
                  ? $productData['description']
                  : "Description pour {$productData['name']}";

                // Check if product already exists
                $existingProduct = Product::where('slug', $slug)
                    ->where('category_id', $category->id)
                    ->first();

                if ($existingProduct) {
                    $this->command->warn("Product '{$productData['name']}' already exists in category '{$category->name}'. Skipping creation.");
                } else {
                    Product::create([
                        'name' => $productData['name'],
                        'slug' => $slug,
                        'description' => $description,
                        'price' => $productData['price'],
                        'category_id' => $category->id,
                        'is_active' => true,
                    ]);
                }

                $totalProducts++;
            }

            $this->command->info('Created '.count($products)." products for category: {$category->name}");
        }

        $this->command->info("Total products created: $totalProducts");
    }
}
