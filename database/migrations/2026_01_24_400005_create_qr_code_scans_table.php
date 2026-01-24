<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('qr_code_scans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('mobile_device_id')->nullable();
            $table->string('entity_type'); // donor, donation, blood_product, etc.
            $table->unsignedBigInteger('entity_id');
            $table->string('qr_code_value');
            $table->enum('scan_status', ['success', 'failed', 'not_found', 'invalid'])->default('success');
            $table->text('error_message')->nullable();
            $table->json('scan_metadata')->nullable(); // location, timestamp, etc.
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('mobile_device_id')->references('id')->on('mobile_devices')->onDelete('set null');
            $table->index(['tenant_id', 'entity_type', 'entity_id']);
            $table->index(['created_at', 'scan_status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('qr_code_scans');
    }
};
