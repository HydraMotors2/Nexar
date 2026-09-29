-- =====================================================
-- NEXAR - Complete Database Schema
-- =====================================================
-- A platform connecting clients and service providers
-- =====================================================


-- =====================================================
-- DATABASE CREATION
-- =====================================================

-- =====================================================
-- TABLE: users
-- Core user accounts for both clients and providers
-- =====================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `uuid` CHAR(36) NOT NULL UNIQUE,
    `account_type` TEXT NOT NULL DEFAULT 'entrepreneur' CHECK (`account_type` IN ('entrepreneur', 'supplier', 'admin')),
    `role` TEXT NOT NULL DEFAULT 'client' CHECK (`role` IN ('client', 'provider', 'admin')),
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `avatar` VARCHAR(255) NULL DEFAULT NULL,
    `phone` VARCHAR(20) NULL DEFAULT NULL,
    `country` CHAR(2) NULL DEFAULT NULL,
    `city` VARCHAR(100) NULL DEFAULT NULL,
    `timezone` VARCHAR(50) NULL DEFAULT 'UTC',
    `locale` VARCHAR(10) NULL DEFAULT 'en',
    `status` TEXT NOT NULL DEFAULT 'active' CHECK (`status` IN ('active', 'inactive', 'banned', 'pending')),
    `last_login_at` TIMESTAMP NULL DEFAULT NULL,
    `last_login_ip` VARCHAR(45) NULL DEFAULT NULL,
    `settings` TEXT NULL,
    `metadata` TEXT NULL,
    `remember_token` VARCHAR(100) NULL DEFAULT NULL,
    `email_verification_token` VARCHAR(100) NULL DEFAULT NULL,
    `password_reset_token` VARCHAR(100) NULL DEFAULT NULL,
    `password_reset_expires` TIMESTAMP NULL DEFAULT NULL,
    `failed_login_attempts` INTEGER NOT NULL DEFAULT 0,
    `locked_until` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL
    
    
);
CREATE INDEX IF NOT EXISTS `idx_users_email` ON `users` (`email`);
CREATE INDEX IF NOT EXISTS `idx_users_account_type` ON `users` (`account_type`);
CREATE INDEX IF NOT EXISTS `idx_users_role` ON `users` (`role`);
CREATE INDEX IF NOT EXISTS `idx_users_status` ON `users` (`status`);
CREATE INDEX IF NOT EXISTS `idx_users_uuid` ON `users` (`uuid`);
CREATE INDEX IF NOT EXISTS `idx_users_deleted_at` ON `users` (`deleted_at`);

-- =====================================================
-- TABLE: email_verifications
-- Tracks email verification tokens for newly registered users
-- =====================================================
CREATE TABLE IF NOT EXISTS `email_verifications` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `user_id` INTEGER NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `expires_at` TIMESTAMP NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS `idx_email_verifications_user_id` ON `email_verifications` (`user_id`);
CREATE INDEX IF NOT EXISTS `idx_email_verifications_token` ON `email_verifications` (`token`);
CREATE INDEX IF NOT EXISTS `idx_email_verifications_expires_at` ON `email_verifications` (`expires_at`);

-- =====================================================
-- TABLE: remember_tokens
-- Secure persistent login tokens for "remember me" sessions
-- =====================================================
CREATE TABLE IF NOT EXISTS `remember_tokens` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `user_id` INTEGER NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `expires_at` TIMESTAMP NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS `idx_remember_tokens_user_id` ON `remember_tokens` (`user_id`);
CREATE INDEX IF NOT EXISTS `idx_remember_tokens_token` ON `remember_tokens` (`token`);
CREATE INDEX IF NOT EXISTS `idx_remember_tokens_expires_at` ON `remember_tokens` (`expires_at`);

-- =====================================================
-- TABLE: entrepreneurs
-- Marketplace buyer profiles for micro and small businesses
-- =====================================================
CREATE TABLE IF NOT EXISTS `entrepreneurs` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `user_id` INTEGER NOT NULL,
    `cnpj` VARCHAR(18) NULL DEFAULT NULL,
    `legal_name` VARCHAR(255) NULL DEFAULT NULL,
    `trade_name` VARCHAR(255) NULL DEFAULT NULL,
    `phone` VARCHAR(20) NULL DEFAULT NULL,
    `business_segment` VARCHAR(150) NULL DEFAULT NULL,
    `city` VARCHAR(100) NULL DEFAULT NULL,
    `state` VARCHAR(2) NULL DEFAULT NULL,
    `company_photo` BLOB NULL,
    `employees_range` VARCHAR(50) NULL DEFAULT NULL,
    `revenue_range` VARCHAR(50) NULL DEFAULT NULL,
    `interested_categories` TEXT NULL,
    `products_purchased` TEXT NULL,
    `purchase_frequency` VARCHAR(100) NULL DEFAULT NULL,
    `plan` TEXT NOT NULL DEFAULT 'free' CHECK (`plan` IN ('free','starter','growth')),
    `payment_status` TEXT NOT NULL DEFAULT 'trial' CHECK (`payment_status` IN ('trial','pending','paid','canceled')),
    `payment_mode` TEXT NOT NULL DEFAULT 'manual_test' CHECK (`payment_mode` IN ('manual_test','gateway','trial')),
    `metadata` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    
);
CREATE INDEX IF NOT EXISTS `idx_entrepreneurs_user_id` ON `entrepreneurs` (`user_id`);
CREATE INDEX IF NOT EXISTS `idx_entrepreneurs_cnpj` ON `entrepreneurs` (`cnpj`);
CREATE INDEX IF NOT EXISTS `idx_entrepreneurs_state` ON `entrepreneurs` (`state`);

-- =====================================================
-- TABLE: suppliers
-- Marketplace supplier profiles for businesses selling to companies
-- =====================================================
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `user_id` INTEGER NOT NULL,
    `cnpj` VARCHAR(18) NULL DEFAULT NULL,
    `legal_name` VARCHAR(255) NULL DEFAULT NULL,
    `trade_name` VARCHAR(255) NULL DEFAULT NULL,
    `phone` VARCHAR(20) NULL DEFAULT NULL,
    `city` VARCHAR(100) NULL DEFAULT NULL,
    `state` VARCHAR(2) NULL DEFAULT NULL,
    `business_segment` VARCHAR(150) NULL DEFAULT NULL,
    `logo` BLOB NULL,
    `cover_image` BLOB NULL,
    `description` TEXT NULL,
    `category` VARCHAR(150) NULL DEFAULT NULL,
    `main_products` TEXT NULL,
    `service_region` VARCHAR(150) NULL DEFAULT NULL,
    `website` VARCHAR(255) NULL DEFAULT NULL,
    `whatsapp` VARCHAR(50) NULL DEFAULT NULL,
    `plan` TEXT NOT NULL DEFAULT 'account' CHECK (`plan` IN ('account','boost','promoted')),
    `payment_status` TEXT NOT NULL DEFAULT 'trial' CHECK (`payment_status` IN ('trial','pending','paid','canceled')),
    `payment_mode` TEXT NOT NULL DEFAULT 'manual_test' CHECK (`payment_mode` IN ('manual_test','gateway','trial')),
    `metadata` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    
);
CREATE INDEX IF NOT EXISTS `idx_suppliers_user_id` ON `suppliers` (`user_id`);
CREATE INDEX IF NOT EXISTS `idx_suppliers_cnpj` ON `suppliers` (`cnpj`);
CREATE INDEX IF NOT EXISTS `idx_suppliers_state` ON `suppliers` (`state`);
CREATE INDEX IF NOT EXISTS `idx_suppliers_category` ON `suppliers` (`category`);

