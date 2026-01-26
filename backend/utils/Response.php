<?php
/**
 * Response Helper Class
 * Handles API responses with consistent formatting
 * 
 * @author Moueene Development Team
 * @version 1.0.0
 */

class Response {
    
    /**
     * Send JSON success response
     * 
     * @param mixed $data Response data
     * @param string $message Success message
     * @param int $code HTTP status code
     * @param array $meta Additional metadata
     */
    public static function success($data = null, $message = 'Success', $code = 200, $meta = []) {
        http_response_code($code);
        header('Content-Type: application/json');
        
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
        
        if (!empty($meta)) {
            $response['meta'] = $meta;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Send JSON error response
     * 
     * @param string $message Error message
     * @param int $code HTTP status code
     * @param array $errors Validation errors
     */
    public static function error($message = 'An error occurred', $code = 400, $errors = []) {
        http_response_code($code);
        header('Content-Type: application/json');
        
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Send paginated response
     * 
     * @param array $data Response data
     * @param int $total Total records
     * @param int $page Current page
     * @param int $limit Items per page
     * @param string $message Success message
     */
    public static function paginated($data, $total, $page, $limit, $message = 'Success') {
        $totalPages = ceil($total / $limit);
        
        $meta = [
            'total' => (int) $total,
            'page' => (int) $page,
            'limit' => (int) $limit,
            'total_pages' => $totalPages,
            'has_more' => $page < $totalPages
        ];
        
        self::success($data, $message, 200, $meta);
    }
    
    /**
     * Send 404 not found response
     * 
     * @param string $resource Resource name
     */
    public static function notFound($resource = 'Resource') {
        self::error($resource . ' not found', 404);
    }
    
    /**
     * Send 401 unauthorized response
     * 
     * @param string $message Error message
     */
    public static function unauthorized($message = 'Unauthorized access') {
        self::error($message, 401);
    }
    
    /**
     * Send 403 forbidden response
     * 
     * @param string $message Error message
     */
    public static function forbidden($message = 'Access forbidden') {
        self::error($message, 403);
    }
    
    /**
     * Send 422 validation error response
     * 
     * @param array $errors Validation errors
     * @param string $message Error message
     */
    public static function validationError($errors, $message = 'Validation failed') {
        self::error($message, 422, $errors);
    }
    
    /**
     * Send 500 internal server error response
     * 
     * @param string $message Error message
     */
    public static function serverError($message = 'Internal server error') {
        self::error($message, 500);
    }
    
    /**
     * Send 429 rate limit response
     * 
     * @param string $message Error message
     */
    public static function rateLimitExceeded($message = 'Too many requests') {
        self::error($message, 429);
    }
}
