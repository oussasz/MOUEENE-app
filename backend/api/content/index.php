<?php
/**
 * Content Pages API Endpoints
 * Handles CMS content retrieval
 * 
 * @author Moueene Development Team
 * @version 1.0.0
 */

$action = $parts[2] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

if (empty($action)) {
    Response::error('Page slug required', 400);
}

try {
    $db = Database::getConnection();
    $language = $_GET['lang'] ?? 'en';
    
    $sql = "SELECT cp.*, 
            COALESCE(cpt.page_title, cp.page_slug) as page_title,
            cpt.page_content,
            cpt.meta_title,
            cpt.meta_description
            FROM content_pages cp
            LEFT JOIN content_page_translations cpt ON cp.page_id = cpt.page_id AND cpt.language_code = ?
            WHERE cp.page_slug = ? AND cp.is_active = TRUE";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$language, $action]);
    $page = $stmt->fetch();
    
    if (!$page) {
        Response::notFound('Page');
    }
    
    Response::success($page);
    
} catch (Exception $e) {
    error_log($e->getMessage());
    Response::serverError('Failed to fetch page content');
}
