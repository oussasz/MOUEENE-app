<?php
/**
 * Authentication Helper Class
 * Handles JWT token generation and validation
 * 
 * @author Moueene Development Team
 * @version 1.0.0
 */

class Auth {
    
    private static $secretKey;
    private static $algorithm = 'HS256';
    
    /**
     * Initialize Auth with secret key
     */
    public static function init() {
        $config = require CONFIG_PATH . '/app.php';
        self::$secretKey = $config['security']['jwt_secret'];
    }
    
    /**
     * Generate JWT token
     * 
     * @param int $userId User ID
     * @param string $userType User type (user, provider, admin)
     * @param array $additionalData Additional data to include
     * @return string JWT token
     */
    public static function generateToken($userId, $userType, $additionalData = []) {
        self::init();
        
        $config = require CONFIG_PATH . '/app.php';
        $expiration = time() + $config['security']['jwt_expiration'];
        
        $payload = array_merge([
            'iss' => $config['app']['url'],
            'iat' => time(),
            'exp' => $expiration,
            'user_id' => $userId,
            'user_type' => $userType
        ], $additionalData);
        
        return self::encode($payload);
    }
    
    /**
     * Verify JWT token
     * 
     * @param string $token JWT token
     * @return array|false Token payload or false if invalid
     */
    public static function verifyToken($token) {
        self::init();
        
        try {
            $payload = self::decode($token);
            
            // Check expiration
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return false;
            }
            
            return $payload;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get authorization token from request header
     * 
     * @return string|null
     */
    public static function getBearerToken() {
        $headers = self::getAuthorizationHeader();
        
        if (!empty($headers)) {
            if (preg_match('/Bearer\s+(.*)$/i', $headers, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
    
    /**
     * Get authorization header
     * 
     * @return string|null
     */
    private static function getAuthorizationHeader() {
        $headers = null;
        
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } else if (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)), 
                array_values($requestHeaders)
            );
            
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        
        return $headers;
    }
    
    /**
     * Get current authenticated user from token
     * 
     * @return array|null User data or null
     */
    public static function user() {
        $token = self::getBearerToken();
        
        if ($token) {
            $payload = self::verifyToken($token);
            if ($payload) {
                return $payload;
            }
        }
        
        return null;
    }
    
    /**
     * Check if user is authenticated
     * 
     * @return bool
     */
    public static function check() {
        return self::user() !== null;
    }
    
    /**
     * Require authentication
     * Sends 401 response if not authenticated
     */
    public static function requireAuth() {
        if (!self::check()) {
            Response::unauthorized('Authentication required');
        }
    }
    
    /**
     * Hash password
     * 
     * @param string $password Plain password
     * @return string Hashed password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Verify password
     * 
     * @param string $password Plain password
     * @param string $hash Hashed password
     * @return bool
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Generate random token
     * 
     * @param int $length Token length
     * @return string
     */
    public static function generateRandomToken($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Encode payload to JWT
     * 
     * @param array $payload
     * @return string
     */
    private static function encode($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => self::$algorithm]);
        $payload = json_encode($payload);
        
        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secretKey, true);
        $base64UrlSignature = self::base64UrlEncode($signature);
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    
    /**
     * Decode JWT to payload
     * 
     * @param string $jwt
     * @return array
     */
    private static function decode($jwt) {
        $tokenParts = explode('.', $jwt);
        
        if (count($tokenParts) !== 3) {
            throw new Exception('Invalid token format');
        }
        
        $header = base64_decode($tokenParts[0]);
        $payload = base64_decode($tokenParts[1]);
        $signatureProvided = $tokenParts[2];
        
        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secretKey, true);
        $base64UrlSignature = self::base64UrlEncode($signature);
        
        if ($base64UrlSignature !== $signatureProvided) {
            throw new Exception('Invalid signature');
        }
        
        return json_decode($payload, true);
    }
    
    /**
     * Base64 URL encode
     * 
     * @param string $text
     * @return string
     */
    private static function base64UrlEncode($text) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
    }
    
    /**
     * Start session if not already started
     */
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Store user in session
     * 
     * @param array $user User data
     * @param string $userType User type (user, provider, admin)
     * @param bool $remember Remember me flag
     */
    public static function setSession($user, $userType, $remember = false) {
        self::startSession();
        
        $_SESSION['user_id'] = $user[($userType === 'provider' ? 'provider_id' : ($userType === 'admin' ? 'admin_id' : 'user_id'))];
        $_SESSION['user_type'] = $userType;
        $_SESSION['user_data'] = $user;
        $_SESSION['logged_in'] = true;
        
        // Set remember me cookie
        if ($remember) {
            $token = self::generateRandomToken();
            setcookie('remember_token', $token, time() + (86400 * 30), '/', '', false, true); // 30 days
            // In production, store this token in database for validation
        }
    }
    
    /**
     * Get user from session
     * 
     * @return array|null User data or null
     */
    public static function getSession() {
        self::startSession();
        
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            return $_SESSION['user_data'];
        }
        
        return null;
    }
    
    /**
     * Check if user is logged in via session
     * 
     * @return bool
     */
    public static function isLoggedIn() {
        self::startSession();
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Destroy session (logout)
     */
    public static function destroySession() {
        self::startSession();
        
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }
        
        // Destroy session
        $_SESSION = [];
        session_destroy();
    }
    
    /**
     * Require session authentication
     * Redirects to login if not authenticated
     */
    public static function requireSession() {
        if (!self::isLoggedIn()) {
            header('Location: /pages/login.html');
            exit;
        }
    }
    
    /**
     * Get session user type
     * 
     * @return string|null
     */
    public static function getUserType() {
        self::startSession();
        return $_SESSION['user_type'] ?? null;
    }
    
    /**
     * Get session user ID
     * 
     * @return int|null
     */
    public static function getUserId() {
        self::startSession();
        return $_SESSION['user_id'] ?? null;
    }
}
