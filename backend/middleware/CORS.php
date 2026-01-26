<?php
/**
 * CORS Middleware
 * Handles Cross-Origin Resource Sharing
 * 
 * @author Moueene Development Team
 * @version 1.0.0
 */

class CORS {
    
    /**
     * Handle CORS headers
     */
    public static function handle() {
        $config = require CONFIG_PATH . '/app.php';
        $corsConfig = $config['cors'];
        
        if (!$corsConfig['enabled']) {
            return;
        }
        
        // Get origin
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        // Check if origin is allowed
        if (in_array($origin, $corsConfig['allowed_origins']) || in_array('*', $corsConfig['allowed_origins'])) {
            header('Access-Control-Allow-Origin: ' . $origin);
        }
        
        // Set other CORS headers
        header('Access-Control-Allow-Methods: ' . implode(', ', $corsConfig['allowed_methods']));
        header('Access-Control-Allow-Headers: ' . implode(', ', $corsConfig['allowed_headers']));
        header('Access-Control-Expose-Headers: ' . implode(', ', $corsConfig['expose_headers']));
        header('Access-Control-Max-Age: ' . $corsConfig['max_age']);
        
        if ($corsConfig['credentials']) {
            header('Access-Control-Allow-Credentials: true');
        }
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}
