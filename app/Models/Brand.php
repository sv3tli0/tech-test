<?php

namespace App\Models;

use App\Casts\OptionalImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    /** @use HasFactory<\Database\Factories\BrandFactory> */
    use HasFactory;

    // Set primary to match requirement.
    protected $primaryKey = 'brand_id';

    protected $fillable = [
        'brand_name',
        'brand_image',
        'rating',
    ];

    protected $casts = [
        'brand_image' => OptionalImage::class,
    ];
}
