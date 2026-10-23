<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SoftwareCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Relationship: A category has many software catalog items.
     */
    public function software(): HasMany
    {
        return $this->hasMany(SoftwareCatalog::class, 'software_category_id');
    }

    /**
     * Relationship: Only published software in this category.
     */
    public function publishedSoftware(): HasMany
    {
        return $this->hasMany(SoftwareCatalog::class, 'software_category_id')
            ->where('status', SoftwareCatalog::STATUS_TAYANGKAN);
    }

    protected static function booted(): void
    {
        static::creating(function (SoftwareCategory $cat) {
            if (blank($cat->slug) && filled($cat->name)) {
                $cat->slug = Str::slug($cat->name);
            }
        });
    }
}
