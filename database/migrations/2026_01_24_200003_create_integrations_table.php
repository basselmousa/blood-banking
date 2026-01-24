<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegrationsTable extends Migration
{
    public function up()
    {
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name'); // twilio, sendgrid, stripe, etc.
            $table->string('type'); // sms, email, payment, ehr
            $table->json('credentials'); // Encrypted credentials
            $table->json('config')->nullable(); // Provider-specific config
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sync_at')->nullable();
            $table->string('sync_status')->default('idle'); // idle, syncing, failed
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->unique(['tenant_id', 'name']);
            $table->index(['type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('integrations');
    }
}
