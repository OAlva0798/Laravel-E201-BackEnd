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
        Schema::create('user_details', function (Blueprint $table) {
            $table->integer('ud_id')->autoIncrement();
            $table->string('ud_uniqid', 100)->nullable();
            $table->text('ud_avatar')->nullable();
            $table->string('ud_firstname', 50)->nullable();
            $table->string('ud_middlename', 50)->nullable();
            $table->string('ud_lastname', 50)->nullable();
            $table->string('ud_email', 100)->nullable();
            $table->enum('ud_center_head', ['Yes', 'No'])->default('No');
            $table->text('ud_center_reference')->nullable();
            $table->text('ud_department_reference')->nullable();
            $table->text('ud_position_reference')->nullable();
            $table->enum('ud_status', ['Active', 'Inactive', 'Deleted'])->default('Active');
            $table->enum('ud_is_approver', ['Yes', 'No'])->default('No');
            $table->text('ud_esign')->nullable();
            $table->timestamp('ud_created_at')->useCurrent();
            $table->timestamp('ud_updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};