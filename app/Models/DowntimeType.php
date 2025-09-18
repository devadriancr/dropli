<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DowntimeType extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];

    public function downtimeReasons(): HasMany
    {
        return $this->hasMany(DowntimeReason::class, 'downtime_type_id');
    }
}
