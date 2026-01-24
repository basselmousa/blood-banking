<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('donor_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('blood_type')->nullable();
            $table->dateTime('scheduled_at');
            $table->dateTime('completed_at')->nullable();
            $table->string('status')->default('scheduled'); // scheduled, completed, cancelled, no_show
            $table->text('notes')->nullable();
            $table->string('appointment_type')->default('donation'); // donation, eligibility_screening, followup
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('donor_id')->references('id')->on('donors')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('bloods')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointments');
    }
}
