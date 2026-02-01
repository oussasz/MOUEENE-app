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

    case 'offers':
        if ($method === 'GET') {
            handleGetServiceOffers();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;

    case 'offers-popular':
        if ($method === 'GET') {
            handleGetPopularServiceOffers();
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
        if ($db === null) {
            Response::serverError('Database connection failed');
        }
        
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
        
    } catch (Throwable $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to fetch services');
    }
}

/**
 * Get provider service offerings (provider_services) with optional filters
 * GET /api/v1/services/offers
 */
function handleGetServiceOffers() {
    try {
        $db = Database::getConnection();
        if ($db === null) {
            Response::serverError('Database connection failed');
        }

        $providerServiceId = $_GET['provider_service_id'] ?? null;
        $includeImages = $_GET['include_images'] ?? null;
        $categoryId = $_GET['category_id'] ?? null;
        $search = $_GET['search'] ?? null;
        $language = $_GET['lang'] ?? 'en';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = min(100, max(1, intval($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $where = [
            'ps.is_active = TRUE',
            's.is_active = TRUE',
            "p.account_status = 'active'",
        ];
        $params = [$language];

        if ($providerServiceId !== null && $providerServiceId !== '' && is_numeric($providerServiceId)) {
            $where[] = 'ps.provider_service_id = ?';
            $params[] = (int)$providerServiceId;
        }

        if ($categoryId) {
            $where[] = 's.category_id = ?';
            $params[] = $categoryId;
        }

        if ($search) {
            $where[] = '(s.service_name LIKE ? OR s.description LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ? OR p.business_name LIKE ?)';
            $like = "%$search%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        // Total count
        $countSql = "SELECT COUNT(*)
            FROM provider_services ps
            JOIN services s ON ps.service_id = s.service_id
            JOIN providers p ON ps.provider_id = p.provider_id
            LEFT JOIN service_categories sc ON s.category_id = sc.category_id
            LEFT JOIN service_translations st ON s.service_id = st.service_id AND st.language_code = ?
            $whereSql";

        $stmt = $db->prepare($countSql);
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        // Page data
        $sql = "SELECT
                ps.provider_service_id,
                ps.provider_id,
                ps.service_id,
                ps.price AS base_price,
                ps.price_type,
                ps.total_bookings,
                ps.created_at,
                COALESCE(ps.description, COALESCE(st.translated_description, s.description)) AS description,
                COALESCE(st.translated_name, s.service_name) AS service_name,
                s.duration_minutes,
                s.category_id,
                sc.category_name,
                s.service_image,
                (
                    SELECT psi.image_url
                    FROM provider_service_images psi
                    WHERE psi.provider_service_id = ps.provider_service_id
                    ORDER BY psi.sort_order ASC, psi.image_id ASC
                    LIMIT 1
                ) AS primary_image,
                p.first_name,
                p.last_name,
                p.business_name,
                p.profile_picture AS provider_avatar,
                p.city AS provider_city,
                p.provider_type,
                p.verification_status,
                p.availability_status,
                p.service_fee_percentage,
                p.average_rating,
                p.total_reviews
            FROM provider_services ps
            JOIN services s ON ps.service_id = s.service_id
            JOIN providers p ON ps.provider_id = p.provider_id
            LEFT JOIN service_categories sc ON s.category_id = sc.category_id
            LEFT JOIN service_translations st ON s.service_id = st.service_id AND st.language_code = ?
            $whereSql
            ORDER BY p.average_rating DESC, ps.created_at DESC
            LIMIT ? OFFSET ?";

        $dataParams = $params;
        $dataParams[] = $limit;
        $dataParams[] = $offset;

        $stmt = $db->prepare($sql);
        $stmt->execute($dataParams);
        $offers = $stmt->fetchAll();

        $wantImages = false;
        if ($includeImages !== null) {
            $v = strtolower(trim((string)$includeImages));
            $wantImages = ($v === '1' || $v === 'true' || $v === 'yes');
        }
        if (!$wantImages && ($providerServiceId !== null && $providerServiceId !== '' && is_numeric($providerServiceId))) {
            // service-detail page typically fetches a single offer by id
            $wantImages = true;
        }

        if ($wantImages && $offers) {
            $ids = array_values(array_filter(array_map(function ($row) {
                return isset($row['provider_service_id']) ? (int)$row['provider_service_id'] : 0;
            }, $offers)));

            if ($ids) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $imgSql = "SELECT provider_service_id, image_url, public_id, sort_order
                    FROM provider_service_images
                    WHERE provider_service_id IN ($placeholders)
                    ORDER BY sort_order ASC, image_id ASC";
                $imgStmt = $db->prepare($imgSql);
                $imgStmt->execute($ids);
                $images = $imgStmt->fetchAll();

                $byPs = [];
                foreach ($images as $img) {
                    $psid = (int)($img['provider_service_id'] ?? 0);
                    if (!isset($byPs[$psid])) {
                        $byPs[$psid] = [];
                    }
                    $byPs[$psid][] = [
                        'url' => $img['image_url'] ?? null,
                        'public_id' => $img['public_id'] ?? null,
                        'sort_order' => isset($img['sort_order']) ? (int)$img['sort_order'] : 0,
                    ];
                }

                foreach ($offers as &$offer) {
                    $psid = (int)($offer['provider_service_id'] ?? 0);
                    $offer['images'] = $byPs[$psid] ?? [];
                }
                unset($offer);
            }
        }

        Response::paginated($offers, $total, $page, $limit);

    } catch (Throwable $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to fetch service offers');
    }
}

/**
 * Get popular provider service offerings (provider_services)
 * GET /api/v1/services/offers-popular
 */
function handleGetPopularServiceOffers() {
    try {
        $db = Database::getConnection();
        if ($db === null) {
            Response::success([], 'Database unavailable', 200, [
                'warning' => 'Database connection failed'
            ]);
        }
        $language = $_GET['lang'] ?? 'en';
        $limit = min(20, max(1, intval($_GET['limit'] ?? 8)));
        $categoryId = $_GET['category_id'] ?? null;

        $where = [
            'ps.is_active = TRUE',
            's.is_active = TRUE',
            "p.account_status = 'active'",
        ];
        $params = [$language];

        if ($categoryId) {
            $where[] = 's.category_id = ?';
            $params[] = $categoryId;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT
                ps.provider_service_id,
                ps.provider_id,
                ps.service_id,
                ps.price AS base_price,
                ps.price_type,
                ps.total_bookings,
                ps.created_at,
                COALESCE(ps.description, COALESCE(st.translated_description, s.description)) AS description,
                COALESCE(st.translated_name, s.service_name) AS service_name,
                s.category_id,
                sc.category_name,
                s.service_image,
                (
                    SELECT psi.image_url
                    FROM provider_service_images psi
                    WHERE psi.provider_service_id = ps.provider_service_id
                    ORDER BY psi.sort_order ASC, psi.image_id ASC
                    LIMIT 1
                ) AS primary_image,
                p.first_name,
                p.last_name,
                p.business_name,
                p.profile_picture AS provider_avatar,
                p.city AS provider_city,
                p.average_rating,
                p.total_reviews
            FROM provider_services ps
            JOIN services s ON ps.service_id = s.service_id
            JOIN providers p ON ps.provider_id = p.provider_id
            LEFT JOIN service_categories sc ON s.category_id = sc.category_id
            LEFT JOIN service_translations st ON s.service_id = st.service_id AND st.language_code = ?
            $whereSql
            ORDER BY ps.total_bookings DESC, p.average_rating DESC, ps.created_at DESC
            LIMIT ?";

        $params[] = $limit;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $offers = $stmt->fetchAll();

        Response::success($offers);

    } catch (Throwable $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to fetch popular service offers');
    }
}

/**
 * Get popular services
 */
function handleGetPopularServices() {
    try {
        $db = Database::getConnection();
        if ($db === null) {
            Response::serverError('Database connection failed');
        }
        $language = $_GET['lang'] ?? 'en';
        $limit = min(20, max(1, intval($_GET['limit'] ?? 10)));
        
    $sql = "SELECT s.*, sc.category_name,
                COALESCE(st.translated_name, s.service_name) as service_name,
                COALESCE(st.translated_description, s.description) as description
                FROM services s
        LEFT JOIN service_categories sc ON s.category_id = sc.category_id
                LEFT JOIN service_translations st ON s.service_id = st.service_id AND st.language_code = ?
                WHERE s.is_active = TRUE AND s.is_popular = TRUE
                ORDER BY s.total_bookings DESC, s.average_rating DESC
                LIMIT ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$language, $limit]);
        $services = $stmt->fetchAll();
        
        Response::success($services);
        
    } catch (Throwable $e) {
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
        if ($db === null) {
            Response::serverError('Database connection failed');
        }
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
        
    } catch (Throwable $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to fetch service');
    }
}
