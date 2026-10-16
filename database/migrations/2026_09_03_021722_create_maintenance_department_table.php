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
        Schema::create('maintenance_department', function (Blueprint $table) {
            $table->id('md_id'); // Maps to `md_id` int(11) NOT NULL AUTO_INCREMENT primary key
            $table->text('md_reference');
            $table->text('md_center_reference');
            $table->string('md_name', 255);
            $table->enum('md_status', ['Active', 'Inactive', 'Deleted'])->default('Active');
            
            // Custom timestamps to match your SQL definition
            $table->timestamp('md_created_at')->useCurrent();
            $table->timestamp('md_updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_department');
    }
};