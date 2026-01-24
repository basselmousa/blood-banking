<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebhooksTable extends Migration
{
    public function up()
    {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('event_type'); // donor.created, donation.recorded, etc.
            $table->string('url');
            $table->text('description')->nullable();
            $table->string('secret')->nullable(); // For HMAC signature verification
            $table->json('filters')->nullable(); // Filter events by conditions
            $table->json('headers')->nullable(); // Custom headers
            $table->integer('retry_count')->default(3);
            $table->integer('retry_delay')->default(300); // seconds
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->integer('total_deliveries')->default(0);
            $table->integer('failed_deliveries')->default(0);
            $table->timestamps();
            
            $table->index(['tenant_id', 'event_type']);
            $table->index(['is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('webhooks');
    }
}