-- =====================================================
-- TABLE: companies
-- Provider company profiles
-- =====================================================
CREATE TABLE IF NOT EXISTS `companies` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `user_id` INTEGER NOT NULL,
    `slug` VARCHAR(150) NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `tagline` VARCHAR(255) NULL DEFAULT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `logo` VARCHAR(255) NULL DEFAULT NULL,
    `banner` VARCHAR(255) NULL DEFAULT NULL,
    `website` VARCHAR(255) NULL DEFAULT NULL,
    `email` VARCHAR(255) NULL DEFAULT NULL,
    `phone` VARCHAR(20) NULL DEFAULT NULL,
    `address` VARCHAR(255) NULL DEFAULT NULL,
    `city` VARCHAR(100) NULL DEFAULT NULL,
    `state` VARCHAR(100) NULL DEFAULT NULL,
    `country` CHAR(2) NULL DEFAULT NULL,
    `postal_code` VARCHAR(20) NULL DEFAULT NULL,
    `latitude` NUMERIC NULL DEFAULT NULL,
    `longitude` NUMERIC NULL DEFAULT NULL,
    `timezone` VARCHAR(50) NULL DEFAULT NULL,
    `hourly_rate_min` NUMERIC NULL DEFAULT NULL,
    `hourly_rate_max` NUMERIC NULL DEFAULT NULL,
    `project_min_budget` NUMERIC NULL DEFAULT NULL,
    `project_max_budget` NUMERIC NULL DEFAULT NULL,
    `social_links` TEXT NULL,
    `skills` TEXT NULL,
    `certifications` TEXT NULL,
    `awards` TEXT NULL,
    `verified` INTEGER NOT NULL DEFAULT 0,
    `featured` INTEGER NOT NULL DEFAULT 0,
    `response_time` INTEGER NULL DEFAULT NULL,
    `response_rate` INTEGER NULL DEFAULT NULL,
    `completion_rate` INTEGER NULL DEFAULT NULL,
    `total_projects` INTEGER NOT NULL DEFAULT 0,
    `total_reviews` INTEGER NOT NULL DEFAULT 0,
    `average_rating` NUMERIC NULL DEFAULT NULL,
    `status` TEXT NOT NULL DEFAULT 'pending' CHECK (`status` IN ('active', 'inactive', 'pending', 'suspended')),
    `metadata` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    
);
CREATE INDEX IF NOT EXISTS `idx_companies_slug` ON `companies` (`slug`);
CREATE INDEX IF NOT EXISTS `idx_companies_user_id` ON `companies` (`user_id`);
CREATE INDEX IF NOT EXISTS `idx_companies_status` ON `companies` (`status`);
CREATE INDEX IF NOT EXISTS `idx_companies_verified` ON `companies` (`verified`);
CREATE INDEX IF NOT EXISTS `idx_companies_featured` ON `companies` (`featured`);
CREATE INDEX IF NOT EXISTS `idx_companies_country` ON `companies` (`country`);
CREATE INDEX IF NOT EXISTS `idx_companies_city` ON `companies` (`city`);
CREATE INDEX IF NOT EXISTS `idx_companies_rating` ON `companies` (`average_rating`);
CREATE INDEX IF NOT EXISTS `idx_companies_deleted_at` ON `companies` (`deleted_at`);

-- =====================================================
-- TABLE: categories
-- Service categories and subcategories
-- =====================================================
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `parent_id` INTEGER NULL DEFAULT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `icon` VARCHAR(50) NULL DEFAULT NULL,
    `color` CHAR(7) NULL DEFAULT NULL,
    `sort_order` INTEGER NOT NULL DEFAULT 0,
    `is_active` INTEGER NOT NULL DEFAULT 1,
    `metadata` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
    
);
CREATE INDEX IF NOT EXISTS `idx_categories_parent_id` ON `categories` (`parent_id`);
CREATE INDEX IF NOT EXISTS `idx_categories_slug` ON `categories` (`slug`);
CREATE INDEX IF NOT EXISTS `idx_categories_is_active` ON `categories` (`is_active`);
CREATE INDEX IF NOT EXISTS `idx_categories_sort_order` ON `categories` (`sort_order`);

-- =====================================================
-- TABLE: company_categories
-- Pivot table for company-category relationships
-- =====================================================
CREATE TABLE IF NOT EXISTS `company_categories` (
    `company_id` INTEGER NOT NULL,
    `category_id` INTEGER NOT NULL,
    `is_primary` INTEGER NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE,
    
    PRIMARY KEY (`company_id`, `category_id`)
);
CREATE INDEX IF NOT EXISTS `idx_company_categories_company` ON `company_categories` (`company_id`);
CREATE INDEX IF NOT EXISTS `idx_company_categories_category` ON `company_categories` (`category_id`);

