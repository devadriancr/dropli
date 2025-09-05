<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScrapCategory extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];

    /**
     * Get all scrap reasons belonging to this category
     */
    public function scrapReasons(): HasMany
    {
        return $this->hasMany(ScrapReason::class, 'scrap_category_id');
    }
}
