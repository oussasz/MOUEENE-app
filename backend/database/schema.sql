-- ============================================================================
-- MOUEENE DATABASE SCHEMA
-- Home Services Platform Database Structure
-- 
-- Description: Comprehensive database schema for a service marketplace
--              connecting customers with service providers
-- 
-- Version: 1.0.0
-- Date: 2026-01-26
-- Author: Moueene Development Team
-- ============================================================================

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS moueene_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE moueene_db;

-- Drop existing tables in correct order (to handle foreign key constraints)
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS booking_history;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS service_availability;
DROP TABLE IF EXISTS provider_services;
DROP TABLE IF EXISTS service_translations;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS service_category_translations;
DROP TABLE IF EXISTS service_categories;
DROP TABLE IF EXISTS content_page_translations;
DROP TABLE IF EXISTS content_pages;
DROP TABLE IF EXISTS provider_documents;
DROP TABLE IF EXISTS providers;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS languages;
DROP TABLE IF EXISTS admin_users;

-- ============================================================================
-- LANGUAGES TABLE
-- Supported platform languages (Arabic, French, English)
-- ============================================================================
CREATE TABLE languages (
    language_code VARCHAR(10) PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    native_name VARCHAR(50) NOT NULL,
    direction ENUM('ltr', 'rtl') DEFAULT 'ltr',
    is_active BOOLEAN DEFAULT TRUE,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_active (is_active),
    INDEX idx_default (is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- USERS TABLE
-- Stores customer account information
-- ============================================================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    profile_picture VARCHAR(255),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    zip_code VARCHAR(20),
    country VARCHAR(100) DEFAULT 'Algeria',
    date_of_birth DATE,
    gender ENUM('male', 'female') DEFAULT NULL,
    email_verified BOOLEAN DEFAULT FALSE,
    phone_verified BOOLEAN DEFAULT FALSE,
    account_status ENUM('active', 'suspended', 'deactivated', 'deleted') DEFAULT 'active',
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    reset_token VARCHAR(255),
    reset_token_expires TIMESTAMP NULL,
    verification_token VARCHAR(255),
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    two_factor_secret VARCHAR(255),
    preferred_language VARCHAR(10) DEFAULT 'en',
    timezone VARCHAR(50) DEFAULT 'Africa/Casablanca',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_account_status (account_status),
    INDEX idx_registration_date (registration_date),
    INDEX idx_preferred_language (preferred_language),
    
    FOREIGN KEY (preferred_language) REFERENCES languages(language_code) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PROVIDERS TABLE
-- Stores service provider information and profiles
-- ============================================================================
CREATE TABLE providers (
    provider_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    business_name VARCHAR(255),
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    profile_picture VARCHAR(255),
    bio TEXT,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100),
    zip_code VARCHAR(20),
    country VARCHAR(100) DEFAULT 'Algeria',
    date_of_birth DATE,
    gender ENUM('male', 'female') DEFAULT NULL,
    
    -- Professional Information
    experience_years INT DEFAULT 0,
    certification TEXT,
    specialization TEXT,
    languages_spoken VARCHAR(255),
    service_radius INT DEFAULT 10, -- in kilometers
    
    -- Business Information
    commercial_registry_number VARCHAR(100),
    nif VARCHAR(100),
    nis TEXT,
    
    -- Verification & Status
    email_verified BOOLEAN DEFAULT FALSE,
    phone_verified BOOLEAN DEFAULT FALSE,
    identity_verified BOOLEAN DEFAULT FALSE,
    background_check_verified BOOLEAN DEFAULT FALSE,
    verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    verification_date TIMESTAMP NULL,
    account_status ENUM('active', 'inactive', 'suspended', 'deactivated', 'pending') DEFAULT 'pending',
    
    -- Rating & Performance
    average_rating DECIMAL(3,2) DEFAULT 0.00,
    total_reviews INT DEFAULT 0,
    total_bookings INT DEFAULT 0,
    completed_bookings INT DEFAULT 0,
    cancelled_bookings INT DEFAULT 0,
    response_rate DECIMAL(5,2) DEFAULT 0.00,
    acceptance_rate DECIMAL(5,2) DEFAULT 0.00,
    
    -- Financial
    provider_type ENUM('freelancer', 'self_employed', 'company') DEFAULT 'freelancer',
    service_fee_percentage DECIMAL(5,2) DEFAULT 15.00,
    
    -- Availability
    availability_status ENUM('available', 'busy', 'offline') DEFAULT 'offline',
    
    -- Account Management
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    reset_token VARCHAR(255),
    reset_token_expires TIMESTAMP NULL,
    verification_token VARCHAR(255),
    preferred_language VARCHAR(10) DEFAULT 'en',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_city (city),
    INDEX idx_verification_status (verification_status),
    INDEX idx_account_status (account_status),
    INDEX idx_average_rating (average_rating),
    INDEX idx_availability (availability_status),
    INDEX idx_preferred_language (preferred_language),
    
    FOREIGN KEY (preferred_language) REFERENCES languages(language_code) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PROVIDER DOCUMENTS TABLE
-- Stores uploaded documents for provider verification
-- ============================================================================
CREATE TABLE provider_documents (
    document_id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    document_type ENUM('id_card', 'passport', 'driver_license', 'business_license', 'certificate', 'insurance', 'other') NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    document_path VARCHAR(255) NOT NULL,
    document_number VARCHAR(100),
    issue_date DATE,
    expiry_date DATE,
    verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    verified_by INT,
    verified_at TIMESTAMP NULL,
    rejection_reason TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (provider_id) REFERENCES providers(provider_id) ON DELETE CASCADE,
    INDEX idx_provider (provider_id),
    INDEX idx_verification_status (verification_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SERVICE CATEGORIES TABLE
-- Defines main service categories
-- ============================================================================
CREATE TABLE service_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    category_slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(255),
    image VARCHAR(255),
    parent_category_id INT NULL,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_category_id) REFERENCES service_categories(category_id) ON DELETE SET NULL,
    INDEX idx_slug (category_slug),
    INDEX idx_active (is_active),
    INDEX idx_parent (parent_category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SERVICE CATEGORY TRANSLATIONS TABLE
-- Localized category names and descriptions
-- ============================================================================
CREATE TABLE service_category_translations (
    category_id INT NOT NULL,
    language_code VARCHAR(10) NOT NULL,
    translated_name VARCHAR(100) NOT NULL,
    translated_description TEXT,
    
    PRIMARY KEY (category_id, language_code),
    FOREIGN KEY (category_id) REFERENCES service_categories(category_id) ON DELETE CASCADE,
    FOREIGN KEY (language_code) REFERENCES languages(language_code) ON UPDATE CASCADE ON DELETE CASCADE,
    
    INDEX idx_language (language_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SERVICES TABLE
-- Defines specific services within categories
-- ============================================================================
CREATE TABLE services (
    service_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    service_name VARCHAR(255) NOT NULL,
    service_slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    detailed_description LONGTEXT,
    service_image VARCHAR(255),
    duration_minutes INT DEFAULT 60,
    base_price DECIMAL(10,2) NOT NULL,
    price_type ENUM('fixed', 'hourly', 'per_item', 'custom') DEFAULT 'fixed',
    is_popular BOOLEAN DEFAULT FALSE,
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    total_providers INT DEFAULT 0,
    total_bookings INT DEFAULT 0,
    average_rating DECIMAL(3,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES service_categories(category_id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_slug (service_slug),
    INDEX idx_active (is_active),
    INDEX idx_popular (is_popular),
    INDEX idx_featured (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SERVICE TRANSLATIONS TABLE
-- Localized service names and descriptions
-- ============================================================================
CREATE TABLE service_translations (
    service_id INT NOT NULL,
    language_code VARCHAR(10) NOT NULL,
    translated_name VARCHAR(255) NOT NULL,
    translated_description TEXT,
    translated_detailed_description LONGTEXT,
    
    PRIMARY KEY (service_id, language_code),
    FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE CASCADE,
    FOREIGN KEY (language_code) REFERENCES languages(language_code) ON UPDATE CASCADE ON DELETE CASCADE,
    
    INDEX idx_language (language_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- CONTENT PAGES TABLE
-- CMS pages for About, Terms, Privacy, FAQ, etc.
-- ============================================================================
CREATE TABLE content_pages (
    page_id INT AUTO_INCREMENT PRIMARY KEY,
    page_slug VARCHAR(100) NOT NULL UNIQUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_slug (page_slug),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- CONTENT PAGE TRANSLATIONS TABLE
-- Localized page titles and content
-- ============================================================================
CREATE TABLE content_page_translations (
    page_id INT NOT NULL,
    language_code VARCHAR(10) NOT NULL,
    page_title VARCHAR(255) NOT NULL,
    page_content LONGTEXT NOT NULL,
    meta_title VARCHAR(255),
    meta_description TEXT,
    
    PRIMARY KEY (page_id, language_code),
    FOREIGN KEY (page_id) REFERENCES content_pages(page_id) ON DELETE CASCADE,
    FOREIGN KEY (language_code) REFERENCES languages(language_code) ON UPDATE CASCADE ON DELETE CASCADE,
    
    INDEX idx_language (language_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PROVIDER SERVICES TABLE
-- Links providers with services they offer
-- ============================================================================
CREATE TABLE provider_services (
    provider_service_id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    service_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    price_type ENUM('fixed', 'hourly', 'per_item', 'custom') DEFAULT 'fixed',
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    total_bookings INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (provider_id) REFERENCES providers(provider_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE CASCADE,
    UNIQUE KEY unique_provider_service (provider_id, service_id),
    INDEX idx_provider (provider_id),
    INDEX idx_service (service_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PROVIDER SERVICE IMAGES TABLE
-- Stores provider-uploaded images for a specific service offering
-- ============================================================================
CREATE TABLE provider_service_images (
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

-- ============================================================================
-- SERVICE AVAILABILITY TABLE
-- Defines provider availability schedule
-- ============================================================================
CREATE TABLE service_availability (
    availability_id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    day_of_week ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (provider_id) REFERENCES providers(provider_id) ON DELETE CASCADE,
    INDEX idx_provider (provider_id),
    INDEX idx_day (day_of_week)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- BOOKINGS TABLE
-- Stores service booking information
-- ============================================================================
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_reference VARCHAR(50) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    provider_id INT NOT NULL,
    service_id INT NOT NULL,
    
    -- Booking Details
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    duration_minutes INT DEFAULT 60,
    
    -- Location
    service_address TEXT NOT NULL,
    service_city VARCHAR(100) NOT NULL,
    service_zip_code VARCHAR(20),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    
    -- Pricing
    service_price DECIMAL(10,2) NOT NULL,
    service_fee DECIMAL(10,2) DEFAULT 0.00,
    total_price DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    final_price DECIMAL(10,2) NOT NULL,
    
    -- Status & Workflow
    booking_status ENUM(
        'pending',
        'confirmed', 
        'in_progress',
        'completed',
        'cancelled',
        'rejected',
        'refunded'
    ) DEFAULT 'pending',
    
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    
    -- Additional Information
    special_instructions TEXT,
    cancellation_reason TEXT,
    cancellation_date TIMESTAMP NULL,
    cancelled_by ENUM('user', 'provider', 'admin') NULL,
    
    -- Communication
    provider_notes TEXT,
    admin_notes TEXT,
    
    -- Timestamps
    confirmed_at TIMESTAMP NULL,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES providers(provider_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE CASCADE,
    
    INDEX idx_user (user_id),
    INDEX idx_provider (provider_id),
    INDEX idx_service (service_id),
    INDEX idx_reference (booking_reference),
    INDEX idx_status (booking_status),
    INDEX idx_payment_status (payment_status),
    INDEX idx_booking_date (booking_date),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- BOOKING HISTORY TABLE
-- Tracks all status changes for bookings
-- ============================================================================
CREATE TABLE booking_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    previous_status VARCHAR(50),
    new_status VARCHAR(50) NOT NULL,
    changed_by_type ENUM('user', 'provider', 'admin', 'system') NOT NULL,
    changed_by_id INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    INDEX idx_booking (booking_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PAYMENTS TABLE
-- Stores payment transaction records
-- ============================================================================
CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    provider_id INT NOT NULL,
    
    -- Payment Details
    transaction_id VARCHAR(255) UNIQUE,
    payment_method ENUM('credit_card', 'debit_card', 'paypal', 'stripe', 'cash', 'bank_transfer') NOT NULL,
    payment_gateway VARCHAR(50),
    
    -- Amounts
    amount DECIMAL(10,2) NOT NULL,
    service_fee DECIMAL(10,2) DEFAULT 0.00,
    provider_amount DECIMAL(10,2) NOT NULL,
    platform_fee DECIMAL(10,2) DEFAULT 0.00,
    
    -- Status
    payment_status ENUM('pending', 'processing', 'completed', 'failed', 'refunded', 'cancelled') DEFAULT 'pending',
    
    -- Additional Information
    currency VARCHAR(3) DEFAULT 'MAD',
    payment_description TEXT,
    failure_reason TEXT,
    refund_amount DECIMAL(10,2),
    refund_reason TEXT,
    refund_date TIMESTAMP NULL,
    
    -- Card Information (encrypted/tokenized)
    card_last_four VARCHAR(4),
    card_brand VARCHAR(50),
    
    -- Timestamps
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES providers(provider_id) ON DELETE CASCADE,
    
    INDEX idx_booking (booking_id),
    INDEX idx_user (user_id),
    INDEX idx_provider (provider_id),
    INDEX idx_transaction (transaction_id),
    INDEX idx_status (payment_status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- REVIEWS TABLE
-- Stores customer reviews and ratings for providers
-- ============================================================================
CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    provider_id INT NOT NULL,
    service_id INT NOT NULL,
    
    -- Rating (1-5 stars)
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    
    -- Review Content
    title VARCHAR(255),
    comment TEXT,
    
    -- Detailed Ratings
    professionalism_rating INT CHECK (professionalism_rating >= 1 AND professionalism_rating <= 5),
    punctuality_rating INT CHECK (punctuality_rating >= 1 AND punctuality_rating <= 5),
    quality_rating INT CHECK (quality_rating >= 1 AND quality_rating <= 5),
    value_rating INT CHECK (value_rating >= 1 AND value_rating <= 5),
    
    -- Provider Response
    provider_response TEXT,
    provider_response_date TIMESTAMP NULL,
    
    -- Moderation
    is_verified BOOLEAN DEFAULT FALSE,
    is_featured BOOLEAN DEFAULT FALSE,
    is_visible BOOLEAN DEFAULT TRUE,
    flagged BOOLEAN DEFAULT FALSE,
    flag_reason TEXT,
    
    -- Helpful votes
    helpful_count INT DEFAULT 0,
    not_helpful_count INT DEFAULT 0,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES providers(provider_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_booking_review (booking_id),
    INDEX idx_provider (provider_id),
    INDEX idx_user (user_id),
    INDEX idx_service (service_id),
    INDEX idx_rating (rating),
    INDEX idx_created_at (created_at),
    INDEX idx_visible (is_visible)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- FAVORITES TABLE
-- Stores user's favorite providers
-- ============================================================================
CREATE TABLE favorites (
    favorite_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    provider_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES providers(provider_id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_favorite (user_id, provider_id),
    INDEX idx_user (user_id),
    INDEX idx_provider (provider_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- MESSAGES TABLE
-- Stores messages between users and providers
-- ============================================================================
CREATE TABLE messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_type ENUM('user', 'provider', 'admin') NOT NULL,
    sender_id INT NOT NULL,
    receiver_type ENUM('user', 'provider', 'admin') NOT NULL,
    receiver_id INT NOT NULL,
    booking_id INT,
    
    -- Message Content
    subject VARCHAR(255),
    message TEXT NOT NULL,
    attachment_path VARCHAR(255),
    
    -- Status
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    is_deleted_by_sender BOOLEAN DEFAULT FALSE,
    is_deleted_by_receiver BOOLEAN DEFAULT FALSE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE SET NULL,
    
    INDEX idx_sender (sender_type, sender_id),
    INDEX idx_receiver (receiver_type, receiver_id),
    INDEX idx_booking (booking_id),
    INDEX idx_created_at (created_at),
    INDEX idx_read_status (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- NOTIFICATIONS TABLE
-- Stores system notifications for users and providers
-- ============================================================================
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_type ENUM('user', 'provider', 'admin') NOT NULL,
    recipient_id INT NOT NULL,
    
    -- Notification Details
    notification_type ENUM(
        'booking_confirmed',
        'booking_cancelled',
        'booking_reminder',
        'booking_completed',
        'payment_received',
        'review_received',
        'message_received',
        'account_verification',
        'promotion',
        'system_alert'
    ) NOT NULL,
    
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    action_url VARCHAR(255),
    related_id INT,
    
    -- Status
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    is_sent BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP NULL,
    
    -- Delivery Channels
    sent_via_email BOOLEAN DEFAULT FALSE,
    sent_via_sms BOOLEAN DEFAULT FALSE,
    sent_via_push BOOLEAN DEFAULT FALSE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_recipient (recipient_type, recipient_id),
    INDEX idx_type (notification_type),
    INDEX idx_read_status (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- ADMIN USERS TABLE
-- Stores administrator account information
-- ============================================================================
CREATE TABLE admin_users (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'moderator', 'support') DEFAULT 'admin',
    permissions TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- INSERT INITIAL DATA
-- ============================================================================

-- Insert supported languages
INSERT INTO languages (language_code, name, native_name, direction, is_active, is_default) VALUES
('en', 'English', 'English', 'ltr', TRUE, TRUE),
('fr', 'French', 'Français', 'ltr', TRUE, FALSE),
('ar', 'Arabic', 'العربية', 'rtl', TRUE, FALSE);

-- Insert default service categories
INSERT INTO service_categories (category_name, category_slug, description, display_order, is_active) VALUES
('Cleaning Services', 'cleaning', 'Professional home and office cleaning services', 1, TRUE),
('Gardening & Landscaping', 'gardening', 'Garden maintenance and landscaping services', 2, TRUE),
('Childcare & Babysitting', 'childcare', 'Professional childcare and babysitting services', 3, TRUE),
('Tutoring & Education', 'tutoring', 'Educational tutoring and coaching services', 4, TRUE),
('Pet Care', 'pet-care', 'Pet sitting, walking, and grooming services', 5, TRUE),
('Elderly Care', 'elderly-care', 'Professional elderly and senior care services', 6, TRUE),
('Nursing & Medical', 'nursing', 'Home nursing and medical care services', 7, TRUE),
('Home Repairs', 'home-repairs', 'General home repair and maintenance services', 8, TRUE),
('Beauty & Wellness', 'beauty-wellness', 'At-home beauty and wellness services', 9, TRUE),
('Moving & Delivery', 'moving-delivery', 'Moving and delivery assistance services', 10, TRUE);

-- Insert category translations (English default)
INSERT INTO service_category_translations (category_id, language_code, translated_name, translated_description) VALUES
(1, 'en', 'Cleaning Services', 'Professional home and office cleaning services'),
(2, 'en', 'Gardening & Landscaping', 'Garden maintenance and landscaping services'),
(3, 'en', 'Childcare & Babysitting', 'Professional childcare and babysitting services'),
(4, 'en', 'Tutoring & Education', 'Educational tutoring and coaching services'),
(5, 'en', 'Pet Care', 'Pet sitting, walking, and grooming services'),
(6, 'en', 'Elderly Care', 'Professional elderly and senior care services'),
(7, 'en', 'Nursing & Medical', 'Home nursing and medical care services'),
(8, 'en', 'Home Repairs', 'General home repair and maintenance services'),
(9, 'en', 'Beauty & Wellness', 'At-home beauty and wellness services'),
(10, 'en', 'Moving & Delivery', 'Moving and delivery assistance services');

-- Insert sample services
INSERT INTO services (category_id, service_name, service_slug, description, duration_minutes, base_price, price_type, is_popular, is_active) VALUES
-- Cleaning Services
(1, 'House Cleaning', 'house-cleaning', 'Complete house cleaning service', 120, 150.00, 'fixed', TRUE, TRUE),
(1, 'Deep Cleaning', 'deep-cleaning', 'Thorough deep cleaning service', 180, 250.00, 'fixed', TRUE, TRUE),
(1, 'Office Cleaning', 'office-cleaning', 'Professional office cleaning', 90, 200.00, 'fixed', FALSE, TRUE),

-- Gardening
(2, 'Lawn Mowing', 'lawn-mowing', 'Professional lawn mowing service', 60, 100.00, 'fixed', TRUE, TRUE),
(2, 'Garden Maintenance', 'garden-maintenance', 'Complete garden care and maintenance', 120, 180.00, 'hourly', TRUE, TRUE),
(2, 'Tree Trimming', 'tree-trimming', 'Professional tree trimming service', 90, 150.00, 'hourly', FALSE, TRUE),

-- Childcare
(3, 'Babysitting', 'babysitting', 'Professional babysitting services', 240, 80.00, 'hourly', TRUE, TRUE),
(3, 'After School Care', 'after-school-care', 'After school childcare', 180, 120.00, 'hourly', TRUE, TRUE),

-- Tutoring
(4, 'Math Tutoring', 'math-tutoring', 'Professional math tutoring', 60, 100.00, 'hourly', TRUE, TRUE),
(4, 'Language Tutoring', 'language-tutoring', 'Language learning tutoring', 60, 90.00, 'hourly', TRUE, TRUE),

-- Pet Care
(5, 'Dog Walking', 'dog-walking', 'Professional dog walking service', 30, 50.00, 'fixed', TRUE, TRUE),
(5, 'Pet Sitting', 'pet-sitting', 'In-home pet sitting service', 240, 120.00, 'hourly', TRUE, TRUE),

-- Elderly Care
(6, 'Companion Care', 'companion-care', 'Elderly companion care service', 240, 150.00, 'hourly', TRUE, TRUE),
(6, 'Personal Care Assistant', 'personal-care-assistant', 'Personal care assistance', 120, 120.00, 'hourly', TRUE, TRUE),

-- Nursing
(7, 'Home Nursing', 'home-nursing', 'Professional home nursing care', 120, 200.00, 'hourly', TRUE, TRUE),
(7, 'Medication Management', 'medication-management', 'Medication administration and monitoring', 60, 100.00, 'fixed', FALSE, TRUE);

-- Insert service translations (English default)
INSERT INTO service_translations (service_id, language_code, translated_name, translated_description) VALUES
(1, 'en', 'House Cleaning', 'Complete house cleaning service'),
(2, 'en', 'Deep Cleaning', 'Thorough deep cleaning service'),
(3, 'en', 'Office Cleaning', 'Professional office cleaning'),
(4, 'en', 'Lawn Mowing', 'Professional lawn mowing service'),
(5, 'en', 'Garden Maintenance', 'Complete garden care and maintenance'),
(6, 'en', 'Tree Trimming', 'Professional tree trimming service'),
(7, 'en', 'Babysitting', 'Professional babysitting services'),
(8, 'en', 'After School Care', 'After school childcare'),
(9, 'en', 'Math Tutoring', 'Professional math tutoring'),
(10, 'en', 'Language Tutoring', 'Language learning tutoring'),
(11, 'en', 'Dog Walking', 'Professional dog walking service'),
(12, 'en', 'Pet Sitting', 'In-home pet sitting service'),
(13, 'en', 'Companion Care', 'Elderly companion care service'),
(14, 'en', 'Personal Care Assistant', 'Personal care assistance'),
(15, 'en', 'Home Nursing', 'Professional home nursing care'),
(16, 'en', 'Medication Management', 'Medication administration and monitoring');

-- Insert CMS pages
INSERT INTO content_pages (page_slug, is_active) VALUES
('about', TRUE),
('how-it-works', TRUE),
('faq', TRUE),
('terms', TRUE),
('privacy', TRUE),
('contact', TRUE),
('help', TRUE);

-- Insert CMS page translations (English default placeholders)
INSERT INTO content_page_translations (page_id, language_code, page_title, page_content, meta_title, meta_description) VALUES
(1, 'en', 'About Us', 'About Moueene content goes here.', 'About Moueene', 'Learn more about Moueene.'),
(2, 'en', 'How It Works', 'How Moueene works content goes here.', 'How Moueene Works', 'Discover how to book services on Moueene.'),
(3, 'en', 'FAQ', 'Frequently asked questions content goes here.', 'Moueene FAQ', 'Answers to common questions.'),
(4, 'en', 'Terms & Conditions', 'Terms and conditions content goes here.', 'Terms & Conditions', 'Read Moueene terms and conditions.'),
(5, 'en', 'Privacy Policy', 'Privacy policy content goes here.', 'Privacy Policy', 'Read Moueene privacy policy.'),
(6, 'en', 'Contact Us', 'Contact information content goes here.', 'Contact Moueene', 'Get in touch with Moueene support.'),
(7, 'en', 'Help Center', 'Help center content goes here.', 'Moueene Help Center', 'Find help and support resources.');

-- Insert default admin user (password: Admin@123456)
-- Note: This is a hashed password - CHANGE IN PRODUCTION
INSERT INTO admin_users (username, email, password_hash, full_name, role, is_active) VALUES
('admin', 'admin@moueene.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'super_admin', TRUE);

-- ============================================================================
-- TRIGGERS
-- ============================================================================

-- Trigger to update provider average rating after review insert
DELIMITER //
CREATE TRIGGER update_provider_rating_after_insert
AFTER INSERT ON reviews
FOR EACH ROW
BEGIN
    UPDATE providers
    SET average_rating = (
        SELECT AVG(rating)
        FROM reviews
        WHERE provider_id = NEW.provider_id AND is_visible = TRUE
    ),
    total_reviews = (
        SELECT COUNT(*)
        FROM reviews
        WHERE provider_id = NEW.provider_id AND is_visible = TRUE
    )
    WHERE provider_id = NEW.provider_id;
END//

-- Trigger to update provider rating after review update
CREATE TRIGGER update_provider_rating_after_update
AFTER UPDATE ON reviews
FOR EACH ROW
BEGIN
    UPDATE providers
    SET average_rating = (
        SELECT AVG(rating)
        FROM reviews
        WHERE provider_id = NEW.provider_id AND is_visible = TRUE
    ),
    total_reviews = (
        SELECT COUNT(*)
        FROM reviews
        WHERE provider_id = NEW.provider_id AND is_visible = TRUE
    )
    WHERE provider_id = NEW.provider_id;
END//

-- Trigger to update provider rating after review delete
CREATE TRIGGER update_provider_rating_after_delete
AFTER DELETE ON reviews
FOR EACH ROW
BEGIN
    UPDATE providers
    SET average_rating = COALESCE((
        SELECT AVG(rating)
        FROM reviews
        WHERE provider_id = OLD.provider_id AND is_visible = TRUE
    ), 0),
    total_reviews = (
        SELECT COUNT(*)
        FROM reviews
        WHERE provider_id = OLD.provider_id AND is_visible = TRUE
    )
    WHERE provider_id = OLD.provider_id;
END//

-- Trigger to update service average rating
CREATE TRIGGER update_service_rating_after_review
AFTER INSERT ON reviews
FOR EACH ROW
BEGIN
    UPDATE services
    SET average_rating = (
        SELECT AVG(rating)
        FROM reviews
        WHERE service_id = NEW.service_id AND is_visible = TRUE
    )
    WHERE service_id = NEW.service_id;
END//

-- Trigger to generate booking reference
CREATE TRIGGER generate_booking_reference
BEFORE INSERT ON bookings
FOR EACH ROW
BEGIN
    IF NEW.booking_reference IS NULL OR NEW.booking_reference = '' THEN
        SET NEW.booking_reference = CONCAT('BK', DATE_FORMAT(NOW(), '%Y%m%d'), LPAD(FLOOR(RAND() * 9999), 4, '0'));
    END IF;
END//

-- Trigger to log booking status changes
CREATE TRIGGER log_booking_status_change
AFTER UPDATE ON bookings
FOR EACH ROW
BEGIN
    IF OLD.booking_status != NEW.booking_status THEN
        INSERT INTO booking_history (booking_id, previous_status, new_status, changed_by_type, notes)
        VALUES (NEW.booking_id, OLD.booking_status, NEW.booking_status, 'system', 'Status updated automatically');
    END IF;
END//

DELIMITER ;

-- ============================================================================
-- VIEWS
-- ============================================================================

-- View for active bookings with details
CREATE VIEW active_bookings_view AS
SELECT 
    b.booking_id,
    b.booking_reference,
    b.booking_date,
    b.booking_time,
    b.booking_status,
    b.payment_status,
    b.final_price,
    CONCAT(u.first_name, ' ', u.last_name) AS customer_name,
    u.email AS customer_email,
    u.phone AS customer_phone,
    CONCAT(p.first_name, ' ', p.last_name) AS provider_name,
    p.email AS provider_email,
    p.phone AS provider_phone,
    s.service_name,
    sc.category_name,
    b.created_at
FROM bookings b
JOIN users u ON b.user_id = u.user_id
JOIN providers p ON b.provider_id = p.provider_id
JOIN services s ON b.service_id = s.service_id
JOIN service_categories sc ON s.category_id = sc.category_id
WHERE b.booking_status IN ('pending', 'confirmed', 'in_progress');

-- View for provider statistics
CREATE VIEW provider_stats_view AS
SELECT 
    p.provider_id,
    CONCAT(p.first_name, ' ', p.last_name) AS provider_name,
    p.email,
    p.phone,
    p.city,
    p.average_rating,
    p.total_reviews,
    p.total_bookings,
    p.completed_bookings,
    p.verification_status,
    p.account_status,
    COUNT(DISTINCT ps.service_id) AS services_offered,
    p.created_at
FROM providers p
LEFT JOIN provider_services ps ON p.provider_id = ps.provider_id
GROUP BY p.provider_id;

-- ============================================================================
-- END OF SCHEMA
-- ============================================================================
