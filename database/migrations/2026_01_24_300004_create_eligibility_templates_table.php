<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEligibilityTemplatesTable extends Migration
{
    public function up()
    {
        Schema::create('eligibility_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name'); // Template name
            $table->text('description')->nullable();
            $table->json('criteria'); // Eligibility criteria JSON
            $table->json('age_range'); // {min: 18, max: 65}
            $table->json('hemoglobin_levels'); // {min_male: 12.5, min_female: 12.0}
            $table->json('weight_limit'); // {min_kg: 50}
            $table->json('blood_pressure'); // {max_systolic: 180, max_diastolic: 100}
            $table->json('custom_questions')->nullable(); // Custom yes/no questions
            $table->json('deferral_periods')->nullable(); // {fever: 14, surgery: 30, ...}
            $table->json('medications_to_defer')->nullable(); // List of medications
            $table->json('conditions_to_defer')->nullable(); // Medical conditions
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('version')->default(1);
            $table->timestamps();
            
            $table->unique(['tenant_id', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('eligibility_templates');
    }
}
