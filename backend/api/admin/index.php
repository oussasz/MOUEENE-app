<?php
/**
 * Admin API Endpoints
 * Handles admin-specific operations
 * 
 * @author Moueene Development Team
 * @version 1.0.0
 */

require_once CLASSES_PATH . '/Provider.php';
require_once CLASSES_PATH . '/User.php';

// Get action from URL
$action = $parts[2] ?? '';
$subAction = $parts[3] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Get request body
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// Require admin authentication for all endpoints
Auth::requireAuth();
$authUser = Auth::user();

if ($authUser['user_type'] !== 'admin') {
    Response::error('Admin access required', 403);
}

switch ($action) {
    case 'stats':
        if ($method === 'GET') {
            handleGetStats();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;

    case 'services':
        if ($subAction && is_numeric($subAction) && $method === 'DELETE') {
            handleDeleteService((int)$subAction);
        } elseif ($method === 'GET') {
            handleGetServices();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;
        
    case 'providers':
        if ($subAction && is_numeric($subAction)) {
            $providerId = (int)$subAction;
            $providerAction = $parts[4] ?? '';
            
            if ($providerAction === 'verify' && $method === 'POST') {
                handleVerifyProvider($providerId, $input);
            } else if ($method === 'GET') {
                handleGetProvider($providerId);
            } else if ($method === 'PUT') {
                handleUpdateProvider($providerId, $input);
            } else if ($method === 'DELETE') {
                handleDeleteProvider($providerId);
            } else {
                Response::error('Invalid action', 400);
            }
        } else if ($method === 'GET') {
            handleGetProviders();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;
        
    case 'users':
        if ($subAction && is_numeric($subAction)) {
            $userId = (int)$subAction;
            
            if ($method === 'GET') {
                handleGetUser($userId);
            } else if ($method === 'PUT') {
                handleUpdateUser($userId, $input);
            } else if ($method === 'DELETE') {
                handleDeleteUser($userId);
            } else {
                Response::error('Method not allowed', 405);
            }
        } else if ($method === 'GET') {
            handleGetUsers();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;
        
    case 'bookings':
        if ($method === 'GET') {
            handleGetAllBookings();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;
        
    default:
        Response::error('Invalid admin endpoint', 404);
}

/**
 * Admin: Get services (includes inactive)
 * GET /api/v1/admin/services
 *
 * Query Parameters:
 * - category_id (optional)
 * - search (optional)
 * - status (optional): active|inactive
 * - lang (optional): en|fr|ar (default en)
 * - page (optional): default 1
 * - limit (optional): default 50 (max 200)
 */
function handleGetServices() {
    try {
        $db = Database::getConnection();

        $scope = trim($_GET['scope'] ?? 'catalog'); // catalog|offered
        $categoryId = $_GET['category_id'] ?? null;
        $search = trim($_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $language = $_GET['lang'] ?? 'en';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = min(200, max(1, intval($_GET['limit'] ?? 50)));
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [$language];

        // For catalog scope, status refers to services.is_active.
        // For offered scope, status refers to provider_services.is_active.
        $offerStatusSql = '';
        $offerStatusParams = [];
        if ($scope === 'catalog') {
            if ($status === 'active') {
                $where[] = 's.is_active = TRUE';
            } elseif ($status === 'inactive') {
                $where[] = 's.is_active = FALSE';
            }
        } else {
            if ($status === 'active') {
                $offerStatusSql = ' AND ps.is_active = TRUE';
            } elseif ($status === 'inactive') {
                $offerStatusSql = ' AND ps.is_active = FALSE';
            }
        }

        if ($categoryId !== null && $categoryId !== '') {
            $where[] = 's.category_id = ?';
            $params[] = $categoryId;
        }

        if ($search !== '') {
            $where[] = '(s.service_name LIKE ? OR s.description LIKE ? OR st.translated_name LIKE ? OR st.translated_description LIKE ?)';
            $like = "%$search%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if ($scope === 'offered') {
            $where[] = "EXISTS (SELECT 1 FROM provider_services ps WHERE ps.service_id = s.service_id$offerStatusSql)";
        }

        $whereClause = count($where) > 0 ? ('WHERE ' . implode(' AND ', $where)) : '';

        $fromSql = "FROM services s
                LEFT JOIN service_categories sc ON s.category_id = sc.category_id
                LEFT JOIN service_translations st ON s.service_id = st.service_id AND st.language_code = ?
                $whereClause";

        // Count total
        $countSql = "SELECT COUNT(*) $fromSql";
        $stmt = $db->prepare($countSql);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        // Offered scope: add aggregate helper columns.
        $extraSelect = '';
        if ($scope === 'offered') {
            $extraSelect = ",
                (SELECT COUNT(DISTINCT ps2.provider_id) FROM provider_services ps2 WHERE ps2.service_id = s.service_id$offerStatusSql) as providers_offering,
                (SELECT MIN(ps3.price) FROM provider_services ps3 WHERE ps3.service_id = s.service_id$offerStatusSql) as min_price,
                (SELECT MAX(ps4.price) FROM provider_services ps4 WHERE ps4.service_id = s.service_id$offerStatusSql) as max_price";
        }

        // Fetch paged records
        $dataSql = "SELECT s.*, sc.category_name,
                COALESCE(st.translated_name, s.service_name) as service_name,
                COALESCE(st.translated_description, s.description) as description
                $extraSelect
                $fromSql
                ORDER BY s.is_featured DESC, s.is_popular DESC, s.service_name ASC
                LIMIT ? OFFSET ?";

        $dataParams = array_merge($params, [$limit, $offset]);
        $stmt = $db->prepare($dataSql);
        $stmt->execute($dataParams);
        $services = $stmt->fetchAll();

        Response::paginated($services, $total, $page, $limit);
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to fetch services');
    }
}

/**
 * Admin: Deactivate (soft-delete) a service
 * DELETE /api/v1/admin/services/{id}
 *
 * Notes:
 * - This does NOT hard-delete from DB to avoid cascading deletes (bookings, reviews, provider_services).
 * - It deactivates the catalog service and disables all provider offerings for it.
 */
function handleDeleteService(int $serviceId): void {
    try {
        $db = Database::getConnection();

        $stmt = $db->prepare('SELECT service_id, service_name, is_active FROM services WHERE service_id = ?');
        $stmt->execute([$serviceId]);
        $service = $stmt->fetch();

        if (!$service) {
            Response::notFound('Service');
        }

        $db->beginTransaction();

        // Deactivate catalog entry
        $stmt = $db->prepare('UPDATE services SET is_active = FALSE, is_popular = FALSE, is_featured = FALSE WHERE service_id = ?');
        $stmt->execute([$serviceId]);

        // Deactivate provider offerings for this service
        $stmt = $db->prepare('UPDATE provider_services SET is_active = FALSE WHERE service_id = ?');
        $stmt->execute([$serviceId]);

        $db->commit();

        Response::success(
            [
                'service_id' => (int)$service['service_id'],
                'service_name' => $service['service_name'],
                'was_active' => (bool)$service['is_active'],
            ],
            'Service deactivated successfully'
        );
    } catch (Exception $e) {
        if (isset($db) && $db && $db->inTransaction()) {
            $db->rollBack();
        }
        error_log('[MOUEENE-ADMIN] Delete service error: ' . $e->getMessage());
        Response::serverError('Failed to delete service');
    }
}

/**
 * Get dashboard statistics
 */
function handleGetStats() {
    try {
        $db = Database::getConnection();
        
        // Total providers
        $stmt = $db->query("SELECT COUNT(*) as count FROM providers");
        $totalProviders = $stmt->fetch()['count'];
        
        // Total users
        $stmt = $db->query("SELECT COUNT(*) as count FROM users");
        $totalUsers = $stmt->fetch()['count'];
        
        // Pending verifications
        $stmt = $db->query("SELECT COUNT(*) as count FROM providers WHERE verification_status = 'pending'");
        $pendingVerifications = $stmt->fetch()['count'];
        
        // Total bookings
        $stmt = $db->query("SELECT COUNT(*) as count FROM bookings");
        $totalBookings = $stmt->fetch()['count'];
        
        // Verified providers
        $stmt = $db->query("SELECT COUNT(*) as count FROM providers WHERE verification_status = 'verified'");
        $verifiedProviders = $stmt->fetch()['count'];
        
        // Active users (logged in within last 30 days)
        $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE last_login > DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $activeUsers = $stmt->fetch()['count'];
        
        Response::success([
            'total_providers' => (int)$totalProviders,
            'total_users' => (int)$totalUsers,
            'pending_verifications' => (int)$pendingVerifications,
            'total_bookings' => (int)$totalBookings,
            'verified_providers' => (int)$verifiedProviders,
            'active_users' => (int)$activeUsers
        ]);
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to load statistics');
    }
}

/**
 * Get all providers with filters
 */
function handleGetProviders() {
    try {
        $db = Database::getConnection();
        
        $status = $_GET['status'] ?? null;
        $verification = $_GET['verification'] ?? null;
        $includeDeactivated = isset($_GET['include_deactivated']) && (string)$_GET['include_deactivated'] === '1';
        $limit = min((int)($_GET['limit'] ?? 20), 100);
        $offset = (int)($_GET['offset'] ?? 0);
        
        $where = [];
        $params = [];

        // By default, hide deactivated/deleted providers unless explicitly requested
        if (!$includeDeactivated && ($status === null || $status === '' || $status === 'pending')) {
            $where[] = "account_status NOT IN ('deactivated', 'deleted')";
        }
        
        if ($status === 'pending') {
            $where[] = "verification_status = 'pending'";
        } else if ($status) {
            $where[] = "account_status = ?";
            $params[] = $status;
        }
        
        if ($verification) {
            $where[] = "verification_status = ?";
            $params[] = $verification;
        }
        
        $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT provider_id, email, first_name, last_name, phone, city, 
                       profile_picture, verification_status, account_status, 
                       average_rating, total_reviews, created_at
                FROM providers 
                $whereClause
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $providers = $stmt->fetchAll();
        
        Response::success($providers);
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to load providers');
    }
}

/**
 * Get single provider details
 */
function handleGetProvider($providerId) {
    try {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT * FROM providers WHERE provider_id = ?");
        $stmt->execute([$providerId]);
        $provider = $stmt->fetch();
        
        if (!$provider) {
            Response::notFound('Provider');
        }
        
        // Remove sensitive data
        unset($provider['password_hash'], $provider['reset_token'], $provider['verification_token']);
        
        Response::success($provider);
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to load provider');
    }
}

/**
 * Verify or reject a provider
 */
function handleVerifyProvider($providerId, $data) {
    $validator = new Validator($data);
    $validator->required('status')->in('status', ['verified', 'rejected']);
    
    if ($validator->fails()) {
        Response::validationError($validator->getErrors());
    }
    
    try {
        $db = Database::getConnection();
        
        // Check provider exists
        $stmt = $db->prepare("SELECT provider_id, email, first_name FROM providers WHERE provider_id = ?");
        $stmt->execute([$providerId]);
        $provider = $stmt->fetch();
        
        if (!$provider) {
            Response::notFound('Provider');
        }
        
        // Update verification status
        $status = $data['status'];
        $accountStatus = $status === 'verified' ? 'active' : 'inactive';
        
        $stmt = $db->prepare(
            "UPDATE providers 
             SET verification_status = ?, 
                 account_status = ?,
                 verification_date = NOW() 
             WHERE provider_id = ?"
        );
        $stmt->execute([$status, $accountStatus, $providerId]);
        
        // TODO: Send notification email to provider
        
        Response::success([
            'provider_id' => $providerId,
            'verification_status' => $status,
            'account_status' => $accountStatus
        ], "Provider {$status} successfully");
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to update verification status');
    }
}

/**
 * Update provider details
 */
function handleUpdateProvider($providerId, $data) {
    try {
        $db = Database::getConnection();
        
        // Check provider exists
        $stmt = $db->prepare("SELECT provider_id FROM providers WHERE provider_id = ?");
        $stmt->execute([$providerId]);
        
        if (!$stmt->fetch()) {
            Response::notFound('Provider');
        }
        
        // Build update query dynamically based on provided fields
        $allowedFields = ['account_status', 'verification_status', 'first_name', 'last_name', 'phone', 'city', 'address'];
        $updates = [];
        $params = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            Response::error('No valid fields to update', 400);
        }
        
        $params[] = $providerId;
        $sql = "UPDATE providers SET " . implode(', ', $updates) . " WHERE provider_id = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        Response::success(null, 'Provider updated successfully');
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to update provider');
    }
}

/**
 * Delete/deactivate provider
 */
function handleDeleteProvider($providerId) {
    try {
        $db = Database::getConnection();

        // Check provider exists
        $stmt = $db->prepare("SELECT provider_id FROM providers WHERE provider_id = ?");
        $stmt->execute([$providerId]);
        if (!$stmt->fetch()) {
            Response::notFound('Provider');
        }
        
        // Try permanent delete first
        try {
            $stmt = $db->prepare("DELETE FROM providers WHERE provider_id = ?");
            $stmt->execute([$providerId]);

            if ($stmt->rowCount() > 0) {
                Response::success(null, 'Provider deleted permanently');
            }
        } catch (PDOException $e) {
            // If FK constraints prevent deletion, fall back to deactivation
            $stmt = $db->prepare("UPDATE providers SET account_status = 'deactivated' WHERE provider_id = ?");
            $stmt->execute([$providerId]);
            Response::success(
                ['soft_deleted' => true],
                'Provider has related records; account deactivated instead'
            );
        }

        // If DELETE affected no rows, treat as not found
        Response::notFound('Provider');
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to delete provider');
    }
}

/**
 * Get all users with filters
 */
function handleGetUsers() {
    try {
        $db = Database::getConnection();
        
        $status = $_GET['status'] ?? null;
        $includeDeactivated = isset($_GET['include_deactivated']) && (string)$_GET['include_deactivated'] === '1';
        $limit = min((int)($_GET['limit'] ?? 20), 100);
        $offset = (int)($_GET['offset'] ?? 0);
        
        $where = [];
        $params = [];

        // By default, hide deactivated/deleted users unless explicitly requested
        if (!$includeDeactivated && ($status === null || $status === '')) {
            $where[] = "account_status NOT IN ('deactivated', 'deleted')";
        }
        
        if ($status) {
            $where[] = "account_status = ?";
            $params[] = $status;
        }
        
        $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT user_id, email, first_name, last_name, phone, city, 
                       profile_picture, account_status, registration_date, last_login
                FROM users 
                $whereClause
                ORDER BY registration_date DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();
        
        Response::success($users);
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to load users');
    }
}

/**
 * Get single user details
 */
function handleGetUser($userId) {
    try {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            Response::notFound('User');
        }
        
        // Remove sensitive data
        unset($user['password_hash'], $user['reset_token'], $user['verification_token']);
        
        Response::success($user);
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to load user');
    }
}

/**
 * Update user details
 */
function handleUpdateUser($userId, $data) {
    try {
        $db = Database::getConnection();
        
        // Check user exists
        $stmt = $db->prepare("SELECT user_id FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        if (!$stmt->fetch()) {
            Response::notFound('User');
        }
        
        // Build update query dynamically based on provided fields
        $allowedFields = ['account_status', 'first_name', 'last_name', 'phone', 'city', 'address'];
        $updates = [];
        $params = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            Response::error('No valid fields to update', 400);
        }
        
        $params[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE user_id = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        Response::success(null, 'User updated successfully');
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to update user');
    }
}

/**
 * Delete/deactivate user
 */
function handleDeleteUser($userId) {
    try {
        $db = Database::getConnection();

        // Check user exists
        $stmt = $db->prepare("SELECT user_id FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        if (!$stmt->fetch()) {
            Response::notFound('User');
        }
        
        // Try permanent delete first
        try {
            $stmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);

            if ($stmt->rowCount() > 0) {
                Response::success(null, 'User deleted permanently');
            }
        } catch (PDOException $e) {
            // If FK constraints prevent deletion, fall back to deactivation
            $stmt = $db->prepare("UPDATE users SET account_status = 'deactivated' WHERE user_id = ?");
            $stmt->execute([$userId]);
            Response::success(
                ['soft_deleted' => true],
                'User has related records; account deactivated instead'
            );
        }

        // If DELETE affected no rows, treat as not found
        Response::notFound('User');
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to delete user');
    }
}

/**
 * Get all bookings
 */
function handleGetAllBookings() {
    try {
        $db = Database::getConnection();
        
        $status = $_GET['status'] ?? null;
        $limit = min((int)($_GET['limit'] ?? 20), 100);
        $offset = (int)($_GET['offset'] ?? 0);
        
        $where = [];
        $params = [];
        
        if ($status) {
            $where[] = "b.booking_status = ?";
            $params[] = $status;
        }
        
        $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT b.*, 
                       u.first_name as user_first_name, u.last_name as user_last_name, u.email as user_email,
                       p.first_name as provider_first_name, p.last_name as provider_last_name, p.email as provider_email,
                       s.service_name
                FROM bookings b
                JOIN users u ON b.user_id = u.user_id
                JOIN providers p ON b.provider_id = p.provider_id
                JOIN services s ON b.service_id = s.service_id
                $whereClause
                ORDER BY b.created_at DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $bookings = $stmt->fetchAll();
        
        Response::success($bookings);
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to load bookings');
    }
}
