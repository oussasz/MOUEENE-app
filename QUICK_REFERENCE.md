# 🚀 Moueene Backend - Quick Reference

## 📍 Base URL
```
http://localhost/Mouin/backend/api/v1
```

## 🔑 Authentication
Add to request headers for protected endpoints:
```
Authorization: Bearer YOUR_JWT_TOKEN
```

## 📡 Available Endpoints

### Auth
```bash
POST   /auth/register        # Register user/provider
POST   /auth/login           # Login
GET    /auth/me              # Get current user (protected)
POST   /auth/logout          # Logout (protected)
POST   /auth/verify-email    # Verify email
POST   /auth/forgot-password # Request password reset
POST   /auth/reset-password  # Reset password
```

### Services
```bash
GET    /services                 # List all services
GET    /services/{id}            # Get service details
GET    /services/popular         # Get popular services

# Query params: ?lang=en&category_id=1&search=clean&page=1&limit=20
```

### Categories
```bash
GET    /categories              # List all categories
# Query params: ?lang=fr
```

### Content Pages
```bash
GET    /content/about           # Get about page
GET    /content/terms           # Get terms page
GET    /content/privacy         # Get privacy page
GET    /content/faq             # Get FAQ page
# Query params: ?lang=ar
```

## 🌍 Languages
- `en` - English (default)
- `fr` - French
- `ar` - Arabic (RTL)

Add `?lang=CODE` to any endpoint.

## 📝 Example Requests

### Register
```bash
curl -X POST http://localhost/Mouin/backend/api/v1/auth/register \
-H "Content-Type: application/json" \
-d '{
  "email": "user@example.com",
  "password": "Pass123!",
  "first_name": "John",
  "last_name": "Doe",
  "phone": "+212600000000",
  "user_type": "user"
}'
```

### Login
```bash
curl -X POST http://localhost/Mouin/backend/api/v1/auth/login \
-H "Content-Type: application/json" \
-d '{
  "email": "user@example.com",
  "password": "Pass123!",
  "user_type": "user"
}'
```

### Get Services (French)
```bash
curl "http://localhost/Mouin/backend/api/v1/services?lang=fr&page=1&limit=10"
```

### Get Current User
```bash
curl http://localhost/Mouin/backend/api/v1/auth/me \
-H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 📦 Response Format

### Success
```json
{
  "success": true,
  "message": "Success",
  "data": {...},
  "meta": {
    "total": 100,
    "page": 1,
    "limit": 20,
    "total_pages": 5
  }
}
```

### Error
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": "Error details"
  }
}
```

## 🗄️ Database

### Connect
```bash
mysql -u root -p moueene_db
```

### Test Connection
```
http://localhost/Mouin/backend/database/test_connection.php
```

### Default Admin
- Email: `admin@moueene.com`
- Password: `Admin@123456`

## 📁 Key Files

| Path | Purpose |
|------|---------|
| `backend/api/index.php` | API router |
| `backend/config/database.php` | DB connection |
| `backend/config/app.php` | App config |
| `backend/database/schema.sql` | DB schema |
| `backend/API_DOCUMENTATION.md` | Full API docs |

## 🔧 Configuration

Edit these files:
1. `backend/config/database.php` - DB credentials
2. `backend/config/app.php` - JWT secret, CORS
3. `backend/config/.env` - Environment variables

## ⚡ Quick Setup

```bash
# 1. Import database
mysql -u root -p < backend/database/schema.sql

# 2. Update config
nano backend/config/database.php

# 3. Test
curl http://localhost/Mouin/backend/api/v1
```

## 📊 HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Server Error |

## 🎯 Common Tasks

### Get paginated data
```
GET /services?page=2&limit=25
```

### Search services
```
GET /services?search=cleaning
```

### Filter by category
```
GET /services?category_id=1
```

### Get content in Arabic
```
GET /content/about?lang=ar
```

## 📞 Need Help?

1. Check `/backend/API_DOCUMENTATION.md`
2. Review `/backend/README.md`
3. Enable debug: `config/app.php` → `'debug' => true`
4. Check logs: `logs/` directory
