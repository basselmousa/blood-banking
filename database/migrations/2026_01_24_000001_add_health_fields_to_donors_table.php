<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHealthFieldsToDonorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('donors', function (Blueprint $table) {
            $table->integer('weight')->nullable()->comment('Weight in kg');
            $table->integer('height')->nullable()->comment('Height in cm');
            $table->string('blood_pressure')->nullable()->comment('e.g., 120/80');
            $table->decimal('hemoglobin_level', 5, 2)->nullable()->comment('g/dL');
            $table->date('last_health_checkup')->nullable();
            $table->json('health_conditions')->nullable()->comment('Detailed health conditions');
            $table->boolean('is_deferred')->default(false);
            $table->date('deferred_until')->nullable();
            $table->text('deferral_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('donors', function (Blueprint $table) {
            $table->dropColumn([
                'weight',
                'height',
                'blood_pressure',
                'hemoglobin_level',
                'last_health_checkup',
                'health_conditions',
                'is_deferred',
                'deferred_until',
                'deferral_reason'
            ]);
        });
    }
}
