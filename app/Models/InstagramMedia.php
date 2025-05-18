<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// public function up(): void
// {
//     Schema::create('instagram_media', function (Blueprint $table) {
//         $table->id();
//         $table->timestamps();
//         $table->softDeletes();
//         $table->string('media_id')->unique();
//         $table->string('caption')->nullable();
//         $table->string('media_type');
//         $table->string('media_url');
//         $table->string('permalink')->nullable();
//         $table->timestamp('timestamp')->nullable();
//     });
// }

class InstagramMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'media_id',
        'caption',
        'media_type',
        'media_url',
        'permalink',
        'timestamp',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'timestamp',
    ];
}
