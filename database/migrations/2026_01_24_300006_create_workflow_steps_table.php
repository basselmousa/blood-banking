<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkflowStepsTable extends Migration
{
    public function up()
    {
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('workflows')->cascadeOnDelete();
            $table->integer('step_number'); // Order of execution
            $table->string('action_type'); // send_email, send_sms, create_task, defer_donor, etc.
            $table->json('action_config'); // Configuration for the action
            $table->json('conditions')->nullable(); // Conditional logic
            $table->boolean('is_conditional')->default(false);
            $table->integer('wait_hours')->default(0); // Wait before executing
            $table->timestamps();
            
            $table->unique(['workflow_id', 'step_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('workflow_steps');
    }
}
