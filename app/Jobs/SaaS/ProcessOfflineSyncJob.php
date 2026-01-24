<?php

namespace App\Jobs\SaaS;

use App\Models\OfflineSync;
use App\Services\OfflineSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessOfflineSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $syncId;
    public $tries = 5;
    public $timeout = 60;

    public function __construct($syncId)
    {
        $this->syncId = $syncId;
    }

    public function handle(OfflineSyncService $offlineSyncService)
    {
        $sync = OfflineSync::find($this->syncId);

        if (!$sync) {
            return;
        }

        try {
            // Apply the offline sync
            $result = $offlineSyncService->applySync($sync);

            if ($result) {
                $sync->markAsSynced();
            } else {
                $sync->markAsFailed('Failed to apply sync');
                if ($sync->shouldRetry()) {
                    $this->release(60); // Retry after 60 seconds
                }
            }
        } catch (\Exception $e) {
            $sync->markAsFailed($e->getMessage());

            if ($sync->shouldRetry()) {
                $this->release(60); // Retry after 60 seconds
                throw $e;
            }
        }
    }

    public function failed(\Exception $exception)
    {
        $sync = OfflineSync::find($this->syncId);

        if ($sync) {
            $sync->markAsFailed('Job failed: ' . $exception->getMessage());
        }
    }
}
