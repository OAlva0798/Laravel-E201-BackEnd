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
        Schema::create('maintenance_position', function (Blueprint $table) {
            // Maps to `mp_id` int(11) NOT NULL AUTO_INCREMENT (using standard id, or use integer('mp_id')->autoIncrement() if strict)
            $table->id('mp_id');
            
            // Maps to `mp_reference` text NOT NULL
            $table->text('mp_reference');
            
            // Maps to `mp_department_reference` text NOT NULL
            $table->text('mp_department_reference');
            
            // Maps to `mp_name` varchar(255) NOT NULL
            $table->string('mp_name', 255);
            
            // Maps to `mp_status` enum
            $table->enum('mp_status', ['Active', 'Inactive', 'Deleted'])->default('Active');
            
            // Maps to timestamps (`mp_created_at` and `mp_updated_at`)
            $table->timestamp('mp_created_at')->useCurrent();
            $table->timestamp('mp_updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_position');
    }
};