<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDonorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('donors', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('id_number')->unique();
            $table->string('email')->unique();
            $table->string('phone_number')->unique();
            $table->string('gender');
            $table->string('country');
            $table->string('city');
            $table->date('date_of_birth');
            $table->string('blood_group', 5);
            $table->date('last_donation_date');
            $table->text('diseases');
            $table->tinyInteger('share');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('donors');
    }
}
