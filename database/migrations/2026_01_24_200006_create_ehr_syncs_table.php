<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEhrSyncsTable extends Migration
{
    public function up()
    {
        Schema::create('ehr_syncs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('integration_id')->constrained('integrations')->cascadeOnDelete();
            $table->string('entity_type'); // donor, donation, appointment
            $table->foreignId('entity_id');
            $table->string('external_id')->nullable(); // ID in external system
            $table->string('direction'); // to_ehr, from_ehr, bidirectional
            $table->string('status')->default('pending'); // pending, synced, failed, conflict
            $table->json('external_data')->nullable();
            $table->json('local_data')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            $table->unique(['integration_id', 'entity_type', 'entity_id']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ehr_syncs');
    }
}
