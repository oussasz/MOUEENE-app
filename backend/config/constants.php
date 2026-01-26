<?php
/**
 * Application Constants
 * Moueene - Home Services Platform
 * 
 * @author Moueene Development Team
 * @version 1.0.0
 */

// Prevent direct access
defined('APP_ACCESS') or die('Direct access not permitted');

// Base Paths
define('BASE_PATH', dirname(__DIR__));
define('CONFIG_PATH', BASE_PATH . '/config');
define('API_PATH', BASE_PATH . '/api');
define('CLASSES_PATH', BASE_PATH . '/classes');
define('MIDDLEWARE_PATH', BASE_PATH . '/middleware');
define('UTILS_PATH', BASE_PATH . '/utils');
define('UPLOADS_PATH', dirname(BASE_PATH) . '/uploads');
define('LOGS_PATH', dirname(BASE_PATH) . '/logs');

// User Roles
define('ROLE_USER', 'user');
define('ROLE_PROVIDER', 'provider');
define('ROLE_ADMIN', 'admin');
define('ROLE_SUPER_ADMIN', 'super_admin');

// Account Status
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');
define('STATUS_SUSPENDED', 'suspended');
define('STATUS_DEACTIVATED', 'deactivated');
define('STATUS_DELETED', 'deleted');
define('STATUS_PENDING', 'pending');

// Verification Status
define('VERIFICATION_PENDING', 'pending');
define('VERIFICATION_VERIFIED', 'verified');
define('VERIFICATION_REJECTED', 'rejected');

// Booking Status
define('BOOKING_PENDING', 'pending');
define('BOOKING_CONFIRMED', 'confirmed');
define('BOOKING_IN_PROGRESS', 'in_progress');
define('BOOKING_COMPLETED', 'completed');
define('BOOKING_CANCELLED', 'cancelled');
define('BOOKING_REJECTED', 'rejected');
define('BOOKING_REFUNDED', 'refunded');

// Payment Status
define('PAYMENT_PENDING', 'pending');
define('PAYMENT_PROCESSING', 'processing');
define('PAYMENT_COMPLETED', 'completed');
define('PAYMENT_FAILED', 'failed');
define('PAYMENT_REFUNDED', 'refunded');
define('PAYMENT_CANCELLED', 'cancelled');

// Payment Methods
define('PAYMENT_CREDIT_CARD', 'credit_card');
define('PAYMENT_DEBIT_CARD', 'debit_card');
define('PAYMENT_PAYPAL', 'paypal');
define('PAYMENT_STRIPE', 'stripe');
define('PAYMENT_CASH', 'cash');
define('PAYMENT_BANK_TRANSFER', 'bank_transfer');

// Notification Types
define('NOTIFICATION_BOOKING_CONFIRMED', 'booking_confirmed');
define('NOTIFICATION_BOOKING_CANCELLED', 'booking_cancelled');
define('NOTIFICATION_BOOKING_REMINDER', 'booking_reminder');
define('NOTIFICATION_BOOKING_COMPLETED', 'booking_completed');
define('NOTIFICATION_PAYMENT_RECEIVED', 'payment_received');
define('NOTIFICATION_REVIEW_RECEIVED', 'review_received');
define('NOTIFICATION_MESSAGE_RECEIVED', 'message_received');
define('NOTIFICATION_ACCOUNT_VERIFICATION', 'account_verification');
define('NOTIFICATION_PROMOTION', 'promotion');
define('NOTIFICATION_SYSTEM_ALERT', 'system_alert');

// Document Types
define('DOC_ID_CARD', 'id_card');
define('DOC_PASSPORT', 'passport');
define('DOC_DRIVER_LICENSE', 'driver_license');
define('DOC_BUSINESS_LICENSE', 'business_license');
define('DOC_CERTIFICATE', 'certificate');
define('DOC_INSURANCE', 'insurance');
define('DOC_OTHER', 'other');

// Price Types
define('PRICE_FIXED', 'fixed');
define('PRICE_HOURLY', 'hourly');
define('PRICE_PER_ITEM', 'per_item');
define('PRICE_CUSTOM', 'custom');

