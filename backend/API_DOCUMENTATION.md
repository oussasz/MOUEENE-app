# Moueene Platform API Documentation

## Base URL

```
http://localhost/backend/api/v1
```

## Authentication

Most endpoints require authentication using JWT tokens. Include the token in the Authorization header:

```
Authorization: Bearer YOUR_JWT_TOKEN
```

---

## Authentication Endpoints

### Register User/Provider

**POST** `/auth/register`

Register a new user or provider account.

**Request Body:**

```json
{
  "email": "user@example.com",
  "password": "Password123!",
  "first_name": "John",
  "last_name": "Doe",
  "phone": "+212600000000",
  "user_type": "user"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user_id": 1,
    "user_type": "user",
    "email": "user@example.com",
    "first_name": "John",
    "last_name": "Doe"
  }
}
```

---

### Login

**POST** `/auth/login`

Authenticate user and receive JWT token.

**Request Body:**

```json
{
  "email": "user@example.com",
  "password": "Password123!",
  "user_type": "user"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "user_id": 1,
      "email": "user@example.com",
      "first_name": "John",
      "last_name": "Doe",
      "phone": "+212600000000",
      "account_status": "active"
    }
  }
}
```

---

### Get Current User

**GET** `/auth/me`

Get authenticated user's data.

**Headers:**

```
Authorization: Bearer YOUR_JWT_TOKEN
```

**Response:**

```json
{
  "success": true,
  "data": {
    "user_id": 1,
    "email": "user@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "profile_picture": null,
    "account_status": "active"
  }
}
```

---

### Verify Email

**POST** `/auth/verify-email`

Verify user email address.

**Request Body:**

```json
{
  "token": "verification_token_from_email"
}
```

---

### Forgot Password

**POST** `/auth/forgot-password`

Request password reset email.

**Request Body:**

```json
{
  "email": "user@example.com",
  "user_type": "user"
}
```

---

### Reset Password

**POST** `/auth/reset-password`

Reset password using token from email.

**Request Body:**

```json
{
  "token": "reset_token_from_email",
  "password": "NewPassword123!",
  "user_type": "user"
}
```

---

## Services Endpoints

### Get All Services

**GET** `/services`

Retrieve all services with optional filters.

**Query Parameters:**

- `category_id` (optional) - Filter by category
- `search` (optional) - Search in name/description
- `lang` (optional) - Language code (en, fr, ar) - default: en
- `page` (optional) - Page number - default: 1
- `limit` (optional) - Items per page - default: 20

**Example Request:**

```
GET /services?category_id=1&lang=en&page=1&limit=10
```

**Response:**

```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "service_id": 1,
      "category_id": 1,
      "service_name": "House Cleaning",
      "description": "Complete house cleaning service",
      "base_price": 150.0,
      "duration_minutes": 120,
      "is_popular": true,
      "average_rating": 4.5
    }
  ],
  "meta": {
    "total": 100,
    "page": 1,
    "limit": 10,
    "total_pages": 10,
    "has_more": true
  }
}
```

---

### Get Popular Services

**GET** `/services/popular`

Retrieve popular services.

**Query Parameters:**

- `lang` (optional) - Language code - default: en
- `limit` (optional) - Number of results - default: 10

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "service_id": 1,
      "service_name": "House Cleaning",
      "description": "Complete house cleaning service",
      "base_price": 150.0,
      "is_popular": true
    }
  ]
}
```

---

### Get Single Service

**GET** `/services/{service_id}`

Get detailed information about a specific service.

**Query Parameters:**

- `lang` (optional) - Language code - default: en

**Response:**

```json
{
  "success": true,
  "data": {
    "service_id": 1,
    "category_id": 1,
    "category_name": "Cleaning Services",
    "service_name": "House Cleaning",
    "description": "Complete house cleaning service",
    "detailed_description": "Detailed service information...",
    "base_price": 150.0,
    "duration_minutes": 120,
    "is_popular": true,
    "average_rating": 4.5,
    "total_bookings": 245
  }
}
```

---

## Categories Endpoints

### Get All Categories

**GET** `/categories`

Retrieve all service categories.

**Query Parameters:**

- `lang` (optional) - Language code (en, fr, ar) - default: en

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "category_id": 1,
      "category_name": "Cleaning Services",
      "category_slug": "cleaning",
      "description": "Professional home and office cleaning",
      "icon": "cleaning-icon.svg",
      "service_count": 15
    }
  ]
}
```

---

## Content Pages Endpoints

### Get Content Page

**GET** `/content/{page_slug}`

Retrieve CMS page content (About, Terms, Privacy, FAQ, etc.)

**Query Parameters:**

- `lang` (optional) - Language code - default: en

**Example:**

```
GET /content/about?lang=en
```

**Response:**

```json
{
  "success": true,
  "data": {
    "page_id": 1,
    "page_slug": "about",
    "page_title": "About Us",
    "page_content": "<p>Content goes here...</p>",
    "meta_title": "About Moueene",
    "meta_description": "Learn more about Moueene"
  }
}
```

---

## Error Responses

All error responses follow this format:

```json
{
  "success": false,
  "message": "Error message"
}
```

### Common HTTP Status Codes:

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `429` - Too Many Requests
- `500` - Internal Server Error

### Validation Error Response:

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": "Email is required",
    "password": "Password must be at least 8 characters"
  }
}
```

---

## Rate Limiting

API requests are limited to **100 requests per minute** per IP address.

---

## Supported Languages

- `en` - English (default)
- `fr` - French (Français)
- `ar` - Arabic (العربية)

---

## Coming Soon

The following endpoints are under development:

- `/users` - User profile management
- `/providers` - Provider listings and profiles
- `/bookings` - Service bookings
- `/payments` - Payment processing
- `/reviews` - Reviews and ratings
- `/messages` - Messaging system
- `/notifications` - Notifications

---

## Testing the API

### Using cURL:

```bash
# Register
curl -X POST http://localhost/backend/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"Test123!","first_name":"Test","last_name":"User","user_type":"user"}'

# Login
curl -X POST http://localhost/backend/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"Test123!","user_type":"user"}'

# Get Services
curl http://localhost/backend/api/v1/services?lang=en

# Get Categories
curl http://localhost/backend/api/v1/categories?lang=fr
```

### Using Postman:

1. Import the endpoints into Postman
2. Set base URL: `http://localhost/backend/api/v1`
3. For authenticated endpoints, add Authorization header with Bearer token

---

## Support

For issues or questions about the API, please contact the development team.
