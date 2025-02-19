<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $imageFiles = File::files(public_path('images'));

        foreach ($imageFiles as $file) {
            DB::table('images')->insert([
                'path' => 'images/' . $file->getFilename(),
                'alt_text' => null, // You can set a default alt text or leave it null
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
