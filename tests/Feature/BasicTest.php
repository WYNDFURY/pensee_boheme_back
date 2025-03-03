<?php

// I want this file to register the media collection in storage>app>public>images>afs>items with the names [ parure_1, 2, 3, 4, 5, 6] for the product where category_id is 1 using spatie media library

use App\Models\Product;

it('it register a media collection', function () {
    $product = Product::factory()->create(['category_id' => 1]);

    $product->addMedia(storage_path('app/public/images/afs/items/parure_1.jpg'))
        ->preservingOriginal()
        ->toMediaCollection('parure_1');

    $product->addMedia(storage_path('app/public/images/afs/items/parure_2.jpg'))
        ->preservingOriginal()
        ->toMediaCollection('parure_2');

    $product->addMedia(storage_path('app/public/images/afs/items/parure_3.jpg'))
        ->preservingOriginal()
        ->toMediaCollection('parure_3');

    $product->addMedia(storage_path('app/public/images/afs/items/parure_4.jpg'))
        ->preservingOriginal()
        ->toMediaCollection('parure_4');

    $product->addMedia(storage_path('app/public/images/afs/items/parure_5.jpg'))
        ->preservingOriginal()
        ->toMediaCollection('parure_5');

    $product->addMedia(storage_path('app/public/images/afs/items/parure_6.jpg'))
        ->preservingOriginal()
        ->toMediaCollection('parure_6');

    $this->assertCount(6, $product->getMedia('parure_1'));
    $this->assertCount(6, $product->getMedia('parure_2'));
    $this->assertCount(6, $product->getMedia('parure_3'));
    $this->assertCount(6, $product->getMedia('parure_4'));
    $this->assertCount(6, $product->getMedia('parure_5'));
    $this->assertCount(6, $product->getMedia('parure_6'));
});