// Provider Availability
define('AVAILABILITY_AVAILABLE', 'available');
define('AVAILABILITY_BUSY', 'busy');
define('AVAILABILITY_OFFLINE', 'offline');

// HTTP Response Codes
define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_NO_CONTENT', 204);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_METHOD_NOT_ALLOWED', 405);
define('HTTP_CONFLICT', 409);
define('HTTP_UNPROCESSABLE_ENTITY', 422);
define('HTTP_TOO_MANY_REQUESTS', 429);
define('HTTP_INTERNAL_SERVER_ERROR', 500);
define('HTTP_SERVICE_UNAVAILABLE', 503);

// Languages
define('LANG_ENGLISH', 'en');
define('LANG_FRENCH', 'fr');
define('LANG_ARABIC', 'ar');

// Gender
define('GENDER_MALE', 'male');
define('GENDER_FEMALE', 'female');
define('GENDER_OTHER', 'other');
define('GENDER_PREFER_NOT_TO_SAY', 'prefer_not_to_say');

// Days of Week
define('DAY_MONDAY', 'monday');
define('DAY_TUESDAY', 'tuesday');
define('DAY_WEDNESDAY', 'wednesday');
define('DAY_THURSDAY', 'thursday');
define('DAY_FRIDAY', 'friday');
define('DAY_SATURDAY', 'saturday');
define('DAY_SUNDAY', 'sunday');

// Error Messages
define('ERROR_INVALID_CREDENTIALS', 'Invalid email or password');
define('ERROR_EMAIL_EXISTS', 'Email already exists');
define('ERROR_PHONE_EXISTS', 'Phone number already exists');
define('ERROR_ACCOUNT_SUSPENDED', 'Your account has been suspended');
define('ERROR_ACCOUNT_NOT_VERIFIED', 'Please verify your account');
define('ERROR_INVALID_TOKEN', 'Invalid or expired token');
define('ERROR_PERMISSION_DENIED', 'You do not have permission to perform this action');
define('ERROR_RESOURCE_NOT_FOUND', 'Resource not found');
define('ERROR_VALIDATION_FAILED', 'Validation failed');
define('ERROR_SERVER_ERROR', 'An error occurred. Please try again later');

// Success Messages
define('SUCCESS_REGISTRATION', 'Registration successful');
define('SUCCESS_LOGIN', 'Login successful');
define('SUCCESS_LOGOUT', 'Logout successful');
define('SUCCESS_UPDATE', 'Update successful');
define('SUCCESS_DELETE', 'Delete successful');
define('SUCCESS_EMAIL_SENT', 'Email sent successfully');
define('SUCCESS_PASSWORD_RESET', 'Password reset successful');
define('SUCCESS_BOOKING_CREATED', 'Booking created successfully');
define('SUCCESS_BOOKING_CANCELLED', 'Booking cancelled successfully');
define('SUCCESS_PAYMENT_COMPLETED', 'Payment completed successfully');
define('SUCCESS_REVIEW_SUBMITTED', 'Review submitted successfully');

// Regular Expressions
define('REGEX_EMAIL', '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/');
define('REGEX_PHONE', '/^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/');
define('REGEX_PASSWORD', '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/');
define('REGEX_ZIP_CODE', '/^[0-9]{5}$/');

// Date Formats
define('DATE_FORMAT', 'Y-m-d');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'F j, Y');
define('DISPLAY_DATETIME_FORMAT', 'F j, Y g:i A');

// Image Dimensions
define('PROFILE_IMAGE_WIDTH', 400);
define('PROFILE_IMAGE_HEIGHT', 400);
define('SERVICE_IMAGE_WIDTH', 800);
define('SERVICE_IMAGE_HEIGHT', 600);
define('CATEGORY_IMAGE_WIDTH', 600);
define('CATEGORY_IMAGE_HEIGHT', 400);

// Limits
define('MAX_PROFILE_IMAGE_SIZE', 5242880); // 5MB
define('MAX_DOCUMENT_SIZE', 10485760); // 10MB
define('MAX_REVIEW_LENGTH', 1000);
define('MAX_MESSAGE_LENGTH', 5000);
define('MIN_SERVICE_PRICE', 10);
define('MAX_SERVICE_PRICE', 100000);
