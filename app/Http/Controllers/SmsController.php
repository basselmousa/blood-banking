<?php

namespace App\Http\Controllers;

use App\Models\SmsMessage;
use App\Services\SmsService;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $status = $request->query('status');
        $type = $request->query('type');
        $limit = $request->query('limit', 50);

        $query = SmsMessage::where('tenant_id', $tenantId);

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $messages = $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $messages,
            'total' => $messages->count(),
        ]);
    }

    public function show(SmsMessage $message)
    {
        $this->authorize('view', $message);

        return response()->json($message);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string|regex:/^\+?[0-9\-\(\) ]+$/',
            'message' => 'required|string|max:1000',
            'type' => 'nullable|string',
        ]);

        $tenantId = auth()->user()->tenant_id;

        try {
            $smsMessage = $this->smsService->sendSms(
                $validated['phone_number'],
                $validated['message'],
                $validated['type'] ?? 'general',
                null,
                $tenantId
            );

            return response()->json([
                'message' => 'SMS queued for delivery',
                'data' => $smsMessage,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send SMS',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function statistics(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $period = $request->query('period', 'month');

        $stats = $this->smsService->getSmsStatistics($tenantId, $period);

        return response()->json($stats);
    }

    public function resend(Request $request, SmsMessage $message)
    {
        $this->authorize('update', $message);

        if (!$message->canRetry()) {
            return response()->json([
                'message' => 'Message cannot be retried',
            ], 422);
        }

        try {
            \App\Jobs\SendSmsMessage::dispatch($message);

            return response()->json([
                'message' => 'SMS queued for retry',
                'data' => $message,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retry SMS',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function export(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $period = $request->query('period', 'month');

        $query = SmsMessage::where('tenant_id', $tenantId);

        if ($period === 'month') {
            $query->whereMonth('created_at', now()->month);
        } elseif ($period === 'week') {
            $query->whereDate('created_at', '>=', now()->subWeek());
        }

        $messages = $query->get();

        $csv = "Date,Phone,Message,Type,Status,Provider\n";
        foreach ($messages as $msg) {
            $csv .= "{$msg->created_at->format('Y-m-d H:i:s')},";
            $csv .= "{$msg->phone_number},";
            $csv .= "\"{$msg->message}\",";
            $csv .= "{$msg->type},{$msg->status},{$msg->provider}\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="sms_export_' . now()->format('Y-m-d') . '.csv"');
    }
}
