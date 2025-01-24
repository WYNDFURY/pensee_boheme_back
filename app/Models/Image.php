<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'path',
        'alt_text',
    ];

    /**
     * Get the parent model (product, category, etc.) that the image belongs to.
     */
    public function imageable()
    {
        return $this->morphTo();
    }
}
