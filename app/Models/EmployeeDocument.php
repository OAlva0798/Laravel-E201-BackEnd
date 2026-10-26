<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $table = 'employee_documents';

    protected $primaryKey = 'eed_id';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'eed_created_at';
    const UPDATED_AT = 'eed_updated_at';
    
    protected $fillable = [
        'eed_id_number',
        'eed_type',
        'eed_file',
        'eed_file_ext',
    ];

    public function getRouteKeyName(): string
    {
        return 'eed_id_number';
    }
}