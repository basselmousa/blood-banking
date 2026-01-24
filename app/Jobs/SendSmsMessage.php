<?php

namespace App\Jobs;

use App\Models\SmsMessage;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSmsMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $smsMessage;

    public function __construct(SmsMessage $smsMessage)
    {
        $this->smsMessage = $smsMessage;
        $this->onQueue('sms');
        $this->tries = 3;
        $this->timeout = 30;
    }

    public function handle(SmsService $smsService)
    {
        $smsService->deliverSms($this->smsMessage);
    }
}
