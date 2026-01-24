<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDonorPortalSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('donor_portal_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->boolean('enabled')->default(true);
            $table->boolean('allow_appointment_booking')->default(true);
            $table->boolean('allow_eligibility_self_check')->default(true);
            $table->boolean('show_inventory_status')->default(false);
            $table->boolean('send_appointment_reminders')->default(true);
            $table->boolean('send_donation_records')->default(true);
            $table->integer('appointment_reminder_hours')->default(24);
            $table->json('custom_fields')->nullable();
            $table->json('features')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('donor_portal_settings');
    }
}
