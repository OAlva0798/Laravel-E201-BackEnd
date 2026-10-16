<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetails extends Model
{
    protected $table = 'user_details';

    protected $primaryKey = 'ud_id';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'ud_created_at';
    const UPDATED_AT = 'ud_updated_at';

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'ud_uniqid',

        // Files
        'ud_avatar',
        'ud_esign',

        // Personal Information
        'ud_firstname',
        'ud_middlename',
        'ud_lastname',
        'ud_email',

        // Organization
        'ud_center_reference',
        'ud_department_reference',
        'ud_position_reference',

        // Permissions
        'ud_center_head',
        'ud_is_approver',

        // Status
        'ud_status',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'ud_id' => 'integer',
        'ud_created_at' => 'datetime',
        'ud_updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Appended Attributes
    |--------------------------------------------------------------------------
    */

    protected $appends = [
        'ud_avatar_url',
        'ud_esign_url',
        'full_name',
    ];

    /*
    |--------------------------------------------------------------------------
    | Route Model Binding
    |--------------------------------------------------------------------------
    */

    public function getRouteKeyName(): string
    {
        return 'ud_uniqid';
    }

    /*
    |--------------------------------------------------------------------------
    | Full Name
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute(): string
    {
        return collect([
            $this->ud_firstname,
            $this->ud_middlename,
            $this->ud_lastname,
        ])
            ->filter(fn ($value) => filled($value))
            ->implode(' ');
    }

    /*
    |--------------------------------------------------------------------------
    | Avatar URL
    |--------------------------------------------------------------------------
    */

    public function getUdAvatarUrlAttribute(): ?string
    {
        if (empty($this->ud_avatar)) {
            return null;
        }

        if (
            str_starts_with($this->ud_avatar, 'http://') ||
            str_starts_with($this->ud_avatar, 'https://') ||
            str_starts_with($this->ud_avatar, 'data:')
        ) {
            return $this->ud_avatar;
        }

        return asset(
            'storage/' . ltrim($this->ud_avatar, '/')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | E-Signature URL
    |--------------------------------------------------------------------------
    */

    public function getUdEsignUrlAttribute(): ?string
    {
        if (empty($this->ud_esign)) {
            return null;
        }

        if (
            str_starts_with($this->ud_esign, 'http://') ||
            str_starts_with($this->ud_esign, 'https://') ||
            str_starts_with($this->ud_esign, 'data:')
        ) {
            return $this->ud_esign;
        }

        return asset(
            'storage/' . ltrim($this->ud_esign, '/')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Center
    |--------------------------------------------------------------------------
    */

    public function center()
    {
        return $this->belongsTo(
            Center::class,
            'ud_center_reference',
            'mc_reference'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Department
    |--------------------------------------------------------------------------
    */

    public function department()
    {
        return $this->belongsTo(
            Department::class,
            'ud_department_reference',
            'md_reference'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Position
    |--------------------------------------------------------------------------
    */

    public function position()
    {
        return $this->belongsTo(
            Position::class,
            'ud_position_reference',
            'mp_reference'
        );
    }
}
