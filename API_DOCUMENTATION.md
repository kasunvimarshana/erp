# API Documentation

## Base URL
```
http://localhost:8000/api/v1
```

## Authentication

All protected endpoints require a Bearer token in the Authorization header:

```
Authorization: Bearer {your_access_token}
```

## Common Headers

### Required for All Requests
```
Content-Type: application/json
Accept: application/json
```

### Optional Headers
```
X-Tenant-ID: {tenant_uuid}          # Tenant identification
X-Locale: en                         # Language preference
X-Timezone: America/New_York         # Timezone preference
X-Request-ID: {unique_id}            # Request tracking (auto-generated if not provided)
```

## Rate Limiting

| Endpoint Type | Rate Limit |
|--------------|------------|
| Authentication endpoints | 5 requests/minute |
| General API | 60 requests/minute |
| Authenticated users | 120 requests/minute |
| Admin operations | 30 requests/minute |

Rate limit information is returned in response headers:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1612345678
```

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... }
}
```

### Validation Error Response
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": [
      "Error message 1",
      "Error message 2"
    ]
  }
}
```

### Paginated Response
```json
{
  "success": true,
  "message": "Success",
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150,
    "from": 1,
    "to": 15
  }
}
```

## HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created |
| 204 | No Content |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 405 | Method Not Allowed |
| 422 | Validation Error |
| 429 | Too Many Requests |
| 500 | Internal Server Error |

---

## Authentication Endpoints

### Login

**POST** `/auth/login`

**Rate Limit:** 5 requests/minute

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "SecurePassword123!",
  "tenant_id": "uuid-optional",
  "remember": false
}
```

**Validation Rules:**
- `email`: required, valid email, max 255 chars
- `password`: required, string, min 8 chars
- `tenant_id`: optional, valid UUID
- `remember`: optional, boolean

**Success Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "tenant_id": "uuid",
      "roles": ["admin"],
      "permissions": ["users.view", "users.create"],
      "email_verified_at": "2024-01-01T00:00:00Z",
      "created_at": "2024-01-01T00:00:00Z"
    },
    "token": "1|abc123...",
    "token_type": "Bearer",
    "expires_in": 3600
  }
}
```

**Error Response (401):**
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

---

### Register

**POST** `/auth/register`

**Rate Limit:** 5 requests/minute

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "user@example.com",
  "password": "SecurePassword123!",
  "password_confirmation": "SecurePassword123!",
  "tenant_id": "uuid-optional"
}
```

**Validation Rules:**
- `name`: required, string, max 255 chars
- `email`: required, valid email, unique, max 255 chars
- `password`: required, confirmed, min 8 chars, mixed case, numbers, symbols, not compromised
- `tenant_id`: optional, valid UUID, must exist in tenants table

**Success Response (201):**
```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "user": { ... },
    "token": "1|abc123...",
    "token_type": "Bearer",
    "expires_in": 3600
  }
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password must contain at least one uppercase letter."]
  }
}
```

---

### Logout

**POST** `/auth/logout`

**Authentication:** Required

**Success Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

### Get Authenticated User

**GET** `/auth/user`

**Authentication:** Required

**Success Response (200):**
```json
{
  "success": true,
  "message": "User retrieved successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "tenant_id": "uuid",
      "roles": ["admin", "manager"],
      "permissions": ["users.view", "users.create", "users.edit"],
      "email_verified_at": "2024-01-01T00:00:00Z",
      "created_at": "2024-01-01T00:00:00Z"
    }
  }
}
```

---

### Refresh Token

**POST** `/auth/refresh`

**Authentication:** Required

**Success Response (200):**
```json
{
  "success": true,
  "message": "Token refreshed successfully",
  "data": {
    "token": "2|xyz789...",
    "token_type": "Bearer",
    "expires_in": 3600
  }
}
```

---

## IAM - User Management

### List Users

**GET** `/iam/users`

**Authentication:** Required

**Permission:** `users.view`

**Query Parameters:**
```
?page=1               # Page number (default: 1)
&per_page=15          # Items per page (default: 15, max: 100)
&search=john          # Search by name or email
&role=admin           # Filter by role
&tenant_id=uuid       # Filter by tenant
&sort_by=created_at   # Sort field
&sort_order=desc      # Sort direction (asc/desc)
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Users retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "tenant_id": "uuid",
      "roles": ["admin"],
      "created_at": "2024-01-01T00:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```

---

### Create User

**POST** `/iam/users`

**Authentication:** Required

**Permission:** `users.create`

**Request Body:**
```json
{
  "name": "Jane Smith",
  "email": "jane@example.com",
  "password": "SecurePassword123!",
  "tenant_id": "uuid",
  "roles": ["user"]
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "User created successfully",
  "data": {
    "user": { ... }
  }
}
```

---

### Get User

**GET** `/iam/users/{id}`

**Authentication:** Required

**Permission:** `users.view`

**Success Response (200):**
```json
{
  "success": true,
  "message": "User retrieved successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "tenant_id": "uuid",
      "roles": ["admin", "manager"],
      "permissions": ["users.view", "users.create"],
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-02T00:00:00Z"
    }
  }
}
```

**Error Response (404):**
```json
{
  "success": false,
  "message": "User not found"
}
```

---

### Update User

**PUT** `/iam/users/{id}`

**Authentication:** Required

**Permission:** `users.edit`

**Request Body:**
```json
{
  "name": "John Updated",
  "email": "john.updated@example.com"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "User updated successfully",
  "data": {
    "user": { ... }
  }
}
```

---

### Delete User

**DELETE** `/iam/users/{id}`

**Authentication:** Required

**Permission:** `users.delete`

**Success Response (200):**
```json
{
  "success": true,
  "message": "User deleted successfully"
}
```

---

### Get User Permissions

**GET** `/iam/users/{id}/permissions`

**Authentication:** Required

**Permission:** `permissions.view`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Permissions retrieved successfully",
  "data": {
    "permissions": [
      "users.view",
      "users.create",
      "users.edit",
      "users.delete"
    ]
  }
}
```

---

### Assign Roles to User

**POST** `/iam/users/{id}/roles`

**Authentication:** Required

**Permission:** `roles.assign`

**Request Body:**
```json
{
  "roles": ["admin", "manager"]
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Roles assigned successfully",
  "data": {
    "user": { ... }
  }
}
```

---

## IAM - Role Management

### List Roles

**GET** `/iam/roles`

**Authentication:** Required

**Permission:** `roles.view`

**Query Parameters:**
```
?page=1
&per_page=15
&search=admin
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Roles retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "admin",
      "display_name": "Administrator",
      "description": "Full system access",
      "permissions_count": 50,
      "users_count": 5,
      "created_at": "2024-01-01T00:00:00Z"
    }
  ],
  "meta": { ... }
}
```

---

### Create Role

**POST** `/iam/roles`

**Authentication:** Required

**Permission:** `roles.create`

**Request Body:**
```json
{
  "name": "supervisor",
  "display_name": "Supervisor",
  "description": "Supervises operations",
  "permissions": ["users.view", "orders.view"]
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Role created successfully",
  "data": {
    "role": { ... }
  }
}
```

---

### Get Role

**GET** `/iam/roles/{id}`

**Authentication:** Required

**Permission:** `roles.view`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Role retrieved successfully",
  "data": {
    "role": {
      "id": 1,
      "name": "admin",
      "display_name": "Administrator",
      "description": "Full system access",
      "permissions": ["users.view", "users.create", ...],
      "created_at": "2024-01-01T00:00:00Z"
    }
  }
}
```

---

### Update Role

**PUT** `/iam/roles/{id}`

**Authentication:** Required

**Permission:** `roles.edit`

**Request Body:**
```json
{
  "display_name": "Super Administrator",
  "description": "Updated description"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Role updated successfully",
  "data": {
    "role": { ... }
  }
}
```

---

### Delete Role

**DELETE** `/iam/roles/{id}`

**Authentication:** Required

**Permission:** `roles.delete`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Role deleted successfully"
}
```

---

### Get Role Users

**GET** `/iam/roles/{id}/users`

**Authentication:** Required

**Permission:** `roles.view`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Users retrieved successfully",
  "data": {
    "users": [
      {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
      }
    ]
  }
}
```

---

### Assign Permissions to Role

**POST** `/iam/roles/{id}/permissions`

**Authentication:** Required

**Permission:** `permissions.assign`

**Request Body:**
```json
{
  "permissions": ["users.view", "users.create", "users.edit"]
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Permissions assigned successfully",
  "data": {
    "role": { ... }
  }
}
```

---

## Error Codes

### Authentication Errors
- `AUTH001`: Invalid credentials
- `AUTH002`: Account disabled
- `AUTH003`: Email not verified
- `AUTH004`: Token expired
- `AUTH005`: Invalid token

### Authorization Errors
- `AUTHZ001`: Insufficient permissions
- `AUTHZ002`: Resource access denied
- `AUTHZ003`: Role not found

### Validation Errors
- `VAL001`: Required field missing
- `VAL002`: Invalid format
- `VAL003`: Value out of range
- `VAL004`: Unique constraint violation

### Business Logic Errors
- `BUS001`: Operation not allowed
- `BUS002`: Resource in use
- `BUS003`: Duplicate entry

---

## Examples

### cURL Examples

**Login:**
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@demo.local",
    "password": "password"
  }'
```

**Get Users (Authenticated):**
```bash
curl -X GET http://localhost:8000/api/v1/iam/users \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "X-Tenant-ID: YOUR_TENANT_UUID"
```

### JavaScript (Axios) Examples

**Login:**
```javascript
const axios = require('axios');

const response = await axios.post('http://localhost:8000/api/v1/auth/login', {
  email: 'admin@demo.local',
  password: 'password'
});

const token = response.data.data.token;
```

**Get Users:**
```javascript
const response = await axios.get('http://localhost:8000/api/v1/iam/users', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'X-Tenant-ID': tenantId
  },
  params: {
    page: 1,
    per_page: 15
  }
});
```

---

## Multi-Currency Support

When working with monetary values, always include currency information:

```json
{
  "amount": 100.50,
  "currency": "USD"
}
```

The system will automatically handle conversions based on current exchange rates when needed.

---

## Multi-Language Support

Set your preferred language using the `X-Locale` header:

```
X-Locale: es
```

Supported locales: `en`, `es`, `fr`, `de`, `it`, `pt`, `zh`, `ja`, `ko`, `ar`

---

## Multi-Timezone Support

Set your timezone using the `X-Timezone` header:

```
X-Timezone: America/New_York
```

All datetime values in responses will be converted to your specified timezone.

---

## Changelog

### v1.0.0 (2026-02-05)
- Initial API release
- Authentication endpoints
- IAM user and role management
- Multi-currency support
- Multi-language support
- Multi-timezone support
- Audit trail integration
- Rate limiting
- CORS support
