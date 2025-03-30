<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ProductOptionSeeder extends Seeder
{
    public function run(): void
    {
        $baseOptions = [
            // [
            //     'name' => 'test',
            //     'slug' => 'test',
            //     'description' => 'Ajout d\'un rideau de guirlande sur l\'arche',
            //     'price' => 5.00,
            //     'has_price' => true,
            //     'is_active' => true,
            //     'product_name' => 'Couronne',
            // ],
            [
                'name' => 'Avec rideau de guirlande',
                'slug' => 'avec-rideau-de-guirlande',
                'description' => 'Ajout d\'un rideau de guirlande sur l\'arche',
                'price' => 5.00,
                'has_price' => true,
                'is_active' => true,
                'product_name' => 'Arche rectangulaire métallique',
            ],
            // Add more options here
        ];

        // Process each option entry directly
        foreach ($baseOptions as $option) {
            try {
                // Find the product by name
                $product = Product::where('name', $option['product_name'])->first();

                if (! $product) {
                    $this->command->warn("Product not found: {$option['product_name']}");

                    continue;
                }

                // Remove product_name as it's not a field in the database
                unset($option['product_name']);

                // Create the option
                $productOption = $product->options()->create($option);

                $this->command->info("Created option: {$option['name']} for product: {$product->name}");

            } catch (\Exception $e) {
                $this->command->error("Error creating option '{$option['name']}': ".$e->getMessage());
                Log::error("Error creating option '{$option['name']}': ".$e->getMessage());
            }
        }
    }
}
