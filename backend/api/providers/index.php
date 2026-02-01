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

// Get action from URL by parsing the request path directly
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestPath = trim($requestPath ?? '', '/');

$afterProviders = '';
$providersPos = strpos($requestPath, '/providers');
if ($providersPos !== false) {
    $afterProviders = substr($requestPath, $providersPos + strlen('/providers'));
} else {
    if (preg_match('#providers/?(.*)$#', $requestPath, $matches)) {
        $afterProviders = $matches[1] ?? '';
    }
}

$afterProviders = trim($afterProviders, '/');
$segments = $afterProviders === '' ? [] : explode('/', $afterProviders);

$action = $segments[0] ?? '';
$subAction = $segments[1] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// DEBUG MODE - Remove after testing
if (isset($_GET['debug'])) {
    header('Content-Type: application/json');
    
    // Test database connection
    $dbStatus = 'unknown';
    $dbError = null;
    try {
        $testDb = Database::getConnection();
        if ($testDb === null) {
            $dbStatus = 'null returned';
        } else {
            $dbStatus = 'connected';
            // Test query
            $stmt = $testDb->query("SELECT COUNT(*) as cnt FROM providers");
            $row = $stmt->fetch();
            $dbStatus = 'connected - providers count: ' . ($row['cnt'] ?? 'unknown');
        }
    } catch (Exception $e) {
        $dbStatus = 'error';
        $dbError = $e->getMessage();
    }
    
    echo json_encode([
        'file' => 'providers/index.php',
        'requestPath' => $requestPath,
        'providersPos' => $providersPos,
        'afterProviders' => $afterProviders,
        'segments' => $segments,
        'action' => $action,
        'subAction' => $subAction,
        'method' => $method,
        'parts_from_parent' => $parts ?? 'not set',
        'db_status' => $dbStatus,
        'db_error' => $dbError
    ], JSON_PRETTY_PRINT);
    exit;
}