-- =====================================================
-- TABLE: services
-- Services offered by companies
-- =====================================================
CREATE TABLE IF NOT EXISTS `services` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `company_id` INTEGER NOT NULL,
    `category_id` INTEGER NULL DEFAULT NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `price_min` NUMERIC NULL DEFAULT NULL,
    `price_max` NUMERIC NULL DEFAULT NULL,
    `price_type` TEXT NOT NULL DEFAULT 'fixed' CHECK (`price_type` IN ('fixed', 'hourly', 'project')),
    `duration_min` INTEGER NULL DEFAULT NULL,
    `duration_max` INTEGER NULL DEFAULT NULL,
    `duration_unit` TEXT NULL DEFAULT NULL CHECK (`duration_unit` IN ('hours', 'days', 'weeks', 'months')),
    `features` TEXT NULL,
    `is_active` INTEGER NOT NULL DEFAULT 1,
    `sort_order` INTEGER NOT NULL DEFAULT 0,
    `metadata` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
    
);
CREATE INDEX IF NOT EXISTS `idx_services_company_id` ON `services` (`company_id`);
CREATE INDEX IF NOT EXISTS `idx_services_category_id` ON `services` (`category_id`);
CREATE INDEX IF NOT EXISTS `idx_services_slug` ON `services` (`slug`);
CREATE INDEX IF NOT EXISTS `idx_services_is_active` ON `services` (`is_active`);
CREATE INDEX IF NOT EXISTS `idx_services_deleted_at` ON `services` (`deleted_at`);

-- =====================================================
-- TABLE: portfolios
-- Portfolio items for companies
-- =====================================================
CREATE TABLE IF NOT EXISTS `portfolios` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `company_id` INTEGER NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `cover_image` VARCHAR(255) NULL DEFAULT NULL,
    `project_url` VARCHAR(500) NULL DEFAULT NULL,
    `client_name` VARCHAR(255) NULL DEFAULT NULL,
    `start_date` DATE NULL DEFAULT NULL,
    `end_date` DATE NULL DEFAULT NULL,
    `budget` NUMERIC NULL DEFAULT NULL,
    `technologies` TEXT NULL,
    `tags` TEXT NULL,
    `is_featured` INTEGER NOT NULL DEFAULT 0,
    `is_active` INTEGER NOT NULL DEFAULT 1,
    `sort_order` INTEGER NOT NULL DEFAULT 0,
    `views` INTEGER NOT NULL DEFAULT 0,
    `metadata` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE
    
);
CREATE INDEX IF NOT EXISTS `idx_portfolios_company_id` ON `portfolios` (`company_id`);
CREATE INDEX IF NOT EXISTS `idx_portfolios_slug` ON `portfolios` (`slug`);
CREATE INDEX IF NOT EXISTS `idx_portfolios_is_featured` ON `portfolios` (`is_featured`);
CREATE INDEX IF NOT EXISTS `idx_portfolios_is_active` ON `portfolios` (`is_active`);
CREATE INDEX IF NOT EXISTS `idx_portfolios_deleted_at` ON `portfolios` (`deleted_at`);

-- =====================================================
-- TABLE: portfolio_images
-- Images for portfolio items
-- =====================================================
CREATE TABLE IF NOT EXISTS `portfolio_images` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `portfolio_id` INTEGER NOT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `alt_text` VARCHAR(255) NULL DEFAULT NULL,
    `mime_type` VARCHAR(50) NULL DEFAULT NULL,
    `size` INTEGER NULL DEFAULT NULL,
    `width` INTEGER NULL DEFAULT NULL,
    `height` INTEGER NULL DEFAULT NULL,
    `sort_order` INTEGER NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`portfolio_id`) REFERENCES `portfolios`(`id`) ON DELETE CASCADE
    
);
CREATE INDEX IF NOT EXISTS `idx_portfolio_images_portfolio_id` ON `portfolio_images` (`portfolio_id`);

