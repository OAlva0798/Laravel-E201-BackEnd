<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'maintenance_department';

    protected $primaryKey = 'md_id';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'md_created_at';
    const UPDATED_AT = 'md_updated_at';

    protected $fillable = [
        'md_reference',
        'md_center_reference',
        'md_name',
        'md_status',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'md_reference';
    }

    /**
     * Define the relationship to the center.
     */
    public function center()
    {
        return $this->belongsTo(Center::class, 'md_center_reference', 'mc_reference');
    }
}