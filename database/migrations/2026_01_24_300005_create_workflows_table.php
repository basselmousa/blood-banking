<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkflowsTable extends Migration
{
    public function up()
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('trigger_type'); // donor.created, donation.recorded, appointment.scheduled, etc.
            $table->json('trigger_conditions')->nullable(); // Conditions for triggering
            $table->boolean('is_active')->default(true);
            $table->integer('execution_order')->default(0);
            $table->timestamps();
            
            $table->unique(['tenant_id', 'name']);
            $table->index(['trigger_type', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('workflows');
    }
}
