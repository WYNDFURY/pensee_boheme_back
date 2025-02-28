<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_published')->default(true);
            $table->integer('order')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Now add the foreign key constraint for gallery_id in images table
        Schema::table('images', function (Blueprint $table) {
            // Add foreign key constraint for gallery_id
            $table->foreign('gallery_id')->references('id')->on('galleries');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First remove the foreign key constraint from images table
        Schema::table('images', function (Blueprint $table) {
            $table->dropForeign(['gallery_id']);
        });

        // Then drop the galleries table
        Schema::dropIfExists('galleries');
    }
};
