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
        Schema::create('maintenance_company', function (Blueprint $table) {
            $table->id('mco_id');

            $table->text('mco_reference');
            $table->string('mco_name', 255);

            $table->enum('mco_status', [
                'Active',
                'Inactive',
                'Deleted'
            ])->default('Active');

            $table->timestamp('mco_created_at')
                ->useCurrent();

            $table->dateTime('mco_updated_at')
                ->useCurrent()
                ->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_company');
    }
};