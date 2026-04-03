<?php

namespace App\Support;

use Illuminate\Support\Str;

class ProductImagePath
{
    public static function resolve(string $productName): string
    {
        $slug = Str::slug($productName);
        $candidates = [
            "images/products/{$slug}.jpg",
            "images/products/{$slug}.jpeg",
            "images/products/{$slug}.png",
            "images/products/{$slug}.webp",
        ];

        foreach ($candidates as $candidate) {
            if (file_exists(public_path($candidate))) {
                return $candidate;
            }
        }

        return 'images/products/placeholder.svg';
    }
}
