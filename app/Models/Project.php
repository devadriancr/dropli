<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'code',
        'model',
        'description',
        'customer_id',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function projectPrefixes(): HasMany
    {
        return $this->hasMany(ProjectPrefix::class, 'project_id');
    }

    /**
     *
     */
    public function partNumbers(): BelongsToMany
    {
        return $this->belongsToMany(PartNumber::class, 'part_number_project', 'project_id', 'part_number_id');
    }
}
