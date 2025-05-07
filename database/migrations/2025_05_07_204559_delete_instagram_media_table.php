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
        Schema::dropIfExists('instagram_media');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('instagram_media', function (Blueprint $table) {
            $table->id();
            $table->string('media_id')->unique();
            $table->text('caption')->nullable();
            $table->string('media_type');
            $table->string('media_url');
            $table->string('permalink')->nullable();
            $table->timestamp('timestamp')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
};
