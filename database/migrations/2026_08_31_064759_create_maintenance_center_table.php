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
        Schema::create('maintenance_center', function (Blueprint $table) {
            $table->id('mc_id');

            $table->text('mc_reference');
            $table->string('mc_name', 255);
            $table->string('mc_head_uaccid', 255)->nullable();
            $table->string('mc_head_position', 255)->nullable();

            $table->enum('mc_status', [
                'Active',
                'Inactive',
                'Deleted'
            ])->default('Active');

            $table->timestamp('mc_created_at')
                ->useCurrent();

            $table->dateTime('mc_updated_at')
                ->useCurrent()
                ->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_center');
    }
};