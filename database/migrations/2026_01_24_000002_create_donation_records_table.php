<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDonationRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('donation_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('donor_id');
            $table->dateTime('donation_date');
            $table->integer('blood_volume')->default(450)->comment('Volume in ml');
            $table->enum('type', ['whole_blood', 'plasma', 'platelets', 'red_cells'])->default('whole_blood');
            $table->enum('status', ['completed', 'rejected', 'deferred', 'cancelled'])->default('completed');
            $table->text('rejection_reason')->nullable();
            $table->date('next_eligible_date')->nullable();
            $table->integer('hemoglobin_before')->nullable()->comment('Hemoglobin level before donation');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('donor_id')->references('id')->on('donors')->onDelete('cascade');
            $table->index(['donor_id', 'donation_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('donation_records');
    }
}
