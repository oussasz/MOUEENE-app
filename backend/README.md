# Moueene Backend Configuration

## Directory Structure

```
backend/
├── api/
│   ├── index.php              # API entry point & router
│   ├── auth/
│   │   └── index.php          # Authentication endpoints
│   ├── users/
│   │   └── index.php          # User management endpoints
│   ├── providers/
│   │   └── index.php          # Provider endpoints
│   ├── services/
│   │   └── index.php          # Services endpoints
│   ├── categories/
│   │   └── index.php          # Categories endpoints
│   ├── bookings/
│   │   └── index.php          # Booking endpoints
│   ├── payments/
│   │   └── index.php          # Payment endpoints
│   ├── reviews/
│   │   └── index.php          # Review endpoints
│   ├── messages/
│   │   └── index.php          # Messaging endpoints
│   ├── notifications/
│   │   └── index.php          # Notification endpoints
│   └── content/
│       └── index.php          # CMS content endpoints
├── classes/
│   └── README.md              # Model classes (planned)
├── config/
│   ├── database.php           # Database connection
│   ├── app.php                # Application configuration
│   ├── constants.php          # Global constants
│   └── .env.example           # Environment variables template
├── database/
│   ├── schema.sql             # Complete database schema
│   ├── test_connection.php    # Database connection test
│   └── README.md              # Database documentation
├── middleware/
│   └── CORS.php               # CORS handling middleware
├── utils/
│   ├── Response.php           # API response helper
│   ├── Validator.php          # Data validation helper
│   └── Auth.php               # JWT authentication helper
├── .htaccess                  # Apache URL rewriting
├── API_DOCUMENTATION.md       # Complete API documentation
└── README.md                  # This file
```

## Quick Start Guide

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Web server (Apache/Nginx with mod_rewrite enabled)
- PDO MySQL extension enabled
- JSON extension enabled

### Installation Steps

1. **Import Database Schema**
   ```bash
   mysql -u root -p < database/schema.sql
   ```

2. **Configure Environment**
   
   Copy `.env.example` to `.env` and update:
   ```bash
   cp config/.env.example config/.env
   ```
   
   Edit `config/.env` with your credentials:
   ```env
   DB_HOST=localhost
   DB_NAME=moueene_db
   DB_USER=root
   DB_PASS=your_password
   JWT_SECRET=your-secret-key
   ```

3. **Update Database Credentials**
   
   EdMaking API Requests

**Register a new user:**
```bash
curl -X POST http://localhost/backend/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "Test123!",
    "first_name": "John",
    "last_name": "Doe",
    "user_type": "user"
  }'
```

**Login:**
```bash
curl -X POST http://localhost/backend/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "Test123!",
    "user_type": "user"
  }'
```

**Get Services:**
```bash
curl http://localhost/backend/api/v1/services?lang=en&page=1&limit=10
```

**Get Categories (in French):**
```bash
curl http://localhost/backend/api/v1/categories?lang=fr
```

**Get Authenticated User:**
```bash
curl http://localhost/backend/api/v1/auth/me \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

#### Database Operations (Internal)
   Navigate to:
   ```
   http://localhost/your-project/backend/api/v1
   ```
   
   You should see the API welcome response with available endpoints.

### Usage Examples

#### Connect to Database
```php
<?php
require_once 'backend/config/database.php';

// Get database connection
$db = Database::getConnection();

// Test connection
$result = Database::testConnection();
if ($result['status'] === 'success') {
    echo "Connected successfully!";
}
?>
```

#### Execute Queries
```php
<?php
require_once 'backend/config/database.php';

// Using helper functions
$users = fetchAll("SELECT * FROM users WHERE account_status = ?", ['active']);

// Get single record
$user = fetchOne("SELECT * FROM users WHERE email = ?", ['user@example.com']);

// Execute insert/update
executeQuery("INSERT INTO users (email, password_hash, first_name, last_name) VALUES (?, ?, ?, ?)", 
    ['john@example.com', password_hash('password123', PASSWORD_DEFAULT), 'John', 'Doe']
);

// Get last inserted ID
$lastId = getLastInsertId();
?>
```

## Database Features

### Security
- ✅ Password hashing ready
- ✅ Email verification system
- ✅ Phone verification support
- ✅ Two-factor authentication fields
- ✅ Account status management
- ✅ Document verification for providers

### Functionality
- ✅ User and provider management
- ✅ Service catalog system
- ✅API Endpoints

The backend provides the following REST API endpoints:

### ✅ Implemented Endpoints

**Authentication:**
- `POST /api/v1/auth/register` - User/Provider registration
- `POST /api/v1/auth/login` - User/Provider login
- `POST /api/v1/auth/logout` - Logout
- `GET /api/v1/auth/me` - Get current user
- `POST /api/v1/auth/verify-email` - Email verification
- `Features Implemented

✅ **RESTful API Architecture**
- Clean URL routing
- Consistent JSON responses
- Proper HTTP status codes
- Error handling

✅ **Authentication & Security**
- JWT token-based authentication
- Password hashing with bcrypt
- Email verification system
- Password reset functionality
- CORS support

✅ **Multilingual Support**
- English, French, Arabic
- RTL support for Arabic
- Translation tables for dynamic content
- Language detection from query params

✅ **Data Validation**
- Comprehensive validator class
- Required fields, email, phone validation
- Min/max length and value checks
- Pattern matching support

✅ **Database Layer**
- PDO with prepared statements
- Connection pooling
- Helper functions for common operations
- Transaction support ready

✅ **API Features**
- Pagination support
- Search and filtering
- Multilingual content delivery
- Structured error responses**Implement Remaining Endpoints** - Users, Providers, Bookings, Payments
6. **Email Service** - Configure SMTP for notifications
7. **Payment Gateway** - Integrate Stripe/PayPal
8. **File Upload** - Implement profile pictures and documents
9. **Admin Panel** - Build backend management interface
10. **Testing** - Write unit and integration tests
- ✅ Optimized indexes
- ✅ Foreign key relationships
- ✅ Automated triggers
- ✅ Database views
- ✅ Connection pooling ready

## Default Credentials

### Admin Panel
- **Username:** admin
- **Email:** admin@moueene.com
- **Password:** Admin@123456

⚠️ **Change immediately in production!**

## Next Steps

After database setup:

1. **Create API Endpoints** - Build REST API for frontend integration
2. **Implement Authentication** - Add JWT or session-based auth
3. **Add Business Logic** - Create service classes for bookings, payments
4. **Setup Email Service** - Configure SMTP for notifications
5. **Implement Payment Gateway** - Integrate Stripe/PayPal
6. **Add File Upload** - Handle profile pictures and documents
7. **Create Admin Panel** - Build backend management interface

## Recommended File Structure

```
backend/
├── config/
│   ├── database.php
│   ├── app.php             # Application configuration
│   └── email.php           # Email configuration
├── database/
│   ├── schema.sql
│   ├── test_connection.php
│   └── README.md
├── api/
│   ├── auth/               # Authentication endpoints
│   ├── users/              # User management
│   ├── providers/          # Provider management
│   ├── services/           # Service endpoints
│   ├── bookings/           # Booking management
│   └── payments/           # Payment processing
├── classes/
│   ├── User.php
│   ├── Provider.php
│   ├── Service.php
│   ├── Booking.php
│   └── Payment.php
├── middleware/
│   ├── Auth.php
│   ├── CORS.php
│   └── RateLimiter.php
└── utils/
    ├── Validator.php
    ├── Mailer.php
    └── FileUpload.php
```

## Environment Variables

Create a `.env` file for sensitive configuration:

```env
# Database
DB_HOST=localhost
DB_NAME=moueene_db
DB_USER=root
DB_PASS=your_password

# Application
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost

# Localization
DEFAULT_LANGUAGE=en
SUPPORTED_LANGUAGES=en,fr,ar

# Email
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=your-email@gmail.com
MAIL_PASS=your-password

# Payment Gateway
STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret
```

## Troubleshooting

### Database Connection Failed
- Verify MySQL is running: `systemctl status mysql`
- Check credentials in database.php
- Ensure database exists: `SHOW DATABASES;`

### Permission Denied
- Grant proper privileges:
  ```sql
  GRANT ALL PRIVILEGES ON moueene_db.* TO 'your_user'@'localhost';
  FLUSH PRIVILEGES;
  ```

### Table Not Found
- Ensure schema.sql was imported correctly
- Check database name in connection config

### Missing Translations
- Ensure entries exist in `languages`
- Add entries to `service_category_translations`, `service_translations`, and `content_page_translations`

## Support

For issues or questions:
- Check database/README.md for detailed documentation
- Review error logs in `/var/log/mysql/`
- Enable PHP error reporting for debugging

## License

Copyright © 2026 Moueene Platform. All rights reserved.
