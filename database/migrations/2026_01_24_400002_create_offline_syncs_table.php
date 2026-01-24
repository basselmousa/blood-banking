<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('offline_syncs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('mobile_device_id');
            $table->string('entity_type'); // donor, donation, appointment, etc.
            $table->unsignedBigInteger('entity_id');
            $table->enum('action', ['create', 'update', 'delete'])->default('create');
            $table->json('data'); // The entity data to sync
            $table->json('changes')->nullable(); // What changed (for updates)
            $table->enum('status', ['pending', 'synced', 'failed', 'conflict'])->default('pending');
            $table->text('error_message')->nullable();
            $table->json('conflict_data')->nullable(); // Data from server if conflict
            $table->timestamp('synced_at')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('mobile_device_id')->references('id')->on('mobile_devices')->onDelete('cascade');
            $table->index(['tenant_id', 'user_id', 'status']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_syncs');
    }
};
