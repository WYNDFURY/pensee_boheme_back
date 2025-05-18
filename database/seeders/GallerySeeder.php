<?php

namespace Database\Seeders;

use App\Models\Gallery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GallerySeeder extends Seeder
{
    protected $galleriesData = [
        ['name' => 'Élégance Belle Époque', 'photographer' => 'Mickael Liblin'],
        ['name' => 'Bohème Chic', 'photographer' => 'Anne Guerrand'],
        ['name' => 'Champêtre Coloré', 'photographer' => 'Étienne Ster'],
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

    public function run(): void
    {

        foreach ($this->galleriesData as $galleryData) {
            $gallery = Gallery::create([
                'name' => $galleryData['name'],
                'photographer' => $galleryData['photographer'],
                'slug' => Str::slug($galleryData['name']),
                'description' => null,
                'cover_image' => null,
                'is_published' => true,
                'order' => 0,
            ]);
            $this->command->info("Gallery created: {$gallery->name}");
        }
    }
}
