<?php

namespace App\Jobs;

use App\Models\EhrSync;
use App\Services\EhrSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncToEhr implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $sync;

    public function __construct(EhrSync $sync)
    {
        $this->sync = $sync;
        $this->onQueue('ehr');
        $this->tries = 3;
        $this->timeout = 60;
    }

    public function handle(EhrSyncService $ehrSyncService)
    {
        $ehrSyncService->syncToEhr($this->sync);
    }
}
