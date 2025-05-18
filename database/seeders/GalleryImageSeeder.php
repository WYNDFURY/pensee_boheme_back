<?php

namespace Database\Seeders;

use App\Models\Gallery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GalleryImageSeeder extends Seeder
{
    /*
     * List of galleries to be seeded
     */
    protected $galleriesData = [
        ['name' => 'Élégance Belle Époque', 'photographer' => 'Mickael Liblin'],
        ['name' => 'Bohème Chic', 'photographer' => 'Anne Guerrand'],
        ['name' => 'Champêtre Coloré', 'photographer' => 'Etienne Ster'],
        ['name' => 'Couleurs en Fête', 'photographer' => 'Laura Langlois Photographie'],
        ['name' => 'Exotique Coloré', 'photographer' => null],
        ['name' => 'Fraicheur Florale', 'photographer' => 'Cupcakes Photographie'],
        ['name' => 'Nature Vert et Blanc', 'photographer' => 'Studio Larose'],
        ['name' => 'Douceur Pastel', 'photographer' => null],
        ['name' => 'Pastel Romantique', 'photographer' => 'Anne Guerrand'],
        ['name' => 'Douceur printanière', 'photographer' => null],
        ['name' => 'Romantique Rose Blush', 'photographer' => 'Studio Larose'],
        ['name' => 'Épanouissement Sauvage', 'photographer' => 'Cupcakes Photographie'],
        ['name' => 'Inspiration Bridgerton', 'photographer' => 'Allison Blomme'],
        ['name' => 'Inspiration Vintage Industriel', 'photographer' => 'Cupcakes Photographie'],
        ['name' => 'Terracota Lovers', 'photographer' => null],
        ['name' => 'Éclat de sauge', 'photographer' => 'Callmekelly Photography'],
        ['name' => 'Inspiration Satin Chic', 'photographer' => 'Aline Largenton'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Gallery Image Seeder...');

        $basePath = storage_path('app/public/galleries');

        if (! File::isDirectory($basePath)) {
            $this->command->error("Base directory not found: {$basePath}");

            return;
        }

        $totalAddedImages = 0;
        $totalSkippedImages = 0;

        // Process each gallery
        foreach ($this->galleriesData as $galleryData) {
            $galleryName = $galleryData['name'];
            $galleryPhotographer = $galleryData['photographer'];
            $galleryPath = $basePath.'/'.$galleryName.($galleryPhotographer ? ' - '.$galleryPhotographer : '');

            // Find or skip if gallery folder doesn't exist
            if (! File::isDirectory($galleryPath)) {
                $this->command->warn("Gallery directory not found, skipping: {$galleryPath}");

                continue;
            }

            // Get the gallery model
            $gallery = Gallery::where('name', $galleryName)->first();
            if (! $gallery) {
                $this->command->error("Gallery '{$galleryName}' not found in database, skipping.");

                continue;
            }

            $this->command->info("Processing gallery: {$galleryName}");

            // Process images in this gallery folder
            [$added, $skipped] = $this->processGalleryDirectory($galleryPath, $gallery);

            $totalAddedImages += $added;
            $totalSkippedImages += $skipped;
        }

        $this->command->info("Gallery image seeding complete! Total added: {$totalAddedImages}, Total skipped: {$totalSkippedImages}");
    }

    /**
     * Process a directory of images for a gallery
     *
     * @param  string  $directory  Full path to directory
     * @param  Gallery  $gallery  Gallery model
     * @return array [addedCount, skippedCount]
     */
    protected function processGalleryDirectory($directory, $gallery)
    {
        // Get all image files in the directory
        $imageFiles = File::files($directory);
        $addedImages = 0;
        $skippedImages = 0;

        // Base name for the images in this gallery
        $baseImageName = Str::slug($gallery->name);

        $galleryImages = $gallery->getMedia('gallery_images')->toArray();

        $counter = count($galleryImages);

        foreach ($imageFiles as $file) {
            // Skip non-image files
            $extension = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
            if (! in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $skippedImages++;

                continue;
            }

            $newImageName = $baseImageName.'_'.$counter;

            $this->command->info("Using image name '{$newImageName}' for gallery '{$gallery->name}'");

            try {
                // Add the media to the gallery
                $gallery->addMedia($file->getPathname())
                    ->usingName($newImageName)
                    ->toMediaCollection('gallery_images');

                $this->command->info("Added image {$file->getFilename()} to gallery '{$gallery->name}'");
                $addedImages++;
                $counter++;
            } catch (\Exception $e) {
                $this->command->error("Failed to add image {$file->getFilename()} to gallery '{$gallery->name}': ".$e->getMessage());
                $skippedImages++;
            }
        }

        $this->command->info("{$gallery->name}: {$addedImages} images added, {$skippedImages} skipped.");

        return [$addedImages, $skippedImages];
    }
}