-- =====================================================
-- TABLE: reviews
-- Reviews and ratings for companies
-- =====================================================
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `company_id` INTEGER NOT NULL,
    `user_id` INTEGER NOT NULL,
    `project_id` INTEGER NULL DEFAULT NULL,
    `rating` INTEGER NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
    `title` VARCHAR(255) NULL DEFAULT NULL,
    `comment` TEXT NULL DEFAULT NULL,
    `pros` TEXT NULL DEFAULT NULL,
    `cons` TEXT NULL DEFAULT NULL,
    `communication_rating` INTEGER NULL DEFAULT NULL,
    `quality_rating` INTEGER NULL DEFAULT NULL,
    `value_rating` INTEGER NULL DEFAULT NULL,
    `would_recommend` INTEGER NULL DEFAULT NULL,
    `is_verified_purchase` INTEGER NOT NULL DEFAULT 0,
    `is_approved` INTEGER NOT NULL DEFAULT 0,
    `helpful_count` INTEGER NOT NULL DEFAULT 0,
    `admin_response` TEXT NULL DEFAULT NULL,
    `admin_response_at` TIMESTAMP NULL DEFAULT NULL,
    `metadata` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    
);
CREATE INDEX IF NOT EXISTS `idx_reviews_company_id` ON `reviews` (`company_id`);
CREATE INDEX IF NOT EXISTS `idx_reviews_user_id` ON `reviews` (`user_id`);
CREATE INDEX IF NOT EXISTS `idx_reviews_rating` ON `reviews` (`rating`);
CREATE INDEX IF NOT EXISTS `idx_reviews_is_approved` ON `reviews` (`is_approved`);
CREATE INDEX IF NOT EXISTS `idx_reviews_deleted_at` ON `reviews` (`deleted_at`);

-- =====================================================
-- TABLE: review_images
-- Images attached to reviews
-- =====================================================
CREATE TABLE IF NOT EXISTS `review_images` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `review_id` INTEGER NOT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `alt_text` VARCHAR(255) NULL DEFAULT NULL,
    `mime_type` VARCHAR(50) NULL DEFAULT NULL,
    `size` INTEGER NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`review_id`) REFERENCES `reviews`(`id`) ON DELETE CASCADE
    
);
CREATE INDEX IF NOT EXISTS `idx_review_images_review_id` ON `review_images` (`review_id`);

-- =====================================================
-- TABLE: favorites
-- Companies favorited by users
-- =====================================================
CREATE TABLE IF NOT EXISTS `favorites` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `user_id` INTEGER NOT NULL,
    `company_id` INTEGER NOT NULL,
    `note` VARCHAR(255) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    
    UNIQUE (`user_id`, `company_id`)
);
CREATE INDEX IF NOT EXISTS `idx_favorites_user_id` ON `favorites` (`user_id`);
CREATE INDEX IF NOT EXISTS `idx_favorites_company_id` ON `favorites` (`company_id`);

