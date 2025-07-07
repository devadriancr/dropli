<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectPrefix extends Model
{
    protected $fillable = [
        'prefix_code',
        'description',
        'project_id'
    ];

    /**
     *
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

}
