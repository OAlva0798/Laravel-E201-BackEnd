<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAccountSecurityLog extends Model
{
    use HasFactory;

    protected $table = 'user_account_security_logs';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'uacc_id',
        'uacc_reference',
        'uacc_type',
        'uacc_action',
        'uacc_description',
        'uacc_ip_address',
        'uacc_user_agent',
        'uacc_performed_at',
    ];

    protected function casts(): array
    {
        return [
            'uacc_performed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(
            UsersAccount::class,
            'uacc_id',
            'id'
        );
    }
}