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
        Schema::table('images', function (Blueprint $table) {
            // Add new columns
            $table->string('filename')->nullable()->after('path');
            $table->string('type')->nullable()->after('filename');
            $table->integer('position')->nullable()->after('type');
            $table->boolean('is_header')->default(false)->after('position');
            $table->boolean('is_featured')->default(false)->after('is_header');
            $table->boolean('is_svg')->default(false)->after('is_featured');

            // Add subsection relationship
            $table->unsignedBigInteger('subsection_id')->nullable()->after('category_id');

            // Add foreign key constraint for subsection_id
            // Note: You'll need to create a 'subsections' table first if it doesn't exist
            $table->foreign('subsection_id')->references('id')->on('subsections');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            // Remove columns
            $table->dropColumn([
                'filename',
                'type',
                'position',
                'is_header',
                'is_featured',
                'is_svg',
                'subsection_id',
            ]);

            // $table->dropForeign(['subsection_id']);
        });
    }
};
