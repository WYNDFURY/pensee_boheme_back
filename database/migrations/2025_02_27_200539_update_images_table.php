<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('images', function (Blueprint $table) {
            // Add new nullable foreign keys
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('gallery_id')->nullable(); // For future galleries
            $table->integer('order')->default(0); // For ordering in galleries

            // Add foreign key constraints
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('product_id')->references('id')->on('products');
            
            // If you have a galleries table already:
            // $table->foreign('gallery_id')->references('id')->on('galleries');

            // Remove polymorphic columns (will be done last)
            $table->dropColumn(['imageable_id', 'imageable_type']);
        });
    }

    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            //Re-add polymorphic columns
            $table->nullableMorphs('imageable');

            // Remove foreign keys
            $table->dropForeign(['category_id']);
            $table->dropForeign(['product_id']);
            // $table->dropForeign(['gallery_id']);

            // Remove columns
            $table->dropColumn(['category_id', 'product_id', 'gallery_id', 'order']);
        });
    }
};
