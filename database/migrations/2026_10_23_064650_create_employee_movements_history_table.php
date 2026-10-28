<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_movements_history', function (Blueprint $table) {
            $table->id('eemh_id');

            $table->string('eemh_id_number');
            $table->string('eemh_company_reference')->nullable();
            $table->string('eemh_center_reference')->nullable();
            $table->string('eemh_department_reference')->nullable();
            $table->string('eemh_position_reference')->nullable();
            $table->string('eemh_position_level_reference')->nullable();
            $table->string('eemh_job_family')->nullable();
            $table->string('eemh_employment_status')->nullable();
            $table->date('eemh_date_effective')->nullable();
            $table->string('eemh_remarks')->nullable();

            $table->timestamp('eemh_created_at')->useCurrent();

            $table->dateTime('eemh_updated_at')
                ->useCurrent()
                ->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_movements_history');
    }
};