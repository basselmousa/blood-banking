<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('push_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('mobile_device_id')->nullable();
            $table->string('title');
            $table->text('body');
            $table->string('action_url')->nullable();
            $table->enum('notification_type', [
                'appointment_reminder',
                'eligibility_update',
                'donation_request',
                'appointment_scheduled',
                'system_alert',
                'custom'
            ])->default('custom');
            $table->json('data')->nullable(); // Additional payload
            $table->enum('status', ['sent', 'failed', 'read', 'pending'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('mobile_device_id')->references('id')->on('mobile_devices')->onDelete('cascade');
            $table->index(['tenant_id', 'user_id', 'status']);
            $table->index(['notification_type', 'sent_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('push_notifications');
    }
};
