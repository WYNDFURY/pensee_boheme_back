<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Gallery extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'name',
        'photographer',
        'slug',
        'description',
        'cover_image',
        'is_published',
        'order',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery_images');
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
