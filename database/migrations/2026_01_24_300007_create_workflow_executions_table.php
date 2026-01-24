<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkflowExecutionsTable extends Migration
{
    public function up()
    {
        Schema::create('workflow_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('workflows')->cascadeOnDelete();
            $table->string('entity_type');
            $table->foreignId('entity_id');
            $table->string('status')->default('pending'); // pending, running, completed, failed
            $table->integer('completed_steps')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('execution_log')->nullable();
            $table->timestamps();
            
            $table->index(['workflow_id', 'status']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('workflow_executions');
    }
}
