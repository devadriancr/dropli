<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description'
    ];

    /**
     *
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'customer_id');
    }
}
