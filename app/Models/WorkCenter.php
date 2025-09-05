<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkCenter extends Model
{
    protected $fillable = [
        'number',
        'name',
        'area_id'
    ];

    /**
     *
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    /**
     *
     */
    public function partNumbers(): HasMany
    {
        return $this->hasMany(PartNumber::class, 'work_center_id');
    }


    /**
     *
     */
    public function downtimeRecord(): HasMany
    {
        return $this->hasMany(DowntimeRecord::class, 'work_center_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_work_center');
    }
}
