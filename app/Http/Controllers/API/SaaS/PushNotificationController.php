<?php

namespace App\Http\Controllers\API\SaaS;

use App\Http\Controllers\Controller;
use App\Models\PushNotification;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;

class PushNotificationController extends Controller
{
    protected $pushNotificationService;

    public function __construct(PushNotificationService $pushNotificationService)
    {
        $this->middleware('auth:api');
        $this->pushNotificationService = $pushNotificationService;
    }

    /**
     * Send a push notification
     * POST /api/v1/saas/push-notifications/send
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'device_id' => 'nullable|string',
            'title' => 'required|string|max:200',
            'body' => 'required|string|max:1000',
            'notification_type' => 'required|in:appointment_reminder,eligibility_update,donation_request,system_alert,custom',
            'action_url' => 'nullable|string',
            'data' => 'nullable|array',
        ]);

        $notification = $this->pushNotificationService->sendNotification(
            $validated['user_id'],
            $validated['device_id'] ?? null,
            $validated['title'],
            $validated['body'],
            $validated['notification_type'],
            $validated['action_url'] ?? null,
            $validated['data'] ?? []
        );

        return response()->json([
            'success' => true,
            'message' => 'Push notification sent',
            'notification' => $notification,
        ], 201);
    }

    /**
     * Send bulk push notifications
     * POST /api/v1/saas/push-notifications/send-bulk
     */
    public function sendBulk(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
            'title' => 'required|string|max:200',
            'body' => 'required|string|max:1000',
            'notification_type' => 'required|in:appointment_reminder,eligibility_update,donation_request,system_alert,custom',
            'action_url' => 'nullable|string',
            'data' => 'nullable|array',
        ]);

        $result = $this->pushNotificationService->sendBulkNotifications(
            $validated['user_ids'],
            auth()->user()->tenant_id,
            $validated['title'],
            $validated['body'],
            $validated['notification_type'],
            $validated['action_url'] ?? null,
            $validated['data'] ?? []
        );

        return response()->json([
            'success' => true,
            'message' => 'Bulk notifications sent',
            'result' => $result,
        ], 201);
    }

    /**
     * Send to all tenant users
     * POST /api/v1/saas/push-notifications/send-tenant
     */
    public function sendToTenant(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'body' => 'required|string|max:1000',
            'notification_type' => 'required|in:appointment_reminder,eligibility_update,donation_request,system_alert,custom',
            'action_url' => 'nullable|string',
            'exclude_user_ids' => 'nullable|array',
            'data' => 'nullable|array',
        ]);

        $result = $this->pushNotificationService->sendToTenantUsers(
            auth()->user()->tenant_id,
            $validated['title'],
            $validated['body'],
            $validated['notification_type'],
            $validated['action_url'] ?? null,
            $validated['exclude_user_ids'] ?? [],
            $validated['data'] ?? []
        );

        return response()->json([
            'success' => true,
            'message' => 'Notifications sent to all users',
            'result' => $result,
        ], 201);
    }

    /**
     * Get user notifications
     * GET /api/v1/saas/push-notifications
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'limit' => 'nullable|integer|min:1|max:100',
            'type' => 'nullable|in:appointment_reminder,eligibility_update,donation_request,system_alert,custom',
        ]);

        $query = PushNotification::where('user_id', auth()->id());

        if ($request->input('type')) {
            $query->where('notification_type', $request->input('type'));
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->limit($validated['limit'] ?? 50)
            ->get();

        return response()->json([
            'success' => true,
            'count' => $notifications->count(),
            'notifications' => $notifications,
        ]);
    }

    /**
     * Get a specific notification
     * GET /api/v1/saas/push-notifications/{id}
     */
    public function show(PushNotification $pushNotification)
    {
        $this->authorize('view', $pushNotification);

        return response()->json([
            'success' => true,
            'notification' => $pushNotification,
        ]);
    }

    /**
     * Mark notification as read
     * POST /api/v1/saas/push-notifications/{id}/mark-read
     */
    public function markRead(PushNotification $pushNotification)
    {
        $this->authorize('update', $pushNotification);

        $notification = $this->pushNotificationService->markAsRead($pushNotification->id);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'notification' => $notification,
        ]);
    }

    /**
     * Get unread count
     * GET /api/v1/saas/push-notifications/unread/count
     */
    public function unreadCount(Request $request)
    {
        $count = $this->pushNotificationService->getUnreadCount(auth()->id());

        return response()->json([
            'success' => true,
            'unread_count' => $count,
        ]);
    }

    /**
     * Send appointment reminder
     * POST /api/v1/saas/push-notifications/appointment-reminder
     */
    public function sendAppointmentReminder(Request $request)
    {
        $validated = $request->validate([
            'appointment_id' => 'required|integer|exists:appointments,id',
        ]);

        $result = $this->pushNotificationService->sendAppointmentReminder(
            $validated['appointment_id']
        );

        return response()->json([
            'success' => true,
            'message' => 'Appointment reminder sent',
            'result' => $result,
        ], 201);
    }

    /**
     * Send eligibility update
     * POST /api/v1/saas/push-notifications/eligibility-update
     */
    public function sendEligibilityUpdate(Request $request)
    {
        $validated = $request->validate([
            'donor_id' => 'required|integer|exists:donors,id',
            'is_eligible' => 'required|boolean',
        ]);

        $result = $this->pushNotificationService->sendEligibilityUpdate(
            $validated['donor_id'],
            $validated['is_eligible']
        );

        return response()->json([
            'success' => true,
            'message' => 'Eligibility update sent',
            'result' => $result,
        ], 201);
    }

    /**
     * Get notification statistics
     * GET /api/v1/saas/push-notifications/stats
     */
    public function stats(Request $request)
    {
        $stats = $this->pushNotificationService->getNotificationStats(
            auth()->user()->tenant_id
        );

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Delete a notification
     * DELETE /api/v1/saas/push-notifications/{id}
     */
    public function destroy(PushNotification $pushNotification)
    {
        $this->authorize('delete', $pushNotification);

        $pushNotification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }
}
