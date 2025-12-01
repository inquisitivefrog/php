# Laravel Sanctum - Explanation & Manual Testing Guide

## What is Sanctum?

**Laravel Sanctum** is Laravel's API authentication package. It provides two authentication methods:

1. **Token-based authentication** (what we're using) - For APIs, mobile apps, third-party services
2. **Session-based authentication** - For Single Page Applications (SPAs) running on the same domain

### How Token-Based Authentication Works

1. **User logs in** → Server creates a "personal access token"
2. **Token is returned** → Client receives the token (e.g., `1|abc123...`)
3. **Client stores token** → Usually in localStorage, memory, or secure storage
4. **Subsequent requests** → Client sends token in `Authorization: Bearer {token}` header
5. **Server validates token** → Sanctum middleware checks if token exists and is valid
6. **User is authenticated** → `$request->user()` returns the authenticated user
7. **Token can be revoked** → User logs out, token is deleted from database

### Key Components

- **`personal_access_tokens` table** - Stores all tokens (created by Sanctum migration)
- **`HasApiTokens` trait** - Added to User model, provides `createToken()`, `tokens()`, etc.
- **`auth:sanctum` middleware** - Protects routes, validates tokens
- **Token format** - `{id}|{hashed_token}` (e.g., `1|czoq3ZVcyRov3jR4gkTXynQuKi4A3byMxwVkwb6af1b5598b`)

---

## Manual Testing Guide

### Step 1: Register a New User

```bash
curl -X POST http://localhost:8080/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Expected Response:** `204 No Content` (user created, no response body)

---

### Step 2: Login and Get Token

```bash
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

**Expected Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": null,
    "created_at": "2025-12-01T20:00:00.000000Z",
    "updated_at": "2025-12-01T20:00:00.000000Z"
  },
  "token": "1|czoq3ZVcyRov3jR4gkTXynQuKi4A3byMxwVkwb6af1b5598b"
}
```

**Save the token!** You'll need it for the next steps.

---

### Step 3: Access Protected Endpoint (WITH Token)

```bash
# Replace YOUR_TOKEN_HERE with the token from Step 2
curl -X GET http://localhost:8080/api/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "email_verified_at": null,
  "created_at": "2025-12-01T20:00:00.000000Z",
  "updated_at": "2025-12-01T20:00:00.000000Z"
}
```

**This proves Sanctum is working!** The token authenticated you.

---

### Step 4: Try Accessing Protected Endpoint (WITHOUT Token)

```bash
curl -X GET http://localhost:8080/api/user \
  -H "Accept: application/json"
```

**Expected Response:** `401 Unauthorized`

This proves the endpoint is protected.

---

### Step 5: Try Accessing Protected Endpoint (WITH Invalid Token)

```bash
curl -X GET http://localhost:8080/api/user \
  -H "Authorization: Bearer invalid-token-12345" \
  -H "Accept: application/json"
```

**Expected Response:** `401 Unauthorized`

This proves Sanctum validates tokens properly.

---

### Step 6: Logout (Revoke Token)

```bash
# Use the same token from Step 2
curl -X POST http://localhost:8080/api/logout \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

**Expected Response:** `204 No Content`

---

### Step 7: Verify Token is Revoked

```bash
# Try to use the same token again (should fail)
curl -X GET http://localhost:8080/api/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

**Expected Response:** `401 Unauthorized`

The token was deleted from the database, so it no longer works.

---

## Testing with Multiple Tokens

A user can have multiple tokens (e.g., one for mobile app, one for web app):

```bash
# Login again to get a new token
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"password123"}'

# Save this as TOKEN_1

# Login again (same user, different token)
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"password123"}'

# Save this as TOKEN_2

# Both tokens work independently
curl -X GET http://localhost:8080/api/user \
  -H "Authorization: Bearer TOKEN_1"

curl -X GET http://localhost:8080/api/user \
  -H "Authorization: Bearer TOKEN_2"

# Logout with TOKEN_1 (only revokes that specific token)
curl -X POST http://localhost:8080/api/logout \
  -H "Authorization: Bearer TOKEN_1"

# TOKEN_1 no longer works, but TOKEN_2 still works
curl -X GET http://localhost:8080/api/user \
  -H "Authorization: Bearer TOKEN_1"
# → 401 Unauthorized

curl -X GET http://localhost:8080/api/user \
  -H "Authorization: Bearer TOKEN_2"
# → 200 OK (still works!)
```

---

## Database Verification

You can check tokens in the database:

```bash
# Inside workspace container
docker compose exec workspace bash
php artisan tinker

# In tinker:
DB::table('personal_access_tokens')->get();
# Shows all tokens with: id, tokenable_id (user_id), name, token (hashed), abilities, last_used_at, etc.

# Check tokens for a specific user
$user = App\Models\User::find(1);
$user->tokens; // Shows all tokens for this user
```

---

## Key Sanctum Concepts

1. **Token Storage**: Tokens are stored in `personal_access_tokens` table
2. **Token Hashing**: The actual token value is hashed before storage (security)
3. **Token Format**: `{id}|{plain_text_token}` - the ID is used to look up the hashed token
4. **Token Scoping**: Tokens can have "abilities" (permissions) - we're not using this yet
5. **Token Expiration**: Can be set in `config/sanctum.php` (currently `null` = never expires)

---

## Security Notes

- **Never commit tokens to git** - They're like passwords
- **Use HTTPS in production** - Tokens sent over HTTP can be intercepted
- **Store tokens securely** - In memory (SPA) or secure storage (mobile)
- **Revoke tokens on logout** - Prevents unauthorized access if token is stolen
- **Consider token expiration** - Set `expiration` in `config/sanctum.php` for production

