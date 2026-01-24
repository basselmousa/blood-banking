<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomDomainsTable extends Migration
{
    public function up()
    {
        Schema::create('custom_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('domain')->unique(); // custom.domain.com
            $table->string('base_domain')->nullable(); // bloodbank.com (for subdomain)
            $table->boolean('is_primary')->default(false); // Primary domain for tenant
            $table->boolean('is_verified')->default(false);
            $table->string('verification_token')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('ssl_certificate')->nullable();
            $table->text('ssl_key')->nullable();
            $table->timestamp('ssl_expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('dns_records')->nullable(); // For verification
            $table->timestamps();
            
            $table->unique(['tenant_id', 'domain']);
            $table->index(['is_verified']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('custom_domains');
    }
}
