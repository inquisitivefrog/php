# Laravel Notifications Guide

## Overview

**Notifications are FREE for testing** - We use mocking to avoid costs:
- **Email**: FREE (using Mailpit for testing, Mail::fake() for unit tests)
- **Slack**: FREE (webhook URL, Notification::fake() for testing)
- **SMS**: Can cost money (services like Twilio/Vonage), but we use Notification::fake() for testing

## Installation Status

✅ **Notifications are built into Laravel** - No additional installation required

- Laravel Notifications: Built-in
- Mailpit: Running in Docker (port 8025) for email testing
- Slack: Configured via webhook URL
- SMS: Vonage/Nexmo support available (mocked in tests)

## What Notifications Provide

Laravel Notifications provide a unified API for sending notifications across multiple channels:

### Supported Channels

1. **Email** (`mail`) - Send rich HTML emails
2. **Slack** (`slack`) - Send messages to Slack channels
3. **SMS** (`vonage`) - Send SMS via Vonage (formerly Nexmo)
4. **Database** (`database`) - Store notifications in database
5. **Broadcast** (`broadcast`) - Real-time notifications via WebSockets
6. **Custom Channels** - Create your own notification channels

## Notification Examples Created

### 1. WelcomeEmailNotification
- **Type**: Email
- **Purpose**: Welcome new users
- **Features**: Greeting, action button, salutation

### 2. TaskAssignedNotification
- **Type**: Email + Slack
- **Purpose**: Notify users of task assignments
- **Features**: Multi-channel, rich formatting

### 3. PasswordResetSmsNotification
- **Type**: SMS
- **Purpose**: Send password reset codes
- **Features**: SMS delivery (mocked in tests)

### 4. OrderConfirmationNotification
- **Type**: Email + Slack
- **Purpose**: Confirm orders
- **Features**: Rich email, Slack attachments, multiple items

### 5. SystemAlertNotification
- **Type**: Email + Slack
- **Purpose**: System alerts and errors
- **Features**: Error/warning/info types, multi-channel

## API Endpoints

All notification endpoints require authentication via Sanctum.

### Send Welcome Email
```http
POST /api/notifications/welcome
Authorization: Bearer {token}
```

### Send Task Assigned Notification
```http
POST /api/notifications/task-assigned
Authorization: Bearer {token}
Content-Type: application/json

{
  "task_title": "Complete project",
  "assigned_by": "John Doe"
}
```

### Send Password Reset SMS
```http
POST /api/notifications/password-reset-sms
Authorization: Bearer {token}
Content-Type: application/json

{
  "reset_code": "123456"
}
```

### Send Order Confirmation
```http
POST /api/notifications/order-confirmation
Authorization: Bearer {token}
Content-Type: application/json

{
  "order_id": "ORD-12345",
  "amount": 99.99,
  "items": ["Item 1", "Item 2", "Item 3"]
}
```

### Send System Alert
```http
POST /api/notifications/system-alert
Authorization: Bearer {token}
Content-Type: application/json

{
  "alert_type": "error",
  "message": "System error occurred",
  "details": "Database connection failed"
}
```

### Broadcast to Multiple Users
```http
POST /api/notifications/broadcast
Authorization: Bearer {token}
Content-Type: application/json

{
  "message": "System maintenance scheduled"
}
```

### Get Statistics
```http
GET /api/notifications/stats
Authorization: Bearer {token}
```

## Configuration

### Email Configuration

Located in `config/mail.php`:

```php
'default' => env('MAIL_MAILER', 'log'),
```

For Mailpit (testing):
```env
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

### Slack Configuration

Located in `config/services.php`:

```php
'slack' => [
    'notifications' => [
        'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
        'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
    ],
],
```

### SMS Configuration (Vonage)

For SMS notifications, you would configure:

```env
VONAGE_KEY=your_key
VONAGE_SECRET=your_secret
VONAGE_SMS_FROM=your_number
```

## Usage Examples

### Basic Notification

```php
$user->notify(new WelcomeEmailNotification($user->name));
```

### Multi-Channel Notification

```php
$user->notify(new TaskAssignedNotification('Task', 'Admin'));
// Sends to both email and Slack
```

### Broadcast to Multiple Users

```php
$users = User::where('role', 'admin')->get();
Notification::send($users, new SystemAlertNotification('info', 'Message'));
```

### Queued Notifications

```php
// Notification implements ShouldQueue
$user->notify(new WelcomeEmailNotification($user->name));
// Notification is queued automatically
```

### Anonymous Notifications

```php
Notification::route('mail', 'test@example.com')
    ->notify(new WelcomeEmailNotification('User'));
```

## Testing

### Run All Notification Tests
```bash
docker compose run --rm workspace php artisan test --filter NotificationTest
```

### Test Results
- ✅ 20 tests passing
- ✅ 62 assertions
- ✅ All notification features demonstrated

## Test Coverage

| Feature | Tests | Status |
|---------|-------|--------|
| Welcome Email | 2 | ✅ |
| Task Assigned | 2 | ✅ |
| Password Reset SMS | 2 | ✅ |
| Order Confirmation | 2 | ✅ |
| System Alert | 2 | ✅ |
| Broadcast | 2 | ✅ |
| Queued Notifications | 1 | ✅ |
| Channel Configuration | 1 | ✅ |
| Anonymous Notifiable | 1 | ✅ |
| Custom Data | 1 | ✅ |
| Statistics | 1 | ✅ |
| Validation | 1 | ✅ |
| Multiple Types | 1 | ✅ |
| Alert Types | 1 | ✅ |

## Access Mailpit Dashboard

1. Start your application:
   ```bash
   docker compose up
   ```

2. Access dashboard:
   - URL: `http://localhost:8025`
   - View all sent emails
   - Test email delivery

## Best Practices

1. **Use Queued Notifications** - Implement `ShouldQueue` for better performance
2. **Mock in Tests** - Use `Notification::fake()` to avoid external API calls
3. **Multi-Channel** - Send to multiple channels for important notifications
4. **Customize Channels** - Use `via()` method to conditionally select channels
5. **Rich Content** - Use MailMessage and SlackMessage for rich formatting
6. **Error Handling** - Handle notification failures gracefully
7. **Rate Limiting** - Consider rate limiting for SMS notifications

## Cost Considerations

### Email
- **Mailpit**: FREE (local testing)
- **Production**: Costs depend on service (Mailgun, SendGrid, SES, etc.)

### Slack
- **Webhook**: FREE
- **Bot Token**: FREE (within Slack's limits)

### SMS
- **Vonage/Twilio**: Paid service (per SMS)
- **Testing**: Use `Notification::fake()` to avoid costs

## Resources

- [Laravel Notifications Documentation](https://laravel.com/docs/notifications)
- [Mail Configuration](https://laravel.com/docs/mail)
- [Slack Notifications](https://laravel.com/docs/notifications#slack-notifications)
- [SMS Notifications](https://laravel.com/docs/notifications#sms-notifications)

