<?php
/**
 * Authentication Middleware
 * Protects routes and validates user access
 * 
 * @author Moueene Development Team
 * @version 1.0.0
 */

class AuthMiddleware {
    
    /**
     * Check if user is authenticated via JWT or session
     * 
     * @return bool
     */
    public static function isAuthenticated() {
        // Check JWT token first
        if (Auth::check()) {
            return true;
        }
        
        // Check session
        if (Auth::isLoggedIn()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Require authentication (JWT or session)
     */
    public static function requireAuth() {
        if (!self::isAuthenticated()) {
            Response::unauthorized('Authentication required');
        }
    }
    
    /**
     * Require specific user type
     * 
     * @param string|array $allowedTypes Allowed user types
     */
    public static function requireUserType($allowedTypes) {
        self::requireAuth();
        
        $allowedTypes = is_array($allowedTypes) ? $allowedTypes : [$allowedTypes];
        
        // Get user type from JWT or session
        $userType = null;
        
        if (Auth::check()) {
            $user = Auth::user();
            $userType = $user['user_type'] ?? null;
        } elseif (Auth::isLoggedIn()) {
            $userType = Auth::getUserType();
        }
        
        if (!in_array($userType, $allowedTypes)) {
            Response::error('Access forbidden', 403);
        }
    }
    
    /**
     * Require customer access
     */
    public static function requireCustomer() {
        self::requireUserType('user');
    }
    
    /**
     * Require provider access
     */
    public static function requireProvider() {
        self::requireUserType('provider');
    }
    
    /**
     * Require admin access
     */
    public static function requireAdmin() {
        self::requireUserType('admin');
    }
    
    /**
     * Check if current user owns the resource
     * 
     * @param int $resourceUserId Resource owner ID
     * @param string $userType User type
     * @return bool
     */
    public static function ownsResource($resourceUserId, $userType = 'user') {
        self::requireAuth();
        
        $currentUserId = null;
        $currentUserType = null;
        
        if (Auth::check()) {
            $user = Auth::user();
            $currentUserId = $user['user_id'] ?? null;
            $currentUserType = $user['user_type'] ?? null;
        } elseif (Auth::isLoggedIn()) {
            $currentUserId = Auth::getUserId();
            $currentUserType = Auth::getUserType();
        }
        
        return $currentUserId == $resourceUserId && $currentUserType == $userType;
    }
    
    /**
     * Require resource ownership
     * 
     * @param int $resourceUserId Resource owner ID
     * @param string $userType User type
     */
    public static function requireOwnership($resourceUserId, $userType = 'user') {
        if (!self::ownsResource($resourceUserId, $userType)) {
            Response::error('Access forbidden', 403);
        }
    }
}
