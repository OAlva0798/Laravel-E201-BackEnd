<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class UsersAccount extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use MustVerifyEmailTrait;

    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    protected $table = 'users_account';

    /*
    |--------------------------------------------------------------------------
    | Primary Key
    |--------------------------------------------------------------------------
    */

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'uacc_reference',
        'uacc_email',
        'uacc_email_verified_at',
        'uacc_password',
        'uacc_type',
        'uacc_status',
        'uacc_failed_login_attempts',
        'uacc_locked_at',
        'uacc_locked_until',
        'uacc_last_login_at',
        'uacc_last_login_ip',
        'uacc_password_changed_at',
        'uacc_password_expires_at',
        'uacc_two_factor_enabled',
        'uacc_two_factor_secret',
        'uacc_two_factor_confirmed_at',
    ];

    /*
    |--------------------------------------------------------------------------
    | Hidden
    |--------------------------------------------------------------------------
    */

    protected $hidden = [
        'uacc_password',
        'uacc_two_factor_secret',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'uacc_email_verified_at' =>
                'datetime',

            'uacc_failed_login_attempts' =>
                'integer',

            'uacc_locked_at' =>
                'datetime',

            'uacc_locked_until' =>
                'datetime',

            'uacc_last_login_at' =>
                'datetime',

            'uacc_password_changed_at' =>
                'datetime',

            'uacc_password_expires_at' =>
                'datetime',

            'uacc_two_factor_enabled' =>
                'boolean',

            'uacc_two_factor_confirmed_at' =>
                'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    public function getAuthIdentifierName()
    {
        return 'uacc_email';
    }

    public function getAuthIdentifier()
    {
        return $this->uacc_email;
    }

    public function getAuthPassword()
    {
        return $this->uacc_password;
    }

    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    /*
    |--------------------------------------------------------------------------
    | Email Verification
    |--------------------------------------------------------------------------
    */

    public function getEmailForVerification()
    {
        return $this->uacc_email;
    }

    public function hasVerifiedEmail()
    {
        return !is_null(
            $this->uacc_email_verified_at
        );
    }

    public function markEmailAsVerified()
    {
        if ($this->hasVerifiedEmail()) {
            return false;
        }

        return $this->forceFill([
            'uacc_email_verified_at' =>
                now(),
        ])->save();
    }

    public function routeNotificationForMail(
        $notification = null
    ) {
        return $this->uacc_email;
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * User account -> login logs.
     */
    public function loginLogs()
    {
        return $this->hasMany(
            UserAccountLoginLog::class,
            'uacc_id',
            'id'
        );
    }

    /**
     * User account -> security logs.
     */
    public function securityLogs()
    {
        return $this->hasMany(
            UserAccountSecurityLog::class,
            'uacc_id',
            'id'
        );
    }

    /**
     * User account -> user details.
     *
     * users_account.uacc_reference
     *          ↓
     * user_details.ud_uniqid
     */
    public function userDetails()
    {
        return $this->hasOne(
            UserDetails::class,
            'ud_uniqid',
            'uacc_reference'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Account Helpers
    |--------------------------------------------------------------------------
    */

    public function isSuperAdmin(): bool
    {
        return $this->uacc_type === 'Super Admin';
    }

    public function isAdmin(): bool
    {
        return $this->uacc_type === 'Admin';
    }

    public function isUser(): bool
    {
        return $this->uacc_type === 'User';
    }

    public function hasType(string $type): bool
    {
        return $this->uacc_type === $type;
    }

    public function isActive(): bool
    {
        return $this->uacc_status === 'Active';
    }
}