<?php

namespace App\Http\Controllers;

use App\Notifications\OrderConfirmationNotification;
use App\Notifications\PasswordResetSmsNotification;
use App\Notifications\SystemAlertNotification;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\WelcomeEmailNotification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

/**
 * Notification demo controller demonstrating various notification features
 * 
 * Cost considerations:
 * - Email: FREE (using Mailpit for testing)
 * - Slack: FREE (webhook URL, can be mocked for testing)
 * - SMS: Can cost money (services like Twilio/Vonage), but we mock for testing
 */
class NotificationDemoController extends Controller
{
    /**
     * Send welcome email notification
     * Demonstrates: Basic email notification
     */
    public function sendWelcomeEmail(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->notify(new WelcomeEmailNotification($user->name));

        return response()->json([
            'message' => 'Welcome email notification sent',
            'user' => $user->name,
            'channel' => 'email',
        ]);
    }

    /**
     * Send task assigned notification
     * Demonstrates: Multi-channel notification (Email + Slack)
     */
    public function sendTaskAssigned(Request $request): JsonResponse
    {
        $request->validate([
            'task_title' => 'required|string',
            'assigned_by' => 'required|string',
        ]);

        $user = $request->user();

        $user->notify(new TaskAssignedNotification(
            $request->input('task_title'),
            $request->input('assigned_by')
        ));

        return response()->json([
            'message' => 'Task assigned notification sent',
            'task_title' => $request->input('task_title'),
            'channels' => ['mail', 'slack'],
        ]);
    }

    /**
     * Send password reset SMS
     * Demonstrates: SMS notification
     */
    public function sendPasswordResetSms(Request $request): JsonResponse
    {
        $request->validate([
            'reset_code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        // In production, this would send a real SMS
        // For testing, we mock this
        $user->notify(new PasswordResetSmsNotification(
            $request->input('reset_code')
        ));

        return response()->json([
            'message' => 'Password reset SMS notification sent (mocked for testing)',
            'reset_code' => $request->input('reset_code'),
            'channel' => 'sms',
            'note' => 'SMS notifications are mocked in tests to avoid costs',
        ]);
    }

    /**
     * Send order confirmation
     * Demonstrates: Rich email with multiple channels
     */
    public function sendOrderConfirmation(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*' => 'required|string',
        ]);

        $user = $request->user();

        $user->notify(new OrderConfirmationNotification(
            $request->input('order_id'),
            $request->input('amount'),
            $request->input('items')
        ));

        return response()->json([
            'message' => 'Order confirmation notification sent',
            'order_id' => $request->input('order_id'),
            'channels' => ['mail', 'slack'],
        ]);
    }

    /**
     * Send system alert
     * Demonstrates: Alert notifications to multiple channels
     */
    public function sendSystemAlert(Request $request): JsonResponse
    {
        $request->validate([
            'alert_type' => 'required|in:info,warning,error',
            'message' => 'required|string',
            'details' => 'nullable|string',
        ]);

        $user = $request->user();

        $user->notify(new SystemAlertNotification(
            $request->input('alert_type'),
            $request->input('message'),
            $request->input('details')
        ));

        return response()->json([
            'message' => 'System alert notification sent',
            'alert_type' => $request->input('alert_type'),
            'channels' => ['mail', 'slack'],
        ]);
    }

    /**
     * Send notification to multiple users
     * Demonstrates: Broadcasting notifications
     */
    public function sendToMultipleUsers(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        // Get all users (in production, you'd filter by criteria)
        $users = User::take(5)->get();

        if ($users->isEmpty()) {
            return response()->json([
                'message' => 'No users found',
            ], 404);
        }

        Notification::send($users, new SystemAlertNotification(
            'info',
            $request->input('message')
        ));

        return response()->json([
            'message' => 'Notification sent to multiple users',
            'recipients_count' => $users->count(),
            'channels' => ['mail', 'slack'],
        ]);
    }

    /**
     * Get notification statistics
     * Demonstrates: Notification configuration info
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'message' => 'Notification statistics',
            'mail_driver' => config('mail.default'),
            'mail_host' => config('mail.mailers.smtp.host'),
            'slack_configured' => !empty(config('services.slack.notifications.bot_user_oauth_token')),
            'mailpit_url' => 'http://localhost:8025',
            'note' => 'Check Mailpit dashboard to view sent emails',
        ]);
    }
}

