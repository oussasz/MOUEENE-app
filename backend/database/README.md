# Moueene Database Setup Guide

## Overview

This directory contains the database schema and configuration files for the Moueene Home Services Platform.

## Files Description

### 1. `schema.sql`

Complete database schema with all tables, indexes, triggers, and initial data.

**Tables Created:**

- **languages** - Supported platform languages (English, French, Arabic)
- **users** - Customer accounts
- **providers** - Service provider accounts
- **provider_documents** - Verification documents
- **service_categories** - Service category definitions
- **service_category_translations** - Localized category content
- **services** - Available services
- **service_translations** - Localized service content
- **provider_services** - Provider-service relationships
- **service_availability** - Provider availability schedule
- **bookings** - Service bookings
- **booking_history** - Booking status change logs
- **payments** - Payment transactions
- **reviews** - Customer reviews and ratings
- **favorites** - User favorite providers
- **messages** - In-app messaging
- **notifications** - System notifications
- **content_pages** - CMS pages (About, Terms, FAQ, etc.)
- **content_page_translations** - Localized CMS content
- **admin_users** - Admin panel users

### 2. `test_connection.php`

Web-based utility to test database connectivity and display configuration.

## Initial Setup Instructions

### Step 1: Create the Database

```bash
# Option 1: Using command line
mysql -u root -p < schema.sql

# Option 2: Using phpMyAdmin
# - Import the schema.sql file through phpMyAdmin interface
```

### Step 2: Configure Database Connection

Edit `../config/database.php` and update the following credentials:

```php
private static $host = 'localhost';      // Your database host
private static $db_name = 'moueene_db';  // Database name
private static $username = 'root';        // Your MySQL username
private static $password = '';            // Your MySQL password
```

### Step 3: Test Connection

1. Place the project on your web server (Apache/Nginx)
2. Navigate to: `http://localhost/your-project/backend/database/test_connection.php`
3. Verify the connection is successful

## Database Schema Highlights

### Key Features:

- **Multilingual Platform**: Arabic (RTL), French, and English via translation tables
- **Comprehensive User Management**: Separate tables for customers and providers
- **Service Marketplace**: Categories, services, and provider offerings
- **Booking System**: Complete booking workflow with status tracking
- **Payment Integration**: Ready for multiple payment gateways
- **Review System**: Multi-criteria ratings and provider responses
- **Messaging System**: Built-in communication between users and providers
- **Notification System**: Multi-channel notification support
- **Document Verification**: Provider verification document management
- **CMS Pages**: Localized static pages like Terms, Privacy, FAQ, About

### Security Features:

- Password hashing support
- Email verification tokens
- Phone verification
- Two-factor authentication ready
- Account status management
- Background check verification

### Performance Optimizations:

- Strategic indexes on frequently queried columns
- Efficient foreign key relationships
- Optimized views for common queries
- Triggers for automatic calculations

## Default Admin Account

**Username:** admin  
**Email:** admin@moueene.com  
**Password:** Admin@123456

⚠️ **IMPORTANT**: Change this password immediately in production!

## Sample Data Included

The schema includes initial data:

- 3 Supported languages (English, French, Arabic)
- 10 Service categories (Cleaning, Gardening, Childcare, etc.)
- 16 Sample services across categories
- English translations for categories and services
- 7 CMS pages with English placeholders
- 1 Default admin user

## Triggers Implemented

1. **update_provider_rating_after_insert** - Updates provider rating on new review
2. **update_provider_rating_after_update** - Updates rating on review modification
3. **update_provider_rating_after_delete** - Recalculates rating on review deletion
4. **update_service_rating_after_review** - Updates service average rating
5. **generate_booking_reference** - Auto-generates unique booking references
6. **log_booking_status_change** - Logs all booking status changes

## Views Available

1. **active_bookings_view** - Complete details of active bookings
2. **provider_stats_view** - Comprehensive provider statistics

## Maintenance

### Backup Database

```bash
mysqldump -u root -p moueene_db > backup_$(date +%Y%m%d).sql
```

### Restore Database

```bash
mysql -u root -p moueene_db < backup_20260126.sql
```

### Check Database Size

```sql
SELECT
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.tables
WHERE table_schema = 'moueene_db'
GROUP BY table_schema;
```

## Common Queries

### Get Total Users

```sql
SELECT COUNT(*) as total_users FROM users WHERE account_status = 'active';
```

### Get Total Providers

```sql
SELECT COUNT(*) as total_providers FROM providers WHERE verification_status = 'verified';
```

### Get Booking Statistics

```sql
SELECT
    booking_status,
    COUNT(*) as count,
    SUM(final_price) as total_revenue
FROM bookings
GROUP BY booking_status;
```

### Top Rated Providers

```sql
SELECT
    provider_id,
    CONCAT(first_name, ' ', last_name) as provider_name,
    average_rating,
    total_reviews
FROM providers
WHERE verification_status = 'verified'
ORDER BY average_rating DESC, total_reviews DESC
LIMIT 10;
```

## Troubleshooting

### Connection Issues

1. Verify MySQL service is running
2. Check credentials in database.php
3. Ensure database exists
4. Verify user permissions

### Import Errors

1. Check MySQL version compatibility (MySQL 5.7+ or MariaDB 10.2+)
2. Ensure sufficient privileges
3. Check for syntax errors in schema.sql

### Performance Issues

1. Check indexes are created properly
2. Monitor slow query log
3. Optimize table periodically: `OPTIMIZE TABLE table_name`
4. Analyze table statistics: `ANALYZE TABLE table_name`

## Support

For database-related issues, please check:

- MySQL error logs: `/var/log/mysql/error.log`
- PHP error logs: Check your php.ini configuration
- Application logs: Will be in `../logs/` directory

## Version History

- **v1.0.0** (2026-01-26) - Initial database schema release
  - Complete table structure
  - Initial data seeding
  - Triggers and views implementation
  - Security features integration
