<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomBrandingTable extends Migration
{
    public function up()
    {
        Schema::create('custom_branding', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('logo_path')->nullable(); // Path to uploaded logo
            $table->string('favicon_path')->nullable();
            $table->string('primary_color')->default('#007bff');
            $table->string('secondary_color')->default('#6c757d');
            $table->string('accent_color')->default('#28a745');
            $table->string('text_color')->default('#212529');
            $table->string('background_color')->default('#ffffff');
            $table->string('font_family')->default('Segoe UI, Tahoma, sans-serif');
            $table->text('custom_css')->nullable(); // Custom CSS rules
            $table->text('custom_html_header')->nullable(); // Custom HTML in header
            $table->text('custom_html_footer')->nullable(); // Custom HTML in footer
            $table->string('company_name')->nullable();
            $table->text('company_description')->nullable();
            $table->string('support_email')->nullable();
            $table->string('support_phone')->nullable();
            $table->text('privacy_policy_url')->nullable();
            $table->text('terms_of_service_url')->nullable();
            $table->boolean('hide_saas_branding')->default(false);
            $table->boolean('show_powered_by')->default(true);
            $table->json('email_templates')->nullable(); // Custom email template HTML
            $table->json('report_branding')->nullable(); // PDF report branding
            $table->timestamps();
            
            $table->unique('tenant_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('custom_branding');
    }
}
