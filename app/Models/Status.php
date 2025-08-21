<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    protected $fillable = [
        'key',
        'label',
        'color',
    ];


    public function productionPlans(): HasMany
    {
        return $this->hasMany(ProductionPlan::class);
    }

    public function productionRecords(): HasMany
    {
        return $this->hasMany(ProductionRecord::class, 'status_id');
    }
}
