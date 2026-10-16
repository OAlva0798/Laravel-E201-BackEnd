<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'system_setting';

    protected $primaryKey = 'ss_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'ss_field',
        'ss_value',
        'ss_type',
    ];

    /**
     * Cast database values.
     */
    protected $casts = [
        'ss_id' => 'integer',
    ];
}