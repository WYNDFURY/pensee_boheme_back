<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImageSeeder extends Seeder
{
    /*
     * List of products by category
     */
    protected $categoryMappings = [
        'enfants' => [
            'bandeau' => 'Bandeau',
            'barrettes' => 'Barrette',
            'couronne' => 'Couronne',
            'serre-tete' => 'Serre-Tête',
        ],
        'femmes' => [
            'boucles-doreilles_' => 'Boucles d\'oreille',
            'bracelet' => 'Bracelet',
            'couronne_' => 'Couronne',
            'mini-peigne' => 'Mini peigne',
            'peigne-double' => 'Peigne double',
            'peigne' => 'Peigne',
            'pince-a-chignon' => 'Pince à chignon',
            'serre-tete' => 'Serre-Tête',
        ],
        'hommes' => [
            'boutonniere_' => 'Boutonnière',
            'noeud-papillon' => 'Noeud Papillon',
            'pochette-fleurie' => 'Pochette fleurie',
        ],
        'cadeaux-invites' => [
            'bougie' => 'Bougies artisanales',
            'cartes' => 'Cartes fleuries',
            'mini-bouquet' => 'Mini bouquets',
            'fiole' => 'Fioles fleuries',
            'sachets-graines' => 'Sachets de graines',
        ],
        'details-personnalises' => [
            'couronne-murale' => 'Couronne murale',
            'couronne-xl' => 'Couronne XL',
            'initiales' => 'Initiales fleuries',
            'porte-alliance' => 'Porte Alliance',
        ],
        'arches-de-ceremonie' => [
            'arche-bois' => 'Arche rectangulaire en bois',
            'arche-macrame' => 'Arche en macramé',
            'arche-metallique' => 'Arche rectangulaire métallique',
            'arche-triangulaire' => 'Arche triangulaire',
            'backdrop-bois' => 'Backdrop bois',
            'backdrop-3-pans' => 'Backdrop 3 pans',
        ],
        'decorations-personnalisees' => [
            'panneau-bienvenue' => 'Panneau de bienvenue',
            'panneau-plan' => 'Panneau plan de table',
            'presentoir' => 'Présentoir de bienvenue',
            'signaletique' => 'Signalétique',
        ],
        'elements-decoratifs' => [
            'caisse' => 'Caisses en bois',
            'fauteuil-rotin' => 'Fauteuil rotin',
            'banquette-rotin' => 'Banquette rotin',
            'chevalet' => 'Chevalet',
            'echelle' => 'Échelle de peintre',
            'valise' => 'Valises vintages',
            'set-cordage' => 'Set en cordage',
            'bureau' => 'Bureau d\'écolier',
            'presentoir-metal' => 'Présentoir métallique doré',
            'guirlande' => 'Guirlande lumineuse LED (100m)',
            'neon' => 'Néon "Love is in the air"',
        ],
        'vases-et-contenants' => [
            'soliflore' => 'Soliflore',
            'contenant-pied' => 'Contenant sur pied',
            'dames-jeanne' => 'Dames Jeanne',
            'bouteille' => 'Bouteilles en grès',
            'vase-suspendre' => 'Vase à suspendre',
            'vase-centre' => 'Vase centre de table',
            'vase-divers' => 'Vases divers',
        ],
        'ateliers-creatifs' => [
            'la-couronne-fleurie' => 'La Couronne Fleurie',
            'le-bracelet-fleuri' => 'Le Bracelet Fleuri',
            'le-peigne-fleuri' => 'Le Peigne Fleuri',
            'la-couronne-murale' => 'La Couronne Murale',
        ],
    ];

    /**
     * Map directory names to category slugs
     */
    protected $dirToCategoryMap = [
        'accessoires-fleurs-sechees/enfants' => 'enfants',
        'accessoires-fleurs-sechees/femmes' => 'femmes',
        'accessoires-fleurs-sechees/hommes' => 'hommes',
        'cadeaux-invites/cadeaux-invites' => 'cadeaux-invites',
        'cadeaux-invites/details-personnalises' => 'details-personnalises',
        'ateliers-creatifs' => 'ateliers-creatifs',
        'locations/arches-de-ceremonie' => 'arches-de-ceremonie',
        'locations/decorations-personnalisees' => 'decorations-personnalisees',
        'locations/elements-decoratifs' => 'elements-decoratifs',
        'locations/vases-et-contenants' => 'vases-et-contenants',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Image Seeder...');

        $basePath = storage_path('app/public/products');

        if (! File::isDirectory($basePath)) {
            $this->command->error("Base directory not found: {$basePath}");

            return;
        }

        $totalAddedImages = 0;
        $totalSkippedImages = 0;

        // Process each mapped directory
        foreach ($this->dirToCategoryMap as $dirPath => $categorySlug) {
            $fullPath = $basePath.'/'.$dirPath;

            if (! File::isDirectory($fullPath)) {
                $this->command->warn("Directory not found, skipping: {$fullPath}");

                continue;
            }

            // Get the category
            $category = Category::where('slug', $categorySlug)->first();
            if (! $category) {
                $this->command->error("Category with slug '{$categorySlug}' not found, skipping directory: {$dirPath}");

                continue;
            }

            $this->command->info("Processing directory: {$dirPath} for category: {$category->name}");

            // Process images in this directory
            [$added, $skipped] = $this->processDirectory($fullPath, $category, $categorySlug);

            $totalAddedImages += $added;
            $totalSkippedImages += $skipped;
        }

        $this->command->info("Image seeding complete! Total added: {$totalAddedImages}, Total skipped: {$totalSkippedImages}");
    }

    /**
     * Process a directory of images
     *
     * @param  string  $directory  Full path to directory
     * @param  Category  $category  Category model
     * @param  string  $categorySlug  Category slug for mapping
     * @return array [addedCount, skippedCount]
     */
    protected function processDirectory($directory, $category, $categorySlug)
    {
        // Get mapping for this category
        $imageToProductMapping = $this->categoryMappings[$categorySlug] ?? [];

        if (empty($imageToProductMapping)) {
            $this->command->warn("No mappings defined for category: {$categorySlug}, skipping.");

            return [0, 0];
        }

        // Get all image files in the directory
        $imageFiles = File::files($directory);
        $addedImages = 0;
        $skippedImages = 0;

        foreach ($imageFiles as $file) {
            // Skip non-image files
            $extension = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
            if (! in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $skippedImages++;

                continue;
            }

            // Get the filename without extension
            $filename = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $filenameSlug = Str::slug($filename);

            // Try to find a direct match in the mapping first
            $productName = null;

            // Find the BEST product match by checking prefixes in the image-to-product mapping
            $bestMatch = null;
            $bestMatchLength = 0;

            foreach ($imageToProductMapping as $imagePrefix => $mappedProductName) {
                // Check if the filename starts with the mapping key or contains it
                if (Str::startsWith($filenameSlug, $imagePrefix) ||
                    Str::contains($filenameSlug, $imagePrefix)) {
                    // If this match is longer than our previous best match, use it instead
                    $currentMatchLength = strlen($imagePrefix);
                    if ($currentMatchLength > $bestMatchLength) {
                        $bestMatch = $mappedProductName;
                        $bestMatchLength = $currentMatchLength;
                        $this->command->info("Found better match for {$filename}: {$bestMatch} (length: {$bestMatchLength})");
                    }
                }
            }

            if ($bestMatch) {
                $productName = $bestMatch;
                $this->command->info("Matched image {$filename} to product {$productName} using prefix mapping");
            }

            // If we still don't have a product name, try the old method as fallback
            if (! $productName) {
                $products = Product::where('category_id', $category->id)->get();

                // Sort products by name length DESC to prioritize longer, more specific names
                $sortedProducts = $products->sortByDesc(function ($product) {
                    return strlen($product->name);
                });

                foreach ($sortedProducts as $possibleProduct) {
                    $productSlug = Str::slug($possibleProduct->name);
                    if (Str::contains($filenameSlug, $productSlug)) {
                        $productName = $possibleProduct->name;
                        $this->command->info("Matched image {$filename} via slug comparison to product {$productName}");
                        break;
                    }
                }
            }

            if (! $productName) {
                $this->command->warn("Could not match image {$filename} to any product in category {$categorySlug}. Skipping.");
                $skippedImages++;

                continue;
            }

            // Find the product
            $product = Product::where('name', $productName)
                ->where('category_id', $category->id)
                ->first();

            if (! $product) {
                $this->command->warn("Product '{$productName}' not found in '{$category->name}' category. Skipping image {$filename}.");
                $skippedImages++;

                continue;
            }

            // Rest of your code for adding the media remains the same...
            $newImageName = Str::slug($productName);
            $existingMedia = $product->getMedia('product_images');
            // Generate a unique name for this image by appending a counter if similar names exist
            $counter = 1;
            $baseImageName = $newImageName;

            while ($existingMedia->contains(function ($item) use ($newImageName) {
                return $item->name === $newImageName;
            })) {
                $newImageName = $baseImageName.'-'.$counter;
                $counter++;
            }

            // At this point, $newImageName is guaranteed to be unique
            $this->command->info("Using image name '{$newImageName}' for product '{$productName}'");

            try {
                // Add the media to the product
                $product->addMedia($file->getPathname())
                    ->usingName($newImageName)
                    ->toMediaCollection('product_images');

                $this->command->info("Added image {$filename} to product '{$productName}'");
                $addedImages++;
            } catch (\Exception $e) {
                $this->command->error("Failed to add image {$filename} to product '{$productName}': ".$e->getMessage());
                $skippedImages++;
            }
        }

        $this->command->info("{$category->name}: {$addedImages} images added, {$skippedImages} skipped.");

        return [$addedImages, $skippedImages];
    }
}
