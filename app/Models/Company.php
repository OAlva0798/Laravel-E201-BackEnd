<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'maintenance_company';

    protected $primaryKey = 'mco_id';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'mco_created_at';
    const UPDATED_AT = 'mco_updated_at';

    protected $fillable = [
        'mco_reference',
        'mco_name',
        'mco_status',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'mco_reference';
    }

    /**
     * Define the relationship to the center.
     */
    public function center()
    {
        return $this->belongsTo(Center::class, 'mco_center_reference', 'mc_reference');
    }
}