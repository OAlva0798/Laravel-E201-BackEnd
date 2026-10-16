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
        Schema::create('system_setting', function (Blueprint $table) {
            $table->id('ss_id'); // Sets up an auto-incrementing primary key safely
            $table->text('ss_field')->nullable();
            $table->text('ss_value')->nullable();
            $table->enum('ss_type', [
                'text',
                'password',
                'email',
                'number',
                'url',
                'tel',
                'date',
                'time',
                'datetime-local',
                'month',
                'week',
                'select',
                'textarea',
                'quill',
                'checkbox',
                'switch',
                'color',
                'range',
                'image',
                'video',
            ])->default('text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_setting');
    }
};