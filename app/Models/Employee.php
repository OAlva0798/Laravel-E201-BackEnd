<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $primaryKey = 'ee_id';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'ee_created_at';
    const UPDATED_AT = 'ee_updated_at';
    
    protected $fillable = [
        // Personal Information
        'ee_profile',
        'ee_firstname',
        'ee_middlename',
        'ee_lastname',
        'ee_gender',
        'ee_birth_date',
        'ee_civil_status',

        // Contact Information
        'ee_email',
        'ee_mobile_number',
        'ee_telephone',

        // Government Information
        'ee_sss',
        'ee_tinid',
        'ee_bloodtype',

        // Emergency Contact Information
        'ee_ecname',
        'ee_eccontact',
        'ee_ecaddress',

        // Company and Organizational Assignment
        'ee_company_reference',
        'ee_center_reference',
        'ee_department_reference',
        'ee_job_family',
        'ee_position_level_reference',
        'ee_position_reference',
        'ee_employment_status',

        // Employment Dates
        'ee_date_hired',
        'ee_date_hired_tdg',
        'ee_date_contractual_start',
        'ee_date_contractual_end',

        // Employee Identification and Biometrics
        'ee_id_number',
        'ee_id_biometrics',

        // Record Status
        'ee_status',

        // Audit Information
        'ee_created_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'ee_birth_date' => 'date',
            'ee_date_hired' => 'date',
            'ee_date_hired_tdg' => 'date',
            'ee_date_contractual_start' => 'date',
            'ee_date_contractual_end' => 'date',
        ];
    }
}