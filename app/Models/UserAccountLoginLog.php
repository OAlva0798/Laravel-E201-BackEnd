<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAccountLoginLog extends Model
{
    use HasFactory;

    protected $table = 'user_account_login_logs';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'uacc_id',
        'uacc_reference',
        'uacc_email',
        'uacc_type',
        'uacc_login_status',
        'uacc_failure_reason',
        'uacc_ip_address',
        'uacc_user_agent',
        'uacc_device',
        'uacc_browser',
        'uacc_platform',
        'uacc_session_id',
        'uacc_logged_at',
    ];

    protected function casts(): array
    {
        return [
            'uacc_logged_at' => 'datetime',
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