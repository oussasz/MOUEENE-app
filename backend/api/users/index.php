<?php
/**
 * Users API Endpoints
 * User profile and management endpoints
 * 
 * @author Moueene Development Team
 * @version 1.0.0
 */

// Load required classes
require_once CLASSES_PATH . '/User.php';

// Get action from URL
$action = $parts[2] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Get request body
$input = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($action) {
    case 'profile':
        if ($method === 'GET') {
            handleGetProfile();
        } elseif ($method === 'PUT') {
            handleUpdateProfile($input);
        } else {
            Response::error('Method not allowed', 405);
        }
        break;
        
    case 'change-password':
        if ($method === 'POST') {
            handleChangePassword($input);
        } else {
            Response::error('Method not allowed', 405);
        }
        break;
        
    case 'bookings':
        if ($method === 'GET') {
            handleGetBookings();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;
        
    case 'favorites':
        if ($method === 'GET') {
            handleGetFavorites();
        } elseif ($method === 'POST') {
            handleAddFavorite($input);
        } elseif ($method === 'DELETE') {
            handleRemoveFavorite($input);
        } else {
            Response::error('Method not allowed', 405);
        }
        break;
        
    default:
        Response::error('Invalid users endpoint', 404);
}

/**
 * Get user profile
 */
function handleGetProfile() {
    Auth::requireAuth();
    $authUser = Auth::user();
    
    try {
        $user = new User();
        $profile = $user->findById($authUser['user_id']);
        
        if (!$profile) {
            Response::notFound('User');
        }
        
        Response::success($profile);
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to get profile');
    }
}

/**
 * Update user profile
 */
function handleUpdateProfile($data) {
    Auth::requireAuth();
    $authUser = Auth::user();
    
    try {
        if (is_array($data) && array_key_exists('gender', $data)) {
            $gender = is_string($data['gender']) ? trim($data['gender']) : $data['gender'];
            if ($gender === '' || $gender === null) {
                $data['gender'] = null;
            } elseif (!in_array($gender, ['male', 'female'], true)) {
                Response::error('Invalid gender. Allowed values: male, female.', 422);
            }
        }

        if (is_array($data) && array_key_exists('profile_picture', $data)) {
            $pic = is_string($data['profile_picture']) ? trim($data['profile_picture']) : $data['profile_picture'];
            if ($pic === '' || $pic === null) {
                $data['profile_picture'] = null;
            } elseif (is_string($pic)) {
                $isCloudinary = (strpos($pic, 'https://res.cloudinary.com/') === 0) || (strpos($pic, 'http://res.cloudinary.com/') === 0);
                $isDefault = (strpos($pic, '/assets/images/') === 0);
                if (!$isCloudinary && !$isDefault) {
                    Response::error('Invalid profile picture URL. Please upload via Cloudinary.', 422);
                }
            }
        }

        $user = new User();
        $result = $user->update($authUser['user_id'], $data);
        
        if ($result) {
            $updatedProfile = $user->findById($authUser['user_id']);
            Response::success($updatedProfile, 'Profile updated successfully');
        } else {
            Response::error('No changes made', 400);
        }
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to update profile');
    }
}

/**
 * Change password
 */
function handleChangePassword($data) {
    Auth::requireAuth();
    $authUser = Auth::user();
    
    // Validate input
    $validator = new Validator($data);
    $validator
        ->required('current_password')
        ->required('new_password')->minLength('new_password', 8)
        ->required('confirm_password');
    
    if ($validator->fails()) {
        Response::validationError($validator->getErrors());
    }
    
    if ($data['new_password'] !== $data['confirm_password']) {
        Response::error('Passwords do not match', 400);
    }
    
    try {
        $userModel = new User();
        $user = $userModel->findById($authUser['user_id']);
        
        // Get full user data with password hash
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT password_hash FROM users WHERE user_id = ?");
        $stmt->execute([$authUser['user_id']]);
        $userData = $stmt->fetch();
        
        if (!Auth::verifyPassword($data['current_password'], $userData['password_hash'])) {
            Response::error('Current password is incorrect', 401);
        }
        
        $newPasswordHash = Auth::hashPassword($data['new_password']);
        $userModel->updatePassword($authUser['user_id'], $newPasswordHash);
        
        Response::success(null, 'Password changed successfully');
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to change password');
    }
}

/**
 * Get user bookings
 */
function handleGetBookings() {
    Auth::requireAuth();
    $authUser = Auth::user();
    
    try {
        $db = Database::getConnection();
        
        // Check if database connection exists
        if ($db === null) {
            Response::success([], 'Database not available');
            return;
        }
        
        // Check if bookings table exists first
        $tableCheck = $db->query("SHOW TABLES LIKE 'bookings'");
        if ($tableCheck->rowCount() === 0) {
            // Return empty array if table doesn't exist yet
            Response::success([], 'No bookings found');
            return;
        }
        
        $user = new User();
        $bookings = $user->getUpcomingBookings($authUser['user_id'], 10);
        
        Response::success($bookings ?? []);
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        // Return empty array instead of server error for better UX
        Response::success([], 'Could not load bookings');
    }
}

/**
 * Get user favorites
 */
function handleGetFavorites() {
    Auth::requireAuth();
    $authUser = Auth::user();
    
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT f.*, p.first_name, p.last_name, p.business_name, 
                    p.profile_picture, p.average_rating, p.total_reviews
             FROM favorites f
             JOIN providers p ON f.provider_id = p.provider_id
             WHERE f.user_id = ?
             ORDER BY f.created_at DESC"
        );
        $stmt->execute([$authUser['user_id']]);
        $favorites = $stmt->fetchAll();
        
        Response::success($favorites);
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to get favorites');
    }
}

/**
 * Add favorite provider
 */
function handleAddFavorite($data) {
    Auth::requireAuth();
    $authUser = Auth::user();
    
    $validator = new Validator($data);
    $validator->required('provider_id');
    
    if ($validator->fails()) {
        Response::validationError($validator->getErrors());
    }
    
    try {
        $db = Database::getConnection();
        
        // Check if already favorited
        $stmt = $db->prepare("SELECT * FROM favorites WHERE user_id = ? AND provider_id = ?");
        $stmt->execute([$authUser['user_id'], $data['provider_id']]);
        
        if ($stmt->fetch()) {
            Response::error('Provider already in favorites', 409);
        }
        
        // Add favorite
        $stmt = $db->prepare("INSERT INTO favorites (user_id, provider_id) VALUES (?, ?)");
        $stmt->execute([$authUser['user_id'], $data['provider_id']]);
        
        Response::success(['favorite_id' => $db->lastInsertId()], 'Added to favorites', 201);
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to add favorite');
    }
}

/**
 * Remove favorite provider
 */
function handleRemoveFavorite($data) {
    Auth::requireAuth();
    $authUser = Auth::user();
    
    $validator = new Validator($data);
    $validator->required('provider_id');
    
    if ($validator->fails()) {
        Response::validationError($validator->getErrors());
    }
    
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM favorites WHERE user_id = ? AND provider_id = ?");
        $stmt->execute([$authUser['user_id'], $data['provider_id']]);
        
        Response::success(null, 'Removed from favorites');
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to remove favorite');
    }
}
