# Database Setup Guide

## Quick Setup

### 1. Configure Database Credentials

Edit the file `/backend/config/.env` and set your MySQL password:

```bash
# Open the .env file
nano backend/config/.env

# Update this line with your MySQL root password:
DB_PASSWORD=your_mysql_password_here
```

### 2. Create Database and Import Schema

```bash
# Method 1: If MySQL root has no password
mysql -u root -e "CREATE DATABASE IF NOT EXISTS moueene_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root moueene_db < backend/database/schema.sql

# Method 2: If MySQL root has a password
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS moueene_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p moueene_db < backend/database/schema.sql
```

### 3. Test Database Connection

```bash
php backend/database/test_connection.php
```

## Alternative: Create MySQL User Without Password (Development Only)

If you're having trouble with MySQL authentication:

```bash
# Login to MySQL as root
sudo mysql

# In MySQL console, run:
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';
FLUSH PRIVILEGES;
CREATE DATABASE IF NOT EXISTS moueene_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit
```

Then test:

```bash
mysql -u root moueene_db < backend/database/schema.sql
```

## Troubleshooting

### Error: "Access denied for user 'root'@'localhost'"

**Solution 1** - Set password in .env file:

1. Find your MySQL root password
2. Update `backend/config/.env` with: `DB_PASSWORD=your_password`

**Solution 2** - Use MySQL with sudo:

```bash
sudo mysql -u root -e "CREATE DATABASE moueene_db;"
sudo mysql -u root moueene_db < backend/database/schema.sql
```

**Solution 3** - Create a new MySQL user:

```bash
sudo mysql
```

Then in MySQL:

```sql
CREATE USER 'moueene_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON moueene_db.* TO 'moueene_user'@'localhost';
FLUSH PRIVILEGES;
CREATE DATABASE IF NOT EXISTS moueene_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit
```

Update `.env`:

```
DB_USER=moueene_user
DB_PASSWORD=secure_password
```

### Verify Setup

Once configured, test the registration:

1. Start server: `php -S localhost:8000`
2. Open browser: `http://localhost:8000/pages/register.html`
3. Try creating an account

If you see errors, check:

```bash
# Test database connection
php backend/database/test_connection.php

# Check if database exists
mysql -u root -p -e "SHOW DATABASES LIKE 'moueene_db';"

# Check if tables exist
mysql -u root -p moueene_db -e "SHOW TABLES;"
```
