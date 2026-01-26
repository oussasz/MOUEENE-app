<?php
/**
 * Services API Endpoints
 * Handles service-related operations
 * 
 * @author Moueene Development Team
 * @version 1.0.0
 */

$action = $parts[2] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($action) {
    case '':
        if ($method === 'GET') {
            handleGetServices();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;
        
    case 'popular':
        if ($method === 'GET') {
            handleGetPopularServices();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;
        
    default:
        // Numeric ID - get single service
        if (is_numeric($action) && $method === 'GET') {
            handleGetService($action);
        } else {
            Response::error('Invalid service endpoint', 404);
        }
}

/**
 * Get all services with optional filters
 */
function handleGetServices() {
    try {
        $db = Database::getConnection();
        
        // Get query parameters
        $categoryId = $_GET['category_id'] ?? null;
        $search = $_GET['search'] ?? null;
        $language = $_GET['lang'] ?? 'en';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = min(100, max(1, intval($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        
        // Build query
        $sql = "SELECT s.*, sc.category_name, 
                COALESCE(st.translated_name, s.service_name) as service_name,
                COALESCE(st.translated_description, s.description) as description
                FROM services s
                LEFT JOIN service_categories sc ON s.category_id = sc.category_id
                LEFT JOIN service_translations st ON s.service_id = st.service_id AND st.language_code = ?
                WHERE s.is_active = TRUE";
        
        $params = [$language];
        
        if ($categoryId) {
            $sql .= " AND s.category_id = ?";
            $params[] = $categoryId;
        }
        
        if ($search) {
            $sql .= " AND (s.service_name LIKE ? OR s.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        // Get total count
        $countSql = str_replace("SELECT s.*, sc.category_name, 
                COALESCE(st.translated_name, s.service_name) as service_name,
                COALESCE(st.translated_description, s.description) as description", "SELECT COUNT(*)", $sql);
        $stmt = $db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // Get services
        $sql .= " ORDER BY s.is_featured DESC, s.is_popular DESC, s.service_name ASC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $services = $stmt->fetchAll();
        
        Response::paginated($services, $total, $page, $limit);
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to fetch services');
    }
}

/**
 * Get popular services
 */
function handleGetPopularServices() {
    try {
        $db = Database::getConnection();
        $language = $_GET['lang'] ?? 'en';
        $limit = min(20, max(1, intval($_GET['limit'] ?? 10)));
        
        $sql = "SELECT s.*, 
                COALESCE(st.translated_name, s.service_name) as service_name,
                COALESCE(st.translated_description, s.description) as description
                FROM services s
                LEFT JOIN service_translations st ON s.service_id = st.service_id AND st.language_code = ?
                WHERE s.is_active = TRUE AND s.is_popular = TRUE
                ORDER BY s.total_bookings DESC, s.average_rating DESC
                LIMIT ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$language, $limit]);
        $services = $stmt->fetchAll();
        
        Response::success($services);
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to fetch popular services');
    }
}

/**
 * Get single service by ID
 */
function handleGetService($serviceId) {
    try {
        $db = Database::getConnection();
        $language = $_GET['lang'] ?? 'en';
        
        $sql = "SELECT s.*, sc.category_name,
                COALESCE(st.translated_name, s.service_name) as service_name,
                COALESCE(st.translated_description, s.description) as description,
                COALESCE(st.translated_detailed_description, s.detailed_description) as detailed_description
                FROM services s
                LEFT JOIN service_categories sc ON s.category_id = sc.category_id
                LEFT JOIN service_translations st ON s.service_id = st.service_id AND st.language_code = ?
                WHERE s.service_id = ? AND s.is_active = TRUE";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$language, $serviceId]);
        $service = $stmt->fetch();
        
        if (!$service) {
            Response::notFound('Service');
        }
        
        Response::success($service);
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to fetch service');
    }
}
