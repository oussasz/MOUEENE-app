-- Create provider_service_images table for provider-uploaded images (Cloudinary URLs)

CREATE TABLE IF NOT EXISTS provider_service_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    provider_service_id INT NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    public_id VARCHAR(255) NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (provider_service_id) REFERENCES provider_services(provider_service_id) ON DELETE CASCADE,
    INDEX idx_provider_service (provider_service_id),
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
