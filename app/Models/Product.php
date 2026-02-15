<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Enums\Fit;
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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('product_images');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 400, 400)
            ->format('webp')
            ->quality(80)
            ->optimize()
            ->nonQueued();

        $this->addMediaConversion('medium')
            ->width(1200)
            ->format('webp')
            ->quality(85)
            ->optimize()
            ->nonQueued();

        $this->addMediaConversion('large')
            ->width(2000)
            ->format('webp')
            ->quality(85)
            ->optimize()
            ->nonQueued();
    }
}
