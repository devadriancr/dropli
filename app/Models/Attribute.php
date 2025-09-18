<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attribute extends Model
{
    protected $fillable = [
        'attribute_key',
        'attribute_value',
        'data_type'
    ];

    /**
     * Get the parent attributable model.
     */
    public function attributable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Cast the attribute value based on data type
     */
    public function getCastValueAttribute()
    {
        return match ($this->data_type) {
            'integer' => (int) $this->attribute_value,
            'double' => (float) $this->attribute_value,
            'boolean' => filter_var($this->attribute_value, FILTER_VALIDATE_BOOLEAN),
            'array' => json_decode($this->attribute_value, true),
            default => $this->attribute_value,
        };
    }
}
