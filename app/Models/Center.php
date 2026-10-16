<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Center extends Model
{
    protected $table = 'maintenance_center';

    protected $primaryKey = 'mc_id';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'mc_created_at';
    const UPDATED_AT = 'mc_updated_at';

    protected $fillable = [
        'mc_reference',
        'mc_name',
        'mc_head_uaccid',
        'mc_head_position',
        'mc_status',
    ];

    public function getRouteKeyName(): string
    {
        return 'mc_reference';
    }
}