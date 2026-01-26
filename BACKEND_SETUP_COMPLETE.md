# Moueene Backend Setup - Complete Summary

## 🎉 What Has Been Created

### 1. Database Schema (Enhanced)
**Location:** `backend/database/schema.sql`

**Tables Created:** 21 tables
- ✅ `languages` - Platform languages (EN, FR, AR)
- ✅ `users` - Customer accounts
- ✅ `providers` - Service provider accounts
- ✅ `provider_documents` - Verification documents
- ✅ `service_categories` - Service categories
- ✅ `service_category_translations` - Category translations
- ✅ `services` - Available services
- ✅ `service_translations` - Service translations
- ✅ `provider_services` - Provider-service relationships
- ✅ `service_availability` - Provider schedules
- ✅ `bookings` - Service bookings
- ✅ `booking_history` - Status change logs
- ✅ `payments` - Payment transactions
- ✅ `reviews` - Customer reviews
- ✅ `favorites` - User favorites
- ✅ `messages` - In-app messaging
- ✅ `notifications` - System notifications
- ✅ `content_pages` - CMS pages
- ✅ `content_page_translations` - CMS translations
- ✅ `admin_users` - Admin accounts

**Features:**
- 6 triggers for automated calculations
- 2 views for optimized queries
- Full multilingual support (EN/FR/AR with RTL)
- Complete indexing strategy
- Foreign key relationships
- Sample data included

### 2. Configuration Files

#### `backend/config/database.php`
- Singleton PDO connection
- Helper functions (fetchAll, fetchOne, executeQuery)
- Error handling and logging
- Connection testing utilities

#### `backend/config/app.php`
- Application settings
- Localization config
- Security settings (JWT, passwords)
- CORS configuration
- Email/SMS settings
- File upload settings
- Payment gateway config
- API settings

#### `backend/config/constants.php`
- Global constants
- Roles, statuses, types
- HTTP codes
- Error messages
- Regex patterns
- Date formats

#### `backend/config/.env.example`
- Environment variables template
- Database credentials
- API keys
- SMTP settings
- Payment gateway keys

### 3. Utility Classes

#### `backend/utils/Response.php`
- Consistent JSON responses
- Success/error responses
- Paginated responses
- HTTP status helpers
- Validation error formatting

#### `backend/utils/Validator.php`
- Comprehensive validation
- Required, email, phone validation
- Min/max length and value
- Pattern matching
- Date validation
- Custom error messages

#### `backend/utils/Auth.php`
- JWT token generation
- Token verification
- Bearer token extraction
- Password hashing/verification
- Random token generation
- User authentication helpers

### 4. Middleware

#### `backend/middleware/CORS.php`
- Cross-origin resource sharing
- Configurable allowed origins
- Preflight request handling
- Credentials support

### 5. API Endpoints

#### Main Router: `backend/api/index.php`
- RESTful routing
- Error handling
- Endpoint registration

#### Implemented Endpoints:

**Authentication** (`/api/v1/auth/*`)
- ✅ POST `/register` - User/provider registration
- ✅ POST `/login` - Authentication
- ✅ POST `/logout` - Logout
- ✅ GET `/me` - Current user
- ✅ POST `/verify-email` - Email verification
- ✅ POST `/forgot-password` - Password reset request
- ✅ POST `/reset-password` - Password reset

**Services** (`/api/v1/services/*`)
- ✅ GET `/` - List services (pagination, filters, i18n)
- ✅ GET `/{id}` - Get single service
- ✅ GET `/popular` - Popular services

**Categories** (`/api/v1/categories`)
- ✅ GET `/` - List categories (i18n)

**Content** (`/api/v1/content/{slug}`)
- ✅ GET `/{slug}` - Get CMS page (i18n)

**Placeholder Endpoints:**
- 🔜 `/users` - User management
- 🔜 `/providers` - Provider management
- 🔜 `/bookings` - Booking system
- 🔜 `/payments` - Payment processing
- 🔜 `/reviews` - Review system
- 🔜 `/messages` - Messaging
- 🔜 `/notifications` - Notifications

### 6. Documentation

- ✅ `backend/API_DOCUMENTATION.md` - Complete API reference
- ✅ `backend/README.md` - Setup and usage guide
- ✅ `backend/database/README.md` - Database documentation
- ✅ `backend/classes/README.md` - Class structure guide

### 7. Apache Configuration

- ✅ `backend/.htaccess` - URL rewriting, security headers

---

## 📁 Complete Directory Structure

```
Mouin/
├── backend/
│   ├── api/
│   │   ├── index.php                 # API router
│   │   ├── auth/
│   │   │   └── index.php             # Authentication endpoints ✅
│   │   ├── users/
│   │   │   └── index.php             # User endpoints 🔜
│   │   ├── providers/
│   │   │   └── index.php             # Provider endpoints 🔜
│   │   ├── services/
│   │   │   └── index.php             # Services endpoints ✅
│   │   ├── categories/
│   │   │   └── index.php             # Categories endpoints ✅
│   │   ├── bookings/
│   │   │   └── index.php             # Booking endpoints 🔜
│   │   ├── payments/
│   │   │   └── index.php             # Payment endpoints 🔜
│   │   ├── reviews/
│   │   │   └── index.php             # Review endpoints 🔜
│   │   ├── messages/
│   │   │   └── index.php             # Message endpoints 🔜
│   │   ├── notifications/
│   │   │   └── index.php             # Notification endpoints 🔜
│   │   └── content/
│   │       └── index.php             # CMS endpoints ✅
│   ├── classes/
│   │   └── README.md                 # Model classes guide
│   ├── config/
│   │   ├── database.php              # Database connection ✅
│   │   ├── app.php                   # App configuration ✅
│   │   ├── constants.php             # Global constants ✅
│   │   └── .env.example              # Environment template ✅
│   ├── database/
│   │   ├── schema.sql                # Complete schema ✅
│   │   ├── test_connection.php       # Connection test ✅
│   │   └── README.md                 # Database docs ✅
│   ├── middleware/
│   │   └── CORS.php                  # CORS middleware ✅
│   ├── utils/
│   │   ├── Response.php              # Response helper ✅
│   │   ├── Validator.php             # Validation helper ✅
│   │   └── Auth.php                  # Auth helper ✅
│   ├── .htaccess                     # Apache config ✅
│   ├── API_DOCUMENTATION.md          # API docs ✅
│   └── README.md                     # Setup guide ✅
├── logs/
│   └── .gitkeep                      # Logs directory
├── uploads/
│   └── .gitkeep                      # Uploads directory
└── [frontend files...]

✅ = Implemented
🔜 = Placeholder for future implementation
```

---

## 🚀 Quick Start

### Step 1: Import Database
```bash
mysql -u root -p < backend/database/schema.sql
```

### Step 2: Configure Environment
```bash
# Copy environment template
cp backend/config/.env.example backend/config/.env

# Edit with your settings
nano backend/config/.env
```

### Step 3: Update Database Credentials
Edit `backend/config/database.php`:
```php
private static $host = 'localhost';
private static $db_name = 'moueene_db';
private static $username = 'root';
private static $password = 'your_password';
```

### Step 4: Test Database Connection
Navigate to:
```
http://localhost/Mouin/backend/database/test_connection.php
```

### Step 5: Test API
Navigate to:
```
http://localhost/Mouin/backend/api/v1
```

---

## 🧪 Testing the API

### Using cURL

**Get Services:**
```bash
curl http://localhost/Mouin/backend/api/v1/services?lang=en
```

**Get Categories in French:**
```bash
curl http://localhost/Mouin/backend/api/v1/categories?lang=fr
```

**Register User:**
```bash
curl -X POST http://localhost/Mouin/backend/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "Test123!",
    "first_name": "John",
    "last_name": "Doe",
    "phone": "+212600000000",
    "user_type": "user"
  }'
```

**Login:**
```bash
curl -X POST http://localhost/Mouin/backend/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "Test123!",
    "user_type": "user"
  }'
```

**Get Current User (with token):**
```bash
curl http://localhost/Mouin/backend/api/v1/auth/me \
  -H "Authorization: Bearer YOUR_JWT_TOKEN_HERE"
```

### Using Postman

1. Import endpoints from `API_DOCUMENTATION.md`
2. Set base URL: `http://localhost/Mouin/backend/api/v1`
3. For auth endpoints, add header:
   - Key: `Authorization`
   - Value: `Bearer YOUR_JWT_TOKEN`

