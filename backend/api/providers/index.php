<?php
/**
 * Providers API Endpoints
 * Provider profile and management endpoints
 * 
 * @author Moueene Development Team
 * @version 1.0.0
 */

// Load required classes
require_once CLASSES_PATH . '/Provider.php';

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
        
    case 'services':
        $providerServiceId = $parts[3] ?? null;
        if ($method === 'GET') {
            handleGetServices();
        } elseif ($method === 'POST') {
            handleAddService($input);
        } elseif ($method === 'PUT' && is_numeric($providerServiceId)) {
            handleUpdateServiceOffering((int)$providerServiceId, $input);
        } elseif ($method === 'DELETE' && is_numeric($providerServiceId)) {
            handleDeleteServiceOffering((int)$providerServiceId);
        } else {
            Response::error('Method not allowed', 405);
        }
        break;
        
    case 'statistics':
        if ($method === 'GET') {
            handleGetStatistics();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;
        
    default:
        Response::error('Invalid providers endpoint', 404);
}

/**
 * Get provider profile
 */
function handleGetProfile() {
    Auth::requireAuth();
    $authUser = Auth::user();
    
    if ($authUser['user_type'] !== 'provider') {
        Response::error('Access denied', 403);
    }
    
    try {
        $provider = new Provider();
        $profile = $provider->findById($authUser['user_id']);
        
        if (!$profile) {
            Response::notFound('Provider');
        }
        
        Response::success($profile);
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to get profile');
    }
}

/**
 * Update provider profile
 */
function handleUpdateProfile($data) {
    Auth::requireAuth();
    $authUser = Auth::user();
    
    if ($authUser['user_type'] !== 'provider') {
        Response::error('Access denied', 403);
    }
    
    try {
        $provider = new Provider();
        $result = $provider->update($authUser['user_id'], $data);
        
        if ($result) {
            $updatedProfile = $provider->findById($authUser['user_id']);
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
    
    if ($authUser['user_type'] !== 'provider') {
        Response::error('Access denied', 403);
    }
    
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
        $providerModel = new Provider();
        
        // Get full provider data with password hash
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT password_hash FROM providers WHERE provider_id = ?");
        $stmt->execute([$authUser['user_id']]);
        $providerData = $stmt->fetch();
        
        if (!Auth::verifyPassword($data['current_password'], $providerData['password_hash'])) {
            Response::error('Current password is incorrect', 401);
        }
        
        $newPasswordHash = Auth::hashPassword($data['new_password']);
        $providerModel->updatePassword($authUser['user_id'], $newPasswordHash);
        
        Response::success(null, 'Password changed successfully');
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to change password');
    }
}

/**
 * Get provider bookings
 */
function handleGetBookings() {
    Auth::requireAuth();
    $authUser = Auth::user();
    
    if ($authUser['user_type'] !== 'provider') {
        Response::error('Access denied', 403);
    }
    
    try {
        $provider = new Provider();
        $status = $_GET['status'] ?? null;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        
        $bookings = $provider->getBookings($authUser['user_id'], $status, $limit);
        
        Response::success($bookings);
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to get bookings');
    }
}

/**
 * Get provider services
 */
function handleGetServices() {
    Auth::requireAuth();
    $authUser = Auth::user();
    
    if ($authUser['user_type'] !== 'provider') {
        Response::error('Access denied', 403);
    }
    
    try {
        $provider = new Provider();
        $services = $provider->getServices($authUser['user_id']);
        
        Response::success($services);
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to get services');
    }
}

/**
 * Add provider service offering
 */
function handleAddService($data) {
    Auth::requireAuth();
    $authUser = Auth::user();

    if ($authUser['user_type'] !== 'provider') {
        Response::error('Access denied', 403);
    }

    $validator = new Validator($data);
    $validator
        ->required('service_id')
        ->numeric('service_id')
        ->required('price')
        ->numeric('price')
        ->min('price', 0)
        ->in('price_type', ['fixed', 'hourly', 'per_item', 'custom']);

    if ($validator->fails()) {
        Response::validationError($validator->getErrors());
    }

    try {
        $provider = new Provider();
        $created = $provider->addService($authUser['user_id'], [
            'service_id' => (int)$data['service_id'],
            'price' => $data['price'],
            'price_type' => $data['price_type'] ?? 'fixed',
            'description' => $data['description'] ?? null,
            'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : true
        ]);

        Response::success($created, 'Service added successfully', 201);

    } catch (Exception $e) {
        $msg = $e->getMessage();
        if ($msg === 'You already offer this service') {
            Response::error($msg, 409);
        }
        if ($msg === 'Service not found' || $msg === 'Provider not found') {
            Response::error($msg, 404);
        }

        error_log($e->getMessage());
        Response::serverError('Failed to add service');
    }
}

/**
 * Update provider service offering
 */
function handleUpdateServiceOffering($providerServiceId, $data) {
    Auth::requireAuth();
    $authUser = Auth::user();

    if ($authUser['user_type'] !== 'provider') {
        Response::error('Access denied', 403);
    }

    $validator = new Validator($data);
    if (isset($data['price'])) {
        $validator->numeric('price')->min('price', 0);
    }
    if (isset($data['price_type'])) {
        $validator->in('price_type', ['fixed', 'hourly', 'per_item', 'custom']);
    }

    if ($validator->fails()) {
        Response::validationError($validator->getErrors());
    }

    try {
        $provider = new Provider();
        $payload = [];
        if (array_key_exists('price', $data)) {
            $payload['price'] = $data['price'];
        }
        if (array_key_exists('price_type', $data)) {
            $payload['price_type'] = $data['price_type'];
        }
        if (array_key_exists('description', $data)) {
            $payload['description'] = $data['description'];
        }
        if (array_key_exists('is_active', $data)) {
            $payload['is_active'] = (bool)$data['is_active'];
        }

        $updated = $provider->updateServiceOffering($authUser['user_id'], $providerServiceId, $payload);

        Response::success($updated, 'Service updated successfully');

    } catch (Exception $e) {
        $msg = $e->getMessage();
        if ($msg === 'Service offering not found') {
            Response::error($msg, 404);
        }
        if ($msg === 'No changes provided') {
            Response::error($msg, 400);
        }
        error_log($e->getMessage());
        Response::serverError('Failed to update service');
    }
}

/**
 * Delete provider service offering
 */
function handleDeleteServiceOffering($providerServiceId) {
    Auth::requireAuth();
    $authUser = Auth::user();

    if ($authUser['user_type'] !== 'provider') {
        Response::error('Access denied', 403);
    }

    try {
        $provider = new Provider();
        $provider->deleteServiceOffering($authUser['user_id'], $providerServiceId);
        Response::success(null, 'Service deleted successfully');
    } catch (Exception $e) {
        $msg = $e->getMessage();
        if ($msg === 'Service offering not found') {
            Response::error($msg, 404);
        }
        error_log($e->getMessage());
        Response::serverError('Failed to delete service');
    }
}

/**
 * Get provider statistics
 */
function handleGetStatistics() {
    Auth::requireAuth();
    $authUser = Auth::user();
    
    if ($authUser['user_type'] !== 'provider') {
        Response::error('Access denied', 403);
    }
    
    try {
        $provider = new Provider();
        $stats = $provider->getStatistics($authUser['user_id']);
        
        Response::success($stats);
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to get statistics');
    }
}
