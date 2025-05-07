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
        Schema::table('instagram_access_tokens', function (Blueprint $table) {
            $table->dropColumn('expires_in');
            $table->timestamp('expires_at')->nullable()->after('access_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instagram_access_tokens', function (Blueprint $table) {
            $table->dropColumn('expires_at');
            $table->string('expires_in')->nullable()->after('access_token');
        });
    }
};
