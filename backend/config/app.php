<?php
/**
 * Application Configuration File
 * Moueene - Home Services Platform
 * 
 * @author Moueene Development Team
 * @version 1.0.0
 */

// Prevent direct access
defined('APP_ACCESS') or define('APP_ACCESS', true);

return [
    // Application Settings
    'app' => [
        'name' => 'Moueene',
        'version' => '1.0.0',
        'env' => 'development', // development, staging, production
        'debug' => true,
        'url' => 'http://localhost',
        'timezone' => 'Africa/Casablanca',
    ],

    // Localization Settings
    'localization' => [
        'default_language' => 'en',
        'supported_languages' => ['en', 'fr', 'ar'],
        'fallback_language' => 'en',
        'auto_detect' => true,
    ],

    // Security Settings
    'security' => [
        'jwt_secret' => 'your-secret-key-change-in-production',
        'jwt_algorithm' => 'HS256',
        'jwt_expiration' => 3600, // 1 hour in seconds
        'password_min_length' => 8,
        'password_require_uppercase' => true,
        'password_require_lowercase' => true,
        'password_require_numbers' => true,
        'password_require_special' => true,
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes in seconds
        'token_expiration' => 3600, // Verification token expiration
    ],

    // Session Settings
    'session' => [
        'lifetime' => 7200, // 2 hours in seconds
        'secure' => false, // Set to true in production with HTTPS
        'httponly' => true,
        'samesite' => 'Lax',
    ],

    // CORS Settings
    'cors' => [
        'enabled' => true,
        'allowed_origins' => ['http://localhost', 'http://localhost:3000'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'expose_headers' => ['X-Total-Count', 'X-Page-Count'],
        'max_age' => 3600,
        'credentials' => true,
    ],

    // Email Settings
    'email' => [
        'from_email' => 'noreply@moueene.com',
        'from_name' => 'Moueene Platform',
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_username' => '',
        'smtp_password' => '',
        'smtp_encryption' => 'tls',
    ],

    // SMS Settings
    'sms' => [
        'provider' => 'twilio', // twilio, nexmo, etc.
        'from_number' => '',
        'account_sid' => '',
        'auth_token' => '',
    ],

    // File Upload Settings
    'upload' => [
        'max_file_size' => 5242880, // 5MB in bytes
        'allowed_image_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'allowed_document_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
        'upload_path' => __DIR__ . '/../../uploads/',
        'profile_pictures_path' => 'profiles/',
        'documents_path' => 'documents/',
        'services_path' => 'services/',
    ],

    // Pagination Settings
    'pagination' => [
        'default_limit' => 20,
        'max_limit' => 100,
    ],

    // Payment Settings
    'payment' => [
        'currency' => 'MAD',
        'platform_fee_percentage' => 15.00,
        'stripe_public_key' => '',
        'stripe_secret_key' => '',
        'paypal_client_id' => '',
        'paypal_secret' => '',
        'paypal_mode' => 'sandbox', // sandbox or live
    ],

    // Booking Settings
    'booking' => [
        'cancellation_window' => 24, // Hours before service
        'auto_confirm' => false,
        'reminder_hours_before' => 24,
        'booking_reference_prefix' => 'BK',
    ],

    // Rating Settings
    'rating' => [
        'min_rating' => 1,
        'max_rating' => 5,
        'allow_provider_response' => true,
    ],

    // Notification Settings
    'notifications' => [
        'email_enabled' => true,
        'sms_enabled' => false,
        'push_enabled' => false,
    ],

    // Cache Settings
    'cache' => [
        'enabled' => false,
        'driver' => 'file', // file, redis, memcached
        'ttl' => 3600, // 1 hour in seconds
    ],

    // Logging Settings
    'logging' => [
        'enabled' => true,
        'level' => 'debug', // debug, info, warning, error
        'path' => __DIR__ . '/../../logs/',
        'max_files' => 30,
    ],

    // API Settings
    'api' => [
        'version' => 'v1',
        'prefix' => '/api/v1',
        'rate_limit' => 100, // Requests per minute
        'rate_limit_period' => 60, // In seconds
    ],

    // Search Settings
    'search' => [
        'min_query_length' => 3,
        'max_results' => 50,
        'default_radius' => 10, // kilometers
        'max_radius' => 50,
    ],
];
