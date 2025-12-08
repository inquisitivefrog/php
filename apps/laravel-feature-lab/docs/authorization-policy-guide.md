# Laravel Authorization (Policies) Guide

## Overview

**Authorization is FREE** - Laravel Policies are built into the framework. No additional packages required.

## Installation Status

✅ **Policies are built into Laravel** - No installation required

- Policy created: `CowPolicy`
- Auto-discovered by Laravel (follows naming convention: `CowPolicy` for `Cow` model)
- Integrated into `CowController`

## What Policies Provide

Laravel Policies provide a clean way to organize authorization logic for models:

- **View Authorization** - Who can view models
- **Create Authorization** - Who can create models
- **Update Authorization** - Who can update models
- **Delete Authorization** - Who can delete models
- **Custom Actions** - Define your own authorization methods

## Policy Implementation

### CowPolicy Rules

**File:** `src/app/Policies/CowPolicy.php`

**Authorization Rules:**
1. ✅ **View/List** - All authenticated users can view/list cows
2. ✅ **Create** - All authenticated users can create cows
3. 🔒 **Update** - Only admins can update cows
4. 🔒 **Delete** - Only admins can delete cows

**Admin Detection:**
- Users with email ending in `@admin.example.com`
- Users with email containing `admin@`
- Matches the pattern used in feature flags

### Controller Integration

**File:** `src/app/Http/Controllers/CowController.php`

All controller methods now use authorization:

```php
// Check authorization before action
$this->authorize('viewAny', Cow::class);  // List
$this->authorize('create', Cow::class);   // Create
$this->authorize('view', $cow);           // Show
$this->authorize('update', $cow);        // Update (admin only)
$this->authorize('delete', $cow);        // Delete (admin only)
```

**What happens:**
- If authorized: Action proceeds normally
- If unauthorized: Returns `403 Forbidden` response

## API Endpoints

All endpoints require authentication via Sanctum.

### List Cows
```http
GET /api/cows
Authorization: Bearer {token}
```

**Authorization:** All authenticated users ✅

### Create Cow
```http
POST /api/cows
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Bessie",
  "breed": "Holstein",
  "tag_number": "COW-001"
}
```

**Authorization:** All authenticated users ✅

### View Cow
```http
GET /api/cows/{id}
Authorization: Bearer {token}
```

**Authorization:** All authenticated users ✅

### Update Cow
```http
PUT /api/cows/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Bessie Updated",
  "breed": "Jersey"
}
```

**Authorization:** Admins only 🔒 (returns 403 for non-admins)

### Delete Cow
```http
DELETE /api/cows/{id}
Authorization: Bearer {token}
```

**Authorization:** Admins only 🔒 (returns 403 for non-admins)

## Testing Authorization

### Test as Regular User

1. Create a regular user and get token
2. Try to update/delete a cow
3. Should receive `403 Forbidden`

### Test as Admin

1. Create a user with email `admin@example.com` or `user@admin.example.com`
2. Get token
3. Update/delete should work

## Policy Methods

The `CowPolicy` implements these standard methods:

| Method | Purpose | Current Rule |
|--------|---------|--------------|
| `viewAny()` | List all cows | ✅ All authenticated users |
| `view()` | View single cow | ✅ All authenticated users |
| `create()` | Create new cow | ✅ All authenticated users |
| `update()` | Update cow | 🔒 Admins only |
| `delete()` | Delete cow | 🔒 Admins only |
| `restore()` | Restore soft-deleted | 🔒 Admins only |
| `forceDelete()` | Permanently delete | 🔒 Admins only |

## How Authorization Works

1. **Request comes in** → Route requires `auth:sanctum` middleware
2. **Controller method called** → `$this->authorize('action', $model)`
3. **Laravel finds policy** → Auto-discovers `CowPolicy` for `Cow` model
4. **Policy method called** → `CowPolicy::action($user, $cow)`
5. **Returns boolean** → `true` = proceed, `false` = 403 Forbidden

## Customization

### Add Ownership-Based Authorization

To allow users to only edit their own cows, add a `user_id` column:

```php
// Migration
$table->foreignId('user_id')->nullable()->constrained();

// Policy
public function update(User $user, Cow $cow): bool
{
    return $cow->user_id === $user->id || $this->isAdmin($user);
}
```

### Use Role System

Instead of email-based admin detection, use a role column:

```php
// Migration
$table->string('role')->default('user'); // 'user', 'admin', 'moderator'

// Policy
private function isAdmin(User $user): bool
{
    return $user->role === 'admin';
}
```

## Related Laravel Features

- **Gates** - Global authorization rules (not model-specific)
- **Middleware** - Route-level authorization
- **Form Requests** - Request-level authorization
- **Blade Directives** - `@can`, `@cannot` for views

## Summary

✅ **Policy created and integrated**
✅ **Authorization working for all CRUD operations**
✅ **Role-based authorization demonstrated (admin vs regular users)**
✅ **Auto-discovered by Laravel (no manual registration needed)**

The `CowPolicy` demonstrates how to implement authorization in Laravel, showing both permissive (view/create) and restrictive (update/delete) authorization patterns.



