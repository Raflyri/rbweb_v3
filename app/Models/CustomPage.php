<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'path',
        'template',
        'content',
        'is_active',
        'meta_title',
        'meta_description',
        'meta_robots',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Clean and normalize path: trim slashes and whitespace.
     */
    public function setPathAttribute(string $value): void
    {
        $this->attributes['path'] = trim($value, "/ \t\n\r\0\x0B");
    }

    /**
     * Helper to get full URL for the page.
     */
    public function getUrlAttribute(): string
    {
        return url($this->path);
    }
}