-- =====================================================
-- TABLE: messages
-- Messaging system between users and companies
-- =====================================================
CREATE TABLE IF NOT EXISTS `messages` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `conversation_id` CHAR(36) NOT NULL,
    `sender_id` INTEGER NOT NULL,
    `recipient_id` INTEGER NOT NULL,
    `company_id` INTEGER NULL DEFAULT NULL,
    `parent_id` INTEGER NULL DEFAULT NULL,
    `subject` VARCHAR(255) NULL DEFAULT NULL,
    `body` TEXT NOT NULL,
    `attachments` TEXT NULL,
    `is_read` INTEGER NOT NULL DEFAULT 0,
    `read_at` TIMESTAMP NULL DEFAULT NULL,
    `is_deleted_sender` INTEGER NOT NULL DEFAULT 0,
    `is_deleted_recipient` INTEGER NOT NULL DEFAULT 0,
    `metadata` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`recipient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`parent_id`) REFERENCES `messages`(`id`) ON DELETE CASCADE
    
);
CREATE INDEX IF NOT EXISTS `idx_messages_conversation_id` ON `messages` (`conversation_id`);
CREATE INDEX IF NOT EXISTS `idx_messages_sender_id` ON `messages` (`sender_id`);
CREATE INDEX IF NOT EXISTS `idx_messages_recipient_id` ON `messages` (`recipient_id`);
CREATE INDEX IF NOT EXISTS `idx_messages_company_id` ON `messages` (`company_id`);
CREATE INDEX IF NOT EXISTS `idx_messages_parent_id` ON `messages` (`parent_id`);
CREATE INDEX IF NOT EXISTS `idx_messages_is_read` ON `messages` (`is_read`);
CREATE INDEX IF NOT EXISTS `idx_messages_created_at` ON `messages` (`created_at`);

-- =====================================================
-- TABLE: projects
-- Projects posted by clients
-- =====================================================
CREATE TABLE IF NOT EXISTS `projects` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `user_id` INTEGER NOT NULL,
    `category_id` INTEGER NULL DEFAULT NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `budget_min` NUMERIC NULL DEFAULT NULL,
    `budget_max` NUMERIC NULL DEFAULT NULL,
    `budget_type` TEXT NOT NULL DEFAULT 'fixed' CHECK (`budget_type` IN ('fixed', 'hourly')),
    `deadline` DATE NULL DEFAULT NULL,
    `duration_estimate` INTEGER NULL DEFAULT NULL,
    `duration_unit` TEXT NULL DEFAULT NULL CHECK (`duration_unit` IN ('hours', 'days', 'weeks', 'months')),
    `skills_required` TEXT NULL,
    `attachments` TEXT NULL,
    `status` TEXT NOT NULL DEFAULT 'open' CHECK (`status` IN ('open', 'in_progress', 'completed', 'cancelled', 'archived')),
    `visibility` TEXT NOT NULL DEFAULT 'public' CHECK (`visibility` IN ('public', 'private', 'invited')),
    `featured` INTEGER NOT NULL DEFAULT 0,
    `urgent` INTEGER NOT NULL DEFAULT 0,
    `views` INTEGER NOT NULL DEFAULT 0,
    `proposals_count` INTEGER NOT NULL DEFAULT 0,
    `metadata` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
    
);
CREATE INDEX IF NOT EXISTS `idx_projects_user_id` ON `projects` (`user_id`);
CREATE INDEX IF NOT EXISTS `idx_projects_category_id` ON `projects` (`category_id`);
CREATE INDEX IF NOT EXISTS `idx_projects_slug` ON `projects` (`slug`);
CREATE INDEX IF NOT EXISTS `idx_projects_status` ON `projects` (`status`);
CREATE INDEX IF NOT EXISTS `idx_projects_featured` ON `projects` (`featured`);
CREATE INDEX IF NOT EXISTS `idx_projects_deleted_at` ON `projects` (`deleted_at`);

-- =====================================================
-- TABLE: proposals
-- Proposals submitted by providers for projects
-- =====================================================
CREATE TABLE IF NOT EXISTS `proposals` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `project_id` INTEGER NOT NULL,
    `company_id` INTEGER NOT NULL,
    `user_id` INTEGER NOT NULL,
    `cover_letter` TEXT NOT NULL,
    `bid_amount` NUMERIC NOT NULL,
    `bid_duration` INTEGER NULL DEFAULT NULL,
    `bid_duration_unit` TEXT NULL DEFAULT NULL CHECK (`bid_duration_unit` IN ('hours', 'days', 'weeks', 'months')),
    `attachments` TEXT NULL,
    `status` TEXT NOT NULL DEFAULT 'pending' CHECK (`status` IN ('pending', 'accepted', 'rejected', 'withdrawn', 'completed')),
    `client_response` TEXT NULL DEFAULT NULL,
    `client_response_at` TIMESTAMP NULL DEFAULT NULL,
    `metadata` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    
    UNIQUE (`project_id`, `company_id`)
);
CREATE INDEX IF NOT EXISTS `idx_proposals_project_id` ON `proposals` (`project_id`);
CREATE INDEX IF NOT EXISTS `idx_proposals_company_id` ON `proposals` (`company_id`);
CREATE INDEX IF NOT EXISTS `idx_proposals_user_id` ON `proposals` (`user_id`);
CREATE INDEX IF NOT EXISTS `idx_proposals_status` ON `proposals` (`status`);
CREATE INDEX IF NOT EXISTS `idx_proposals_deleted_at` ON `proposals` (`deleted_at`);

-- =====================================================
-- TABLE: notifications
-- User notifications
-- =====================================================
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `user_id` INTEGER NOT NULL,
    `type` VARCHAR(50) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NULL DEFAULT NULL,
    `action_url` VARCHAR(500) NULL DEFAULT NULL,
    `action_label` VARCHAR(50) NULL DEFAULT NULL,
    `data` TEXT NULL,
    `is_read` INTEGER NOT NULL DEFAULT 0,
    `read_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    
);
CREATE INDEX IF NOT EXISTS `idx_notifications_user_id` ON `notifications` (`user_id`);
CREATE INDEX IF NOT EXISTS `idx_notifications_type` ON `notifications` (`type`);
CREATE INDEX IF NOT EXISTS `idx_notifications_is_read` ON `notifications` (`is_read`);
CREATE INDEX IF NOT EXISTS `idx_notifications_created_at` ON `notifications` (`created_at`);

-- =====================================================
-- TABLE: activity_log
-- Audit trail for important actions
-- =====================================================
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `user_id` INTEGER NULL DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `model_type` VARCHAR(100) NULL DEFAULT NULL,
    `model_id` INTEGER NULL DEFAULT NULL,
    `properties` TEXT NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` VARCHAR(255) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
    
);
CREATE INDEX IF NOT EXISTS `idx_activity_log_user_id` ON `activity_log` (`user_id`);
CREATE INDEX IF NOT EXISTS `idx_activity_log_action` ON `activity_log` (`action`);
CREATE INDEX IF NOT EXISTS `idx_activity_log_model` ON `activity_log` (`model_type`, `model_id`);
CREATE INDEX IF NOT EXISTS `idx_activity_log_created_at` ON `activity_log` (`created_at`);

-- =====================================================
-- TABLE: sessions
-- User sessions for remember-me functionality
-- =====================================================
CREATE TABLE IF NOT EXISTS `sessions` (
    `id` VARCHAR(128) NOT NULL,
    `user_id` INTEGER NULL DEFAULT NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` TEXT NULL,
    `payload` TEXT NOT NULL,
    `last_activity` INTEGER NOT NULL,
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    
);
CREATE INDEX IF NOT EXISTS `idx_sessions_user_id` ON `sessions` (`user_id`);
CREATE INDEX IF NOT EXISTS `idx_sessions_last_activity` ON `sessions` (`last_activity`);

-- =====================================================
-- SEED DATA
-- =====================================================

-- Insert admin user
INSERT INTO `users` (`uuid`, `role`, `email`, `email_verified_at`, `password`, `first_name`, `last_name`, `status`, `settings`) VALUES
('550e8400-e29b-41d4-a716-446655440001', 'admin', 'admin@nexar.com', CURRENT_TIMESTAMP, '$2y$12$LVCv6OVvI3dGhM.vFpTt6.9dGhM.vFpTt6.9dGhM.vFpTt6.9dGhM', 'NEXAR', 'Admin', 'active', '{"notifications": {"email": true, "push": true}, "privacy": {"profile": "public"}}'),
('550e8400-e29b-41d4-a716-446655440002', 'client', 'client@example.com', CURRENT_TIMESTAMP, '$2y$12$LVCv6OVvI3dGhM.vFpTt6.9dGhM.vFpTt6.9dGhM.vFpTt6.9dGhM', 'John', 'Doe', 'active', '{"notifications": {"email": true}, "privacy": {"profile": "public"}}'),
('550e8400-e29b-41d4-a716-446655440003', 'provider', 'provider@example.com', CURRENT_TIMESTAMP, '$2y$12$LVCv6OVvI3dGhM.vFpTt6.9dGhM.vFpTt6.9dGhM.vFpTt6.9dGhM', 'Jane', 'Smith', 'active', '{"notifications": {"email": true}, "privacy": {"profile": "public"}}');

-- Insert categories
INSERT INTO `categories` (`slug`, `name`, `description`, `icon`, `color`, `sort_order`, `is_active`) VALUES
('web-development', 'Web Development', 'Custom websites and web applications', 'code', '#FF6B35', 1, 1),
('mobile-development', 'Mobile Development', 'iOS and Android mobile applications', 'smartphone', '#00D4AA', 2, 1),
('ui-ux-design', 'UI/UX Design', 'User interface and experience design', 'palette', '#7C3AED', 3, 1),
('digital-marketing', 'Digital Marketing', 'SEO, SEM, and social media marketing', 'megaphone', '#EC4899', 4, 1),
('cloud-devops', 'Cloud & DevOps', 'Cloud infrastructure and DevOps services', 'cloud', '#3B82F6', 5, 1),
('data-analytics', 'Data & Analytics', 'Data analysis and business intelligence', 'chart', '#10B981', 6, 1),
('blockchain', 'Blockchain', 'Blockchain and cryptocurrency solutions', 'link', '#F59E0B', 7, 1),
('ai-ml', 'AI & Machine Learning', 'Artificial intelligence and ML solutions', 'cpu', '#EF4444', 8, 1);

-- Subcategories for Web Development
INSERT INTO `categories` (`parent_id`, `slug`, `name`, `description`, `icon`, `color`, `sort_order`, `is_active`) VALUES
(1, 'frontend', 'Frontend Development', 'React, Vue, Angular development', 'layout', '#FF6B35', 1, 1),
(1, 'backend', 'Backend Development', 'Node.js, Python, PHP backend', 'server', '#FF6B35', 2, 1),
(1, 'fullstack', 'Full Stack Development', 'End-to-end web development', 'codepen', '#FF6B35', 3, 1),
(1, 'ecommerce', 'E-commerce Development', 'Shopify, WooCommerce, Magento', 'shopping-cart', '#FF6B35', 4, 1);

-- Insert sample company
INSERT INTO `companies` (`user_id`, `slug`, `name`, `tagline`, `description`, `hourly_rate_min`, `hourly_rate_max`, `verified`, `featured`, `total_projects`, `total_reviews`, `average_rating`, `status`, `skills`, `social_links`) VALUES
(3, 'techvision-studios', 'TechVision Studios', 'Award-winning digital agency', 'TechVision Studios is an award-winning digital agency with over 10 years of experience delivering exceptional web and mobile solutions.', 80.00, 150.00, 1, 1, 245, 127, 4.90, 'active', '["React", "Node.js", "Python", "AWS", "UI/UX Design"]', '{"website": "https://techvision.studio", "linkedin": "https://linkedin.com/techvision", "twitter": "https://twitter.com/techvision"}');

-- Link company to categories
INSERT INTO `company_categories` (`company_id`, `category_id`, `is_primary`) VALUES
(1, 1, 1),
(1, 2, 0),
(1, 3, 0);

-- Insert sample services
INSERT INTO `services` (`company_id`, `category_id`, `title`, `slug`, `description`, `price_min`, `price_max`, `price_type`, `is_active`) VALUES
(1, 1, 'Custom Web Development', 'custom-web-development', 'Tailored websites and web applications built with modern technologies.', 5000.00, 50000.00, 'project', 1),
(1, 2, 'Mobile App Development', 'mobile-app-development', 'Native and cross-platform mobile applications for iOS and Android.', 10000.00, 100000.00, 'project', 1),
(1, 3, 'UI/UX Design', 'ui-ux-design', 'User-centered design that creates intuitive and engaging experiences.', 3000.00, 15000.00, 'project', 1);

-- Insert sample portfolio
INSERT INTO `portfolios` (`company_id`, `title`, `slug`, `description`, `client_name`, `technologies`, `tags`, `is_featured`, `is_active`) VALUES
(1, 'ShopFlow E-commerce Platform', 'shopflow-ecommerce-platform', 'A complete e-commerce solution with advanced inventory management and AI-powered recommendations.', 'RetailCorp Inc.', '["React", "Node.js", "MongoDB", "AWS"]', '["e-commerce", "retail", "ai"]', 1, 1),
(1, 'FinanceHub Mobile App', 'financehub-mobile-app', 'A secure mobile banking application with biometric authentication and real-time transactions.', 'FinanceHub Ltd.', '["React Native", "TypeScript", "Firebase"]', '["fintech", "mobile", "banking"]', 1, 1);

-- Insert sample review
INSERT INTO `reviews` (`company_id`, `user_id`, `rating`, `title`, `comment`, `communication_rating`, `quality_rating`, `value_rating`, `would_recommend`, `is_verified_purchase`, `is_approved`) VALUES
(1, 2, 5, 'Exceptional work!', 'TechVision Studios exceeded our expectations. They delivered a stunning e-commerce platform that increased our sales by 40%. Their team is professional, responsive, and truly cares about the success of their clients.', 5, 5, 5, 1, 1, 1);
