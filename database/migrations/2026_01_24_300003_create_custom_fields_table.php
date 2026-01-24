<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomFieldsTable extends Migration
{
    public function up()
    {
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('entity_type'); // donor, donation, appointment, patient
            $table->string('field_name'); // Unique field identifier
            $table->string('field_label'); // Display label
            $table->string('field_type'); // text, email, phone, select, date, textarea, checkbox
            $table->text('field_description')->nullable();
            $table->json('field_options')->nullable(); // For select, radio, checkbox types
            $table->string('validation_rules')->nullable(); // required, email, etc.
            $table->string('default_value')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_searchable')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('conditional_logic')->nullable(); // Show field based on conditions
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->unique(['tenant_id', 'entity_type', 'field_name']);
            $table->index(['entity_type', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('custom_fields');
    }
}