---

## 🔐 Default Credentials

### Admin Panel
- **Email:** admin@moueene.com
- **Password:** Admin@123456
- **Type:** admin

⚠️ **IMPORTANT:** Change immediately in production!

---

## 🌍 Multilingual Support

The platform supports 3 languages:

| Code | Language | Direction | Status |
|------|----------|-----------|--------|
| `en` | English  | LTR       | Default |
| `fr` | Français | LTR       | Active  |
| `ar` | العربية  | RTL       | Active  |

Use `?lang=` parameter in API requests:
```
GET /api/v1/services?lang=fr
GET /api/v1/categories?lang=ar
GET /api/v1/content/about?lang=en
```

---

## ✨ Key Features

### Security
- ✅ JWT authentication
- ✅ Password hashing (bcrypt)
- ✅ Email verification
- ✅ Password reset
- ✅ CORS protection
- ✅ SQL injection prevention (PDO prepared statements)

### API Features
- ✅ RESTful architecture
- ✅ Pagination
- ✅ Search & filtering
- ✅ Multilingual content
- ✅ Consistent error handling
- ✅ Rate limiting ready

### Database
- ✅ 21 normalized tables
- ✅ Translation tables
- ✅ Automated triggers
- ✅ Optimized indexes
- ✅ Foreign key constraints

### Development
- ✅ Clean code structure
- ✅ Comprehensive documentation
- ✅ Environment configuration
- ✅ Helper utilities
- ✅ Validation system

---

## 📝 Next Development Steps

### Immediate Priorities
1. **Test all implemented endpoints**
2. **Configure SMTP for emails**
3. **Implement remaining endpoints:**
   - User profile management
   - Provider listings & profiles
   - Booking system
   - Payment processing
   - Reviews & ratings
   - Messaging system

### Short Term
4. **File upload system** (profiles, documents)
5. **Email notifications**
6. **SMS verification** (optional)
7. **Search optimization**

### Medium Term
8. **Payment gateway integration** (Stripe/PayPal)
9. **Admin dashboard backend**
10. **Reporting & analytics**
11. **Real-time notifications**

### Long Term
12. **Unit & integration tests**
13. **Performance optimization**
14. **Caching layer** (Redis)
15. **API versioning**
16. **Rate limiting**

---

## 🛠️ Technologies Used

- **Language:** PHP 7.4+
- **Database:** MySQL 5.7+ / MariaDB 10.2+
- **Architecture:** RESTful API
- **Authentication:** JWT
- **Database Access:** PDO
- **Security:** bcrypt, prepared statements

---

## 📚 Documentation Files

| File | Description |
|------|-------------|
| [API_DOCUMENTATION.md](backend/API_DOCUMENTATION.md) | Complete API reference with examples |
| [backend/README.md](backend/README.md) | Backend setup and usage guide |
| [database/README.md](backend/database/README.md) | Database schema documentation |

---

## ⚙️ Configuration Files

| File | Purpose |
|------|---------|
| `config/database.php` | Database connection settings |
| `config/app.php` | Application configuration |
| `config/constants.php` | Global constants |
| `config/.env.example` | Environment variables template |

---

## 🐛 Troubleshooting

### Database Connection Failed
- Verify MySQL is running: `systemctl status mysql`
- Check credentials in `config/database.php`
- Ensure database exists: `mysql -u root -p -e "SHOW DATABASES;"`

### API Returns 404
- Check `.htaccess` file exists in backend/
- Verify mod_rewrite is enabled: `a2enmod rewrite`
- Restart Apache: `systemctl restart apache2`

### CORS Errors
- Update allowed origins in `config/app.php`
- Check CORS middleware is loaded

### JWT Token Invalid
- Verify secret key is set in `config/app.php`
- Check token hasn't expired (default: 1 hour)

---

## 📞 Support

For issues or questions:
1. Check documentation files
2. Review error logs in `logs/` directory
3. Enable debug mode in `config/app.php`
4. Check PHP error logs

---

## 📄 License

Copyright © 2026 Moueene Platform. All rights reserved.

---

**Status:** Backend framework is complete and ready for development! ✅

All core infrastructure is in place. You can now:
- Test the API endpoints
- Start implementing remaining features
- Connect your frontend
- Add payment integrations
- Build the admin panel
