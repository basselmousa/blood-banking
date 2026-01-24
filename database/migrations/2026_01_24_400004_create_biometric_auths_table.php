<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('biometric_auths', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('mobile_device_id');
            $table->enum('auth_type', ['fingerprint', 'face_recognition', 'iris'])->default('fingerprint');
            $table->text('encrypted_token');
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_primary')->default(false);
            $table->integer('failed_attempts')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('locked_until')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('mobile_device_id')->references('id')->on('mobile_devices')->onDelete('cascade');
            $table->unique(['user_id', 'mobile_device_id', 'auth_type']);
            $table->index(['user_id', 'is_enabled']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('biometric_auths');
    }
};
