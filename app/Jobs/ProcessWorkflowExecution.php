<?php

namespace App\Jobs;

use App\Models\WorkflowExecution;
use App\Services\WorkflowService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWorkflowExecution implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $executionId;

    public function __construct($executionId)
    {
        $this->executionId = $executionId;
    }

    public function handle()
    {
        $workflowService = app(WorkflowService::class);
        
        try {
            $workflowService->processExecution($this->executionId);
        } catch (\Exception $e) {
            $execution = WorkflowExecution::findOrFail($this->executionId);
            $execution->markAsFailed($e->getMessage());
            
            throw $e;
        }
    }
}
