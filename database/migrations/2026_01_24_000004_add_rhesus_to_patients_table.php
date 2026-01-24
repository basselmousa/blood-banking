<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRhesusToPatentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('rhesus_factor')->default('+')->comment('+ or -');
            $table->json('special_requirements')->nullable()->comment('Special blood requirements');
            $table->json('transfusion_history')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['rhesus_factor', 'special_requirements', 'transfusion_history']);
        });
    }
}
