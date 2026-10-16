<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $table = 'maintenance_position';

    protected $primaryKey = 'mp_id';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'mp_created_at';
    const UPDATED_AT = 'mp_updated_at';

    protected $fillable = [
        'mp_reference',
        'mp_department_reference',
        'mp_name',
        'mp_status',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'mp_reference';
    }

    /**
     * Define the relationship to the department.
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'mp_department_reference', 'md_reference');
    }
}