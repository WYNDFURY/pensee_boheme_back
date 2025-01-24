<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'is_active',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Get the category associated with the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->morphMany(\App\Models\Image::class, 'imageable');
    }
}
