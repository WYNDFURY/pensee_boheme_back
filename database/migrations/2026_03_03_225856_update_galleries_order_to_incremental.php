<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $galleries = DB::table('galleries')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($galleries as $index => $gallery) {
            DB::table('galleries')
                ->where('id', $gallery->id)
                ->update(['order' => $index]);
        }
    }

    public function down(): void
    {
        DB::table('galleries')
            ->whereNull('deleted_at')
            ->update(['order' => 0]);
    }
};
