<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mobile_devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('user_id');
            $table->string('device_id')->unique();
            $table->enum('device_type', ['ios', 'android', 'web'])->default('android');
            $table->string('device_name')->nullable();
            $table->string('os_version')->nullable();
            $table->string('app_version')->nullable();
            $table->string('fcm_token')->nullable(); // Firebase Cloud Messaging
            $table->string('apns_token')->nullable(); // Apple Push Notification service
            $table->boolean('notifications_enabled')->default(true);
            $table->boolean('biometric_enabled')->default(false);
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['tenant_id', 'user_id']);
            $table->index(['device_type', 'notifications_enabled']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_devices');
    }
};