// Get request body
$input = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($action) {
    case '':
        if ($method === 'GET') {
            handlePublicList();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;

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
        $providerServiceId = $segments[1] ?? null;
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
        // Numeric ID - public provider details (non-sensitive) or sub-resources
        if (is_numeric($action)) {
            if ($subAction === 'services' && $method === 'GET') {
                handlePublicGetProviderServices((int)$action);
            } elseif ($subAction === 'reviews' && $method === 'GET') {
                handlePublicGetProviderReviews((int)$action);
            } elseif ($subAction === '' && $method === 'GET') {
                handlePublicGetProvider((int)$action);
            } else {
                 Response::error('Invalid provider endpoint', 404);
            }
        } else {
            Response::error('Invalid providers endpoint', 404);
        }
}

/**
 * Public: List providers
 * GET /api/v1/providers
 */
function handlePublicList() {
    try {
        $db = Database::getConnection();
        
        // Check if database connection failed
        if ($db === null) {
            Response::error('Database connection failed', 500);
            return;
        }

        $search = trim($_GET['search'] ?? '');
        $city = trim($_GET['city'] ?? '');
        $specialty = trim($_GET['specialty'] ?? '');
        $minRating = isset($_GET['min_rating']) ? (float)$_GET['min_rating'] : null;
        $verification = trim($_GET['verification_status'] ?? '');

        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = min(100, max(1, intval($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];

        // Hide deactivated/suspended providers from public listing
        $where[] = "account_status NOT IN ('deactivated', 'suspended')";

        if ($verification !== '') {
            $where[] = "verification_status = ?";
            $params[] = $verification;
        }

        if ($city !== '') {
            $where[] = "city LIKE ?";
            $params[] = "%{$city}%";
        }

        if ($specialty !== '') {
            $where[] = "(specialization LIKE ? OR business_name LIKE ?)";
            $params[] = "%{$specialty}%";
            $params[] = "%{$specialty}%";
        }

        if ($search !== '') {
            $where[] = "(first_name LIKE ? OR last_name LIKE ? OR business_name LIKE ? OR specialization LIKE ? OR city LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($minRating !== null && $minRating > 0) {
            $where[] = "average_rating >= ?";
            $params[] = $minRating;
        }

        $whereClause = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        $countSql = "SELECT COUNT(*) FROM providers {$whereClause}";
        $stmt = $db->prepare($countSql);
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        $sql = "SELECT
                    provider_id,
                    business_name,
                    first_name,
                    last_name,
                    profile_picture,
                    bio,
                    city,
                    specialization,
                    experience_years,
                    verification_status,
                    account_status,
                    average_rating,
                    total_reviews,
                    total_bookings,
                    completed_bookings,
                    availability_status,
                    created_at
                FROM providers
                {$whereClause}
                ORDER BY verification_status = 'verified' DESC, average_rating DESC, total_reviews DESC, created_at DESC
                LIMIT ? OFFSET ?";

        $queryParams = array_merge($params, [$limit, $offset]);
        $stmt = $db->prepare($sql);
        $stmt->execute($queryParams);
        $providers = $stmt->fetchAll();

        Response::paginated($providers, $total, $page, $limit);
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to load providers');
    }
}

/**
 * Public: Get provider by ID (non-sensitive)
 * GET /api/v1/providers/{id}
 */
function handlePublicGetProvider($providerId) {
    try {
        $db = Database::getConnection();

        $sql = "SELECT
                    provider_id,
                    business_name,
                    first_name,
                    last_name,
                    profile_picture,
                    bio,
                    address,
                    city,
                    state,
                    country,
                    specialization,
                    experience_years,
                    verification_status,
                    account_status,
                    average_rating,
                    total_reviews,
                    total_bookings,
                    completed_bookings,
                    cancelled_bookings,
                    availability_status,
                    created_at
                FROM providers
                WHERE provider_id = ?";

        $stmt = $db->prepare($sql);
        $stmt->execute([$providerId]);
        $provider = $stmt->fetch();

        if (!$provider) {
            Response::notFound('Provider');
        }

        Response::success($provider);
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to load provider');
    }
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

        // Optional images array: [{url, public_id}] (already uploaded to Cloudinary)
        $images = [];
        if (isset($data['images']) && is_array($data['images'])) {
            $max = min(10, count($data['images']));
            for ($i = 0; $i < $max; $i++) {
                $img = $data['images'][$i];
                if (!is_array($img)) continue;
                $url = trim((string)($img['url'] ?? ''));
                $publicId = isset($img['public_id']) ? trim((string)$img['public_id']) : null;
                if ($url === '') continue;
                $images[] = [
                    'url' => $url,
                    'public_id' => $publicId ?: null,
                ];
            }
        }

        $created = $provider->addService($authUser['user_id'], [
            'service_id' => (int)$data['service_id'],
            'price' => $data['price'],
            'price_type' => $data['price_type'] ?? 'fixed',
            'description' => $data['description'] ?? null,
            'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : true,
            'images' => $images
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

        // Optional images array: [{url, public_id}] (already uploaded to Cloudinary)
        if (array_key_exists('images', $data)) {
            $images = [];
            if (isset($data['images']) && is_array($data['images'])) {
                $max = min(10, count($data['images']));
                for ($i = 0; $i < $max; $i++) {
                    $img = $data['images'][$i];
                    if (!is_array($img)) continue;
                    $url = trim((string)($img['url'] ?? ''));
                    $publicId = isset($img['public_id']) ? trim((string)$img['public_id']) : null;
                    if ($url === '') continue;
                    $images[] = [
                        'url' => $url,
                        'public_id' => $publicId ?: null,
                    ];
                }
            }
            $payload['images'] = $images;
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

/**
 * Public: Get provider services
 * GET /api/v1/providers/{id}/services
 */
function handlePublicGetProviderServices($providerId) {
    try {
        $db = Database::getConnection();
        
        $sql = "SELECT ps.*, s.service_name, s.service_slug, s.description as master_description, sc.category_name
                FROM provider_services ps
                JOIN services s ON ps.service_id = s.service_id
                JOIN service_categories sc ON s.category_id = sc.category_id
                WHERE ps.provider_id = ? AND ps.is_active = TRUE
                ORDER BY s.service_name ASC";
                
        $stmt = $db->prepare($sql);
        $stmt->execute([$providerId]);
        $services = $stmt->fetchAll();
        
        Response::success($services);
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to load provider services');
    }
}

/**
 * Public: Get provider reviews
 * GET /api/v1/providers/{id}/reviews
 */
function handlePublicGetProviderReviews($providerId) {
    try {
        $db = Database::getConnection();
        
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = min(50, max(1, intval($_GET['limit'] ?? 10)));
        $offset = ($page - 1) * $limit;
        
        // Count reviews
        $countSql = "SELECT COUNT(*) FROM reviews WHERE provider_id = ? AND is_visible = TRUE";
        $stmt = $db->prepare($countSql);
        $stmt->execute([$providerId]);
        $total = $stmt->fetchColumn();
        
        // Get reviews
        $sql = "SELECT r.*, u.first_name, u.last_name, u.profile_picture, s.service_name
            FROM reviews r
            JOIN users u ON r.user_id = u.user_id
            JOIN services s ON r.service_id = s.service_id
            WHERE r.provider_id = ? AND r.is_visible = TRUE
            ORDER BY r.created_at DESC
            LIMIT ? OFFSET ?";
                
        $stmt = $db->prepare($sql);
        $stmt->execute([$providerId, $limit, $offset]);
        $reviews = $stmt->fetchAll();
        
        Response::paginated($reviews, $total, $page, $limit);
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to load provider reviews');
    }
}
