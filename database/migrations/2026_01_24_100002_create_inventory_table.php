<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('blood_type'); // O+, O-, A+, A-, B+, B-, AB+, AB-
            $table->string('component_type'); // whole_blood, red_cells, plasma, platelets, cryoprecipitate
            $table->integer('quantity')->default(0);
            $table->integer('quantity_unit')->default(1); // units or ml
            $table->date('expiration_date')->nullable();
            $table->string('storage_location')->nullable();
            $table->integer('critical_level')->default(5); // Alert when below this
            $table->integer('maximum_level')->default(50); // Max storage
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->unique(['tenant_id', 'blood_type', 'component_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory');
    }
}
