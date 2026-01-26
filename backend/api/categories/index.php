<?php
/**
 * Categories API Endpoints
 * Handles category-related operations
 * 
 * @author Moueene Development Team
 * @version 1.0.0
 */

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    handleGetCategories();
} else {
    Response::error('Method not allowed', 405);
}

/**
 * Get all categories
 */
function handleGetCategories() {
    try {
        $db = Database::getConnection();
        $language = $_GET['lang'] ?? 'en';
        
        $sql = "SELECT sc.*, 
                COALESCE(sct.translated_name, sc.category_name) as category_name,
                COALESCE(sct.translated_description, sc.description) as description,
                (SELECT COUNT(*) FROM services WHERE category_id = sc.category_id AND is_active = TRUE) as service_count
                FROM service_categories sc
                LEFT JOIN service_category_translations sct ON sc.category_id = sct.category_id AND sct.language_code = ?
                WHERE sc.is_active = TRUE
                ORDER BY sc.display_order ASC, sc.category_name ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$language]);
        $categories = $stmt->fetchAll();
        
        Response::success($categories);
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::serverError('Failed to fetch categories');
    }
}
