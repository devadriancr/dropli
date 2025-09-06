<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PartNumber extends Model
{
    protected $fillable = [
        'number',
        'name',
        'production_rate',
        'efficiency',
        'item_class_id',
        'work_center_id',
        'standard_pack_id',
        'standard_pack_quantity',
        'is_obsolete',
    ];

    /**
     * Get the item class that owns the part number
     */
    public function itemClass(): BelongsTo
    {
        return $this->belongsTo(ItemClass::class, 'item_class_id');
    }

    /**
     * Get the work center that owns the part number
     */
    public function workCenter(): BelongsTo
    {
        return $this->belongsTo(WorkCenter::class, 'work_center_id');
    }

    /**
     * Get the standard pack that owns the part number
     */
    public function standardPack(): BelongsTo
    {
        return $this->belongsTo(StandardPack::class, 'standard_pack_id');
    }

    /**
     * Get the projects that belong to the part number
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'part_number_project', 'part_number_id', 'project_id');
    }

    /**
     * Get the production plans for the part number
     */
    public function productionPlans(): HasMany
    {
        return $this->hasMany(ProductionPlan::class, 'part_number_id');
    }

    /**
     * Get the scrap records for the part number
     */
    public function scrapRecords(): HasMany
    {
        return $this->hasMany(ScrapRecord::class, 'part_number_id');
    }

    /**
     * Get the production records for the part number
     */
    public function productionRecords(): HasMany
    {
        return $this->hasMany(ProductionRecord::class, 'part_number_id');
    }

    /**
     * Get all attributes for the part number
     */
    public function attributes(): MorphMany
    {
        return $this->morphMany(Attribute::class, 'attributable');
    }

    /**
     * Get the next processes in the sequence
     */
    public function nextProcesses()
    {
        return $this->belongsToMany(
            PartNumber::class,
            'part_number_sequences',
            'current_part_number_id',
            'next_part_number_id'
        )
            ->using(PartNumberSequence::class)
            ->withPivot('sequence_order', 'lead_time_hours', 'is_active')
            ->wherePivot('is_active', true)
            ->orderBy('pivot_sequence_order');
    }

    /**
     * Get the previous processes in the sequence
     */
    public function previousProcesses()
    {
        return $this->belongsToMany(
            PartNumber::class,
            'part_number_sequences',
            'next_part_number_id',
            'current_part_number_id'
        )
            ->using(PartNumberSequence::class)
            ->withPivot('sequence_order', 'lead_time_hours', 'is_active')
            ->wherePivot('is_active', true);
    }

    /**
     * Get a specific custom attribute value
     */
    public function getCustomAttributeValue(string $key)
    {
        $attribute = $this->attributes()->where('attribute_key', $key)->first();
        return $attribute ? $attribute->cast_value : null;
    }

    /**
     * Set a custom attribute value
     */
    public function setCustomAttributeValue(string $key, $value, ?string $dataType = null)
    {
        if (!$dataType) {
            $dataType = $this->detectDataType($value);
        }

        $this->attributes()->updateOrCreate(
            ['attribute_key' => $key],
            [
                'attribute_value' => $this->formatValue($value, $dataType),
                'data_type' => $dataType
            ]
        );
    }

    /**
     * Detect data type from value
     */
    protected function detectDataType($value): string
    {
        if (is_int($value)) return 'integer';
        if (is_float($value)) return 'double';
        if (is_bool($value)) return 'boolean';
        if (is_array($value)) return 'array';
        return 'string';
    }

    /**
     * Format value for storage
     */
    protected function formatValue($value, string $dataType): string
    {
        return match ($dataType) {
            'boolean' => $value ? 'true' : 'false',
            'array' => json_encode($value),
            default => (string) $value,
        };
    }
}
