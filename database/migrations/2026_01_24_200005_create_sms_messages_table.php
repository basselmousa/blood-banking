<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsMessagesTable extends Migration
{
    public function up()
    {
        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->morphs('messageable'); // Donor, Appointment, etc.
            $table->string('phone_number');
            $table->text('message');
            $table->string('type'); // appointment_reminder, eligibility, donation_thank_you
            $table->string('status')->default('pending'); // pending, sent, failed, delivered
            $table->string('provider')->default('twilio'); // twilio, aws_sns
            $table->string('provider_message_id')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index(['messageable_type', 'messageable_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sms_messages');
    }
}
