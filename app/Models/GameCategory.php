<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Relationship: A GameCategory has many ArcadeGames.
     */
    public function games(): HasMany
    {
        return $this->hasMany(ArcadeGame::class, 'game_category_id');
    }
}
