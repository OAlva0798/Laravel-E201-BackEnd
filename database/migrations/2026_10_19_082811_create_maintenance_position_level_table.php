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
        Schema::create('maintenance_position_level', function (Blueprint $table) {
            $table->id('mpl_id');

            $table->text('mpl_reference');
            $table->string('mpl_name', 255);

            $table->enum('mpl_status', [
                'Active',
                'Inactive',
                'Deleted'
            ])->default('Active');

            $table->timestamp('mpl_created_at')
                ->useCurrent();

            $table->dateTime('mpl_updated_at')
                ->useCurrent()
                ->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_position_level');
    }
};