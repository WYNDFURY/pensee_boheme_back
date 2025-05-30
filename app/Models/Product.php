<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'category_id',
        'is_active',
        'has_price',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'has_price' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function options()
    {
        return $this->hasMany(ProductOption::class);
    }

    // method to register a media collection for the product
    public function registerMediaCollections(?Media $media = null): void
    {
        $this->addMediaConversion('optimized')
            ->format('webp')
            ->quality(90)
            ->nonQueued();
    }
}
