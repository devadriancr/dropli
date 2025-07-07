<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StandardPack extends Model
{
    protected $fillable = [
        'name'
    ];

//    public function partNumbers(): HasMany
//    {
//        return $this->hasMany(PartNumber::class);
//    }
}
