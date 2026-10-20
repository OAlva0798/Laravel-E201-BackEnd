<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PositionLevel extends Model
{
    protected $table = 'maintenance_position_level';

    protected $primaryKey = 'mpl_id';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'mpl_created_at';
    const UPDATED_AT = 'mpl_updated_at';

    protected $fillable = [
        'mpl_reference',
        'mpl_name',
        'mpl_status',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'mpl_reference';
    }

    /**
     * Define the relationship to the center.
     */
    public function center()
    {
        return $this->belongsTo(Center::class, 'mpl_center_reference', 'mc_reference');
    }
}