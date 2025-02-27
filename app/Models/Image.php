<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'path',
        'alt_text',
        'category_id',
        'product_id',
        'gallery_id',
        'order',
    ];

    protected $dates = ['deleted_at'];

    protected $appends = ['url'];

    // New direct relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // For future gallery implementation
    public function gallery()
    {
        return $this->belongsTo(Gallery::class);
    }

    // Get the URL for the image
    public function getUrlAttribute()
    {
        return Storage::disk('public')->url($this->path);
    }

    // Helper method to find the associated model regardless of type
    public function getAssociatedModel()
    {
        if ($this->category_id) {
            return $this->category;
        } elseif ($this->product_id) {
            return $this->product;
        } elseif ($this->gallery_id) {
            return $this->gallery;
        }
    }

    // Boot method to handle the automatic file deletion
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($image) {
            Storage::disk('public')->delete($image->path);
        });
    }
}
