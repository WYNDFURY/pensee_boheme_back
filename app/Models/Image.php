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
        'imageable_type',
        'imageable_id',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Get the parent model (product, category, etc.) that the image belongs to.
     */
    public function imageable()
    {
        return $this->morphTo();
    }

    protected static function boot()
    {
        parent::boot();

        // Delete the file when the model is deleted
        static::deleting(function ($image) {
            Storage::disk('public')->delete($image->path);
        });
    }

    public function getUrlAttribute()
    {
        return Storage::disk('public')->url($this->path);
    }

    protected $appends = ['url'];
}
