<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id('eed_id');

            $table->string('eed_id_number');
            $table->string('eed_type');
            $table->string('eed_file')->nullable();
            $table->string('eed_file_ext')->nullable();

            $table->timestamp('eed_created_at')->useCurrent();

            $table->dateTime('eed_updated_at')
                ->useCurrent()
                ->useCurrentOnUpdate();

            $table->index(
                ['eed_id_number', 'eed_type'],
                'employee_documents_employee_type_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};