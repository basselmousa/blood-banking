<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDonationDeferralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('donation_deferrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('donor_id');
            $table->enum('deferral_type', ['temporary', 'permanent', 'conditional'])->default('temporary');
            $table->string('reason')->comment('Deferral reason');
            $table->text('description')->nullable();
            $table->date('deferral_date');
            $table->date('eligible_after')->nullable()->comment('When donor becomes eligible again');
            $table->boolean('is_active')->default(true);
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            
            $table->foreign('donor_id')->references('id')->on('donors')->onDelete('cascade');
            $table->index(['donor_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('donation_deferrals');
    }
}
