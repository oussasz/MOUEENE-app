<?php
/**
 * API Entry Point
 * Moueene - Home Services Platform
 * 
 * @author Moueene Development Team
 * @version 1.0.0
 */

// DEBUG MODE - Remove after testing
if (isset($_GET['debug']) && $_GET['debug'] === 'index') {
    header('Content-Type: application/json');
    echo json_encode([
        'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? '',
        'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? '',
        'PHP_SELF' => $_SERVER['PHP_SELF'] ?? '',
        'PATH_INFO' => $_SERVER['PATH_INFO'] ?? 'not set',
        'QUERY_STRING' => $_SERVER['QUERY_STRING'] ?? '',
        'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? '',
    ], JSON_PRETTY_PRINT);
    exit;
}

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

// New router layout (with legacy fallback for non-migrated resources)
require_once __DIR__ . '/core/Request.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/MetaController.php';

function legacyDispatch(Request $req): void {
    // Build $parts array for legacy endpoint files
    $parts = $req->segments;
    $resource = $req->resource();

    try {
        switch ($resource) {
            case 'auth':
                require_once API_PATH . '/auth/index.php';
                return;
            case 'users':
                require_once API_PATH . '/users/index.php';
                return;
            case 'providers':
                require_once API_PATH . '/providers/index.php';
                return;
            case 'services':
                require_once API_PATH . '/services/index.php';
                return;
            case 'categories':
                require_once API_PATH . '/categories/index.php';
                return;
            case 'bookings':
                require_once API_PATH . '/bookings/index.php';
                return;
            case 'payments':
                require_once API_PATH . '/payments/index.php';
                return;
            case 'reviews':
                require_once API_PATH . '/reviews/index.php';
                return;
            case 'messages':
                require_once API_PATH . '/messages/index.php';
                return;
            case 'notifications':
                require_once API_PATH . '/notifications/index.php';
                return;
            case 'content':
                require_once API_PATH . '/content/index.php';
                return;
            case 'uploads':
                require_once API_PATH . '/uploads/index.php';
                return;
            case 'admin':
                require_once API_PATH . '/admin/index.php';
                return;
            default:
                if ($resource === '') {
                    Response::success([
                        'name' => 'Moueene API',
                        'version' => 'v1',
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
                            'content' => '/api/v1/content',
                            'meta' => '/api/v1/meta/version'
                        ]
                    ], 'API is running');
                }

                Response::notFound('Endpoint');
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        $debug = (require CONFIG_PATH . '/app.php')['app']['debug'];
        $message = $debug ? $e->getMessage() : 'An error occurred';
        Response::serverError($message);
    }
}

$req = Request::fromGlobals();
$router = new Router();
$auth = new AuthController();
$meta = new MetaController();

// Meta
$router->add('GET', '/v1/meta/version', function(Request $r) use ($meta) { $meta->version($r); });

// Auth (new controller)
$router->add('POST', '/v1/auth/register', function(Request $r) use ($auth) { $auth->registerLegacy($r); });
$router->add('POST', '/v1/auth/login', function(Request $r) use ($auth) { $auth->loginLegacy($r); });
$router->add('POST', '/v1/auth/register-user', function(Request $r) use ($auth) { $auth->registerUser($r); });
$router->add('POST', '/v1/auth/register-provider', function(Request $r) use ($auth) { $auth->registerProvider($r); });
$router->add('POST', '/v1/auth/login-user', function(Request $r) use ($auth) { $auth->loginUser($r); });
$router->add('POST', '/v1/auth/login-provider', function(Request $r) use ($auth) { $auth->loginProvider($r); });

try {
    $router->dispatch($req);
} catch (Exception $e) {
    // Fallback to legacy routing for non-migrated resources/routes
    legacyDispatch($req);
}
