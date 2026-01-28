<?php
/**
 * API Entry Point
 * Moueene - Home Services Platform
 * 
 * @author Moueene Development Team
 * @version 1.0.0
 */

// Define access constant
define('APP_ACCESS', true);

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Global error handler for uncaught exceptions
set_exception_handler(function($e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred',
        'debug' => defined('DEBUG') && DEBUG ? $e->getMessage() : null
    ]);
    error_log("Uncaught exception: " . $e->getMessage());
    exit;
});

// Load configuration
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';

// Load utilities
require_once UTILS_PATH . '/Response.php';
require_once UTILS_PATH . '/Validator.php';
require_once UTILS_PATH . '/Auth.php';

// Load middleware
require_once MIDDLEWARE_PATH . '/CORS.php';

// Handle CORS
CORS::handle();

// Set timezone
date_default_timezone_set('Africa/Casablanca');

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, '/');

// Remove 'backend/api/' prefix if present
$path = preg_replace('#^backend/api/#', '', $path);

// Get route parts
$parts = explode('/', $path);

// Check if first part is a version (v1, v2, etc.) or a resource
// If it's not a version, insert 'v1' as default
if (!empty($parts[0]) && !preg_match('/^v\d+$/', $parts[0])) {
    // First part is not a version, treat it as resource
    array_unshift($parts, 'v1');
}

$version = $parts[0] ?? 'v1';
$resource = $parts[1] ?? '';

// Route to appropriate endpoint
try {
    switch ($resource) {
        case 'auth':
            require_once API_PATH . '/auth/index.php';
            break;
            
        case 'users':
            require_once API_PATH . '/users/index.php';
            break;
            
        case 'providers':
            require_once API_PATH . '/providers/index.php';
            break;
            
        case 'services':
            require_once API_PATH . '/services/index.php';
            break;
            
        case 'categories':
            require_once API_PATH . '/categories/index.php';
            break;
            
        case 'bookings':
            require_once API_PATH . '/bookings/index.php';
            break;
            
        case 'payments':
            require_once API_PATH . '/payments/index.php';
            break;
            
        case 'reviews':
            require_once API_PATH . '/reviews/index.php';
            break;
            
        case 'messages':
            require_once API_PATH . '/messages/index.php';
            break;
            
        case 'notifications':
            require_once API_PATH . '/notifications/index.php';
            break;
            
        case 'content':
            require_once API_PATH . '/content/index.php';
            break;
            
        case 'admin':
            require_once API_PATH . '/admin/index.php';
            break;
            
        default:
            if (empty($resource)) {
                Response::success([
                    'name' => 'Moueene API',
                    'version' => 'v1.0.0',
                    'status' => 'active',
                    'endpoints' => [
                        'auth' => '/api/v1/auth',
                        'users' => '/api/v1/users',
                        'providers' => '/api/v1/providers',
                        'services' => '/api/v1/services',
                        'categories' => '/api/v1/categories',
                        'bookings' => '/api/v1/bookings',
                        'payments' => '/api/v1/payments',
                        'reviews' => '/api/v1/reviews',
                        'messages' => '/api/v1/messages',
                        'notifications' => '/api/v1/notifications',
                        'content' => '/api/v1/content'
                    ]
                ], 'API is running');
            } else {
                Response::notFound('Endpoint');
            }
    }
} catch (Exception $e) {
    // Log error
    error_log($e->getMessage());
    
    // Send error response
    $debug = (require CONFIG_PATH . '/app.php')['app']['debug'];
    $message = $debug ? $e->getMessage() : 'An error occurred';
    
    Response::serverError($message);
}
