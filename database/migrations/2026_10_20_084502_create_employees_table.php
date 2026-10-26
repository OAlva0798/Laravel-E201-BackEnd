<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            // Primary Key
            $table->id('ee_id');

            // Personal Information
            $table->string('ee_profile')->nullable();
            $table->string('ee_firstname', 100);
            $table->string('ee_middlename', 100)->nullable();
            $table->string('ee_lastname', 100);
            $table->string('ee_gender')->nullable();
            $table->date('ee_birth_date')->nullable();
            $table->string('ee_civil_status')->nullable();

            // Contact Information
            $table->string('ee_email')->nullable();
            $table->string('ee_mobile_number', 30)->nullable();
            $table->string('ee_telephone', 30)->nullable();

            // Government Information
            $table->string('ee_sss', 30)->nullable();
            $table->string('ee_tinid', 30)->nullable();
            $table->string('ee_bloodtype', 10)->nullable();

            // Emergency Contact Information
            $table->string('ee_ecname', 150)->nullable();
            $table->string('ee_eccontact', 30)->nullable();
            $table->string('ee_ecaddress', 150)->nullable();

            // Company and Organizational Assignment
            $table->string('ee_company_reference')->nullable();
            $table->string('ee_center_reference')->nullable();
            $table->string('ee_department_reference')->nullable();
            $table->string('ee_position_reference')->nullable();
            $table->string('ee_position_level_reference')->nullable();
            $table->string('ee_job_family')->nullable();
            $table->string('ee_employment_status')->nullable();

            // Employee Identification and Biometrics
            $table->string('ee_id_number', 50)->unique();
            $table->string('ee_id_biometrics')->nullable();
            
            // Employment Dates
            $table->date('ee_date_hired')->nullable();
            $table->date('ee_date_hired_tdg')->nullable();
            $table->date('ee_date_contractual_start')->nullable();
            $table->date('ee_date_contractual_end')->nullable();


            // Record Status
            $table->enum('ee_status', [
                'Active',
                'Inactive',
                'Deleted'
            ])->default('Active');

            // Audit Information
            $table->string('ee_created_by')->nullable();

            $table->timestamp('ee_created_at')
                ->useCurrent();

            $table->dateTime('ee_updated_at')
                ->useCurrent()
                ->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ees');
    }
};