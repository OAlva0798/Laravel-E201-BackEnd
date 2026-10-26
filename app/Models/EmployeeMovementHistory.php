<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeMovementHistory extends Model
{
    protected $table = 'employee_movements_history';

    protected $primaryKey = 'eemh_id';

    public $timestamps = false;

    protected $fillable = [
        'eemh_id_number',
        'eemh_company_reference',
        'eemh_center_reference',
        'eemh_department_reference',
        'eemh_position_reference',
        'eemh_position_level_reference',
        'eemh_job_family',
        'eemh_employment_status',
        'eemh_date_effective',
    ];

    protected function casts(): array
    {
        return [
            'eemh_created_at' => 'datetime',
            'eemh_updated_at' => 'datetime',
        ];
    }

    public const CREATED_AT = 'eemh_created_at';

    public const UPDATED_AT = 'eemh_updated_at';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class,
            'eemh_id_number',
            'ee_id_number'
        );
    }
}