-- ==========================================================
-- Bharat SEO - Complete Database Schema + Seed Data
-- MySQL 5.7+ / MySQL 8 / MariaDB | InnoDB | utf8mb4
-- ==========================================================
-- Import:  mysql -u USER -p DBNAME < database/database.sql
-- Or use phpMyAdmin > Import.
-- ==========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- ----------------------------------------------------------
-- Roles
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(60) NOT NULL,
  `slug` VARCHAR(60) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roles_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Permissions
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` INT UNSIGNED NOT NULL,
  `module` VARCHAR(80) NOT NULL,
  `can_view` TINYINT(1) NOT NULL DEFAULT 1,
  `can_create` TINYINT(1) NOT NULL DEFAULT 0,
  `can_edit` TINYINT(1) NOT NULL DEFAULT 0,
  `can_delete` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_perm_role` (`role_id`),
  CONSTRAINT `fk_perm_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Admins
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(40) DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `reset_token` VARCHAR(255) DEFAULT NULL,
  `reset_expires` DATETIME DEFAULT NULL,
  `failed_attempts` INT UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` DATETIME DEFAULT NULL,
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admins_email` (`email`),
  KEY `idx_admins_role` (`role_id`),
  CONSTRAINT `fk_admins_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Settings (key/value store for all editable site options)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_name` VARCHAR(60) NOT NULL DEFAULT 'general',
  `setting_key` VARCHAR(120) NOT NULL,
  `setting_value` LONGTEXT DEFAULT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`setting_key`),
  KEY `idx_settings_group` (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Pages (static/CMS pages: about, privacy, terms, etc.)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(200) NOT NULL,
  `content` LONGTEXT DEFAULT NULL,
  `template` VARCHAR(60) NOT NULL DEFAULT 'default',
  `status` ENUM('published','draft') NOT NULL DEFAULT 'published',
  `is_system` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pages_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Page SEO (per-URL SEO settings; polymorphic by page_key)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `page_seo` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_key` VARCHAR(190) NOT NULL,
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` VARCHAR(500) DEFAULT NULL,
  `meta_keywords` VARCHAR(500) DEFAULT NULL,
  `canonical_url` VARCHAR(255) DEFAULT NULL,
  `og_image` VARCHAR(255) DEFAULT NULL,
  `noindex` TINYINT(1) NOT NULL DEFAULT 0,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_page_seo_key` (`page_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Service Categories
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `service_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(120) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_servcat_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Services
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `services` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED DEFAULT NULL,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(200) NOT NULL,
  `icon` VARCHAR(120) DEFAULT NULL,
  `short_description` VARCHAR(500) DEFAULT NULL,
  `overview` LONGTEXT DEFAULT NULL,
  `benefits` LONGTEXT DEFAULT NULL,
  `deliverables` LONGTEXT DEFAULT NULL,
  `process` LONGTEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `banner` VARCHAR(255) DEFAULT NULL,
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` VARCHAR(500) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('published','draft') NOT NULL DEFAULT 'published',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_services_slug` (`slug`),
  KEY `idx_services_cat` (`category_id`),
  CONSTRAINT `fk_services_cat` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Portfolio
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `portfolio` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(200) NOT NULL,
  `category` VARCHAR(120) DEFAULT NULL,
  `client_industry` VARCHAR(120) DEFAULT NULL,
  `services_provided` VARCHAR(255) DEFAULT NULL,
  `result_metric` VARCHAR(255) DEFAULT NULL,
  `description` LONGTEXT DEFAULT NULL,
  `featured_image` VARCHAR(255) DEFAULT NULL,
  `project_url` VARCHAR(255) DEFAULT NULL,
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` VARCHAR(500) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` ENUM('published','draft') NOT NULL DEFAULT 'published',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_portfolio_slug` (`slug`),
  KEY `idx_portfolio_cat` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Portfolio Images (gallery)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `portfolio_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `portfolio_id` INT UNSIGNED NOT NULL,
  `image` VARCHAR(255) NOT NULL,
  `alt_text` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_pimg_portfolio` (`portfolio_id`),
  CONSTRAINT `fk_pimg_portfolio` FOREIGN KEY (`portfolio_id`) REFERENCES `portfolio` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Case Studies
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `case_studies` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(200) NOT NULL,
  `client_name` VARCHAR(150) DEFAULT NULL,
  `industry` VARCHAR(120) DEFAULT NULL,
  `challenge` LONGTEXT DEFAULT NULL,
  `strategy` LONGTEXT DEFAULT NULL,
  `execution` LONGTEXT DEFAULT NULL,
  `results` LONGTEXT DEFAULT NULL,
  `reach_growth` VARCHAR(80) DEFAULT NULL,
  `leads_growth` VARCHAR(80) DEFAULT NULL,
  `revenue_impact` VARCHAR(80) DEFAULT NULL,
  `roi_metric` VARCHAR(80) DEFAULT NULL,
  `before_metric` VARCHAR(120) DEFAULT NULL,
  `after_metric` VARCHAR(120) DEFAULT NULL,
  `testimonial` TEXT DEFAULT NULL,
  `featured_image` VARCHAR(255) DEFAULT NULL,
  `gallery` LONGTEXT DEFAULT NULL,
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` VARCHAR(500) DEFAULT NULL,
  `status` ENUM('published','draft') NOT NULL DEFAULT 'published',
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_casestudy_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Testimonials
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_name` VARCHAR(150) NOT NULL,
  `company` VARCHAR(150) DEFAULT NULL,
  `designation` VARCHAR(150) DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `rating` TINYINT NOT NULL DEFAULT 5,
  `review` TEXT NOT NULL,
  `video_url` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` ENUM('published','draft') NOT NULL DEFAULT 'published',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Team Members
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `team_members` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `designation` VARCHAR(150) DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `social_linkedin` VARCHAR(255) DEFAULT NULL,
  `social_twitter` VARCHAR(255) DEFAULT NULL,
  `social_instagram` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Pricing Packages
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pricing_packages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `price` VARCHAR(60) DEFAULT NULL,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'INR',
  `duration` VARCHAR(60) DEFAULT NULL,
  `description` VARCHAR(500) DEFAULT NULL,
  `features` LONGTEXT DEFAULT NULL,
  `is_popular` TINYINT(1) NOT NULL DEFAULT 0,
  `cta_text` VARCHAR(80) NOT NULL DEFAULT 'Get Started',
  `payment_link` VARCHAR(255) DEFAULT NULL,
  `notes` VARCHAR(500) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` ENUM('published','draft') NOT NULL DEFAULT 'published',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Blog Categories
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(120) NOT NULL,
  `description` VARCHAR(500) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_blogcat_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Blogs
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blogs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED DEFAULT NULL,
  `title` VARCHAR(220) NOT NULL,
  `slug` VARCHAR(220) NOT NULL,
  `excerpt` VARCHAR(500) DEFAULT NULL,
  `content` LONGTEXT DEFAULT NULL,
  `featured_image` VARCHAR(255) DEFAULT NULL,
  `author` VARCHAR(120) NOT NULL DEFAULT 'Bharat SEO Team',
  `reading_time` INT NOT NULL DEFAULT 5,
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` VARCHAR(500) DEFAULT NULL,
  `tags` VARCHAR(500) DEFAULT NULL,
  `views` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` ENUM('published','draft','scheduled') NOT NULL DEFAULT 'published',
  `published_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_blogs_slug` (`slug`),
  KEY `idx_blogs_cat` (`category_id`),
  KEY `idx_blogs_status` (`status`),
  CONSTRAINT `fk_blogs_cat` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Blog Tags
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_tags` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(80) NOT NULL,
  `slug` VARCHAR(80) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_blogtag_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Blog Comments
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_comments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `blog_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `comment` TEXT NOT NULL,
  `status` ENUM('pending','approved','spam') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_comments_blog` (`blog_id`),
  CONSTRAINT `fk_comments_blog` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Blog Views (per-day analytics)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_views` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `blog_id` INT UNSIGNED NOT NULL,
  `view_date` DATE NOT NULL,
  `views` INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_blogview` (`blog_id`,`view_date`),
  CONSTRAINT `fk_blogviews_blog` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- FAQs
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `faqs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `question` VARCHAR(400) NOT NULL,
  `answer` TEXT NOT NULL,
  `category` VARCHAR(120) NOT NULL DEFAULT 'general',
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` ENUM('published','draft') NOT NULL DEFAULT 'published',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_faqs_cat` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Leads (free audit + service/package inquiries)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `leads` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(150) NOT NULL,
  `business_name` VARCHAR(150) DEFAULT NULL,
  `phone` VARCHAR(40) DEFAULT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `website_url` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(120) DEFAULT NULL,
  `service_needed` VARCHAR(150) DEFAULT NULL,
  `budget` VARCHAR(80) DEFAULT NULL,
  `message` TEXT DEFAULT NULL,
  `source` VARCHAR(80) NOT NULL DEFAULT 'website',
  `status` ENUM('new','contacted','follow_up','qualified','won','lost') NOT NULL DEFAULT 'new',
  `assigned_to` INT UNSIGNED DEFAULT NULL,
  `ip_address` VARCHAR(64) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_leads_status` (`status`),
  KEY `idx_leads_assigned` (`assigned_to`),
  CONSTRAINT `fk_leads_assigned` FOREIGN KEY (`assigned_to`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Lead Notes
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lead_notes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lead_id` INT UNSIGNED NOT NULL,
  `admin_id` INT UNSIGNED DEFAULT NULL,
  `note` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notes_lead` (`lead_id`),
  CONSTRAINT `fk_notes_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Audit Requests (kept separate for detailed audit form data)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_requests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lead_id` INT UNSIGNED DEFAULT NULL,
  `website_url` VARCHAR(255) NOT NULL,
  `goals` TEXT DEFAULT NULL,
  `competitors` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_lead` (`lead_id`),
  CONSTRAINT `fk_audit_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Contact Messages
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(40) DEFAULT NULL,
  `subject` VARCHAR(200) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('unread','read','replied') NOT NULL DEFAULT 'unread',
  `ip_address` VARCHAR(64) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_contact_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Newsletters
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `newsletters` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(150) NOT NULL,
  `status` ENUM('subscribed','unsubscribed') NOT NULL DEFAULT 'subscribed',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_newsletter_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Media Library
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `media_library` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_type` VARCHAR(80) DEFAULT NULL,
  `file_size` INT UNSIGNED DEFAULT NULL,
  `alt_text` VARCHAR(255) DEFAULT NULL,
  `uploaded_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Redirects (301 manager)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `redirects` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_path` VARCHAR(255) NOT NULL,
  `target_url` VARCHAR(255) NOT NULL,
  `status_code` INT NOT NULL DEFAULT 301,
  `hits` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_redirect_source` (`source_path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Payments
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `package_id` INT UNSIGNED DEFAULT NULL,
  `customer_name` VARCHAR(150) DEFAULT NULL,
  `customer_email` VARCHAR(150) DEFAULT NULL,
  `customer_phone` VARCHAR(40) DEFAULT NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'INR',
  `gateway` VARCHAR(40) DEFAULT NULL,
  `status` ENUM('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
  `invoice_reference` VARCHAR(120) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_payments_pkg` (`package_id`),
  CONSTRAINT `fk_payments_pkg` FOREIGN KEY (`package_id`) REFERENCES `pricing_packages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Transactions (gateway-level transaction log)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_id` INT UNSIGNED DEFAULT NULL,
  `transaction_id` VARCHAR(150) DEFAULT NULL,
  `gateway` VARCHAR(40) DEFAULT NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `status` VARCHAR(40) DEFAULT NULL,
  `raw_response` LONGTEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_trans_payment` (`payment_id`),
  CONSTRAINT `fk_trans_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Activity Logs
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(120) NOT NULL,
  `details` VARCHAR(500) DEFAULT NULL,
  `ip_address` VARCHAR(64) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_log_admin` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Menus (navigation builder)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `menus` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `location` VARCHAR(40) NOT NULL DEFAULT 'header',
  `title` VARCHAR(120) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `parent_id` INT UNSIGNED DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_button` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `idx_menu_loc` (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Social Links
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `social_links` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `platform` VARCHAR(60) NOT NULL,
  `icon` VARCHAR(60) DEFAULT NULL,
  `url` VARCHAR(255) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Homepage Stats (editable agency statistics)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `agency_stats` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` VARCHAR(120) NOT NULL,
  `value` VARCHAR(60) NOT NULL,
  `suffix` VARCHAR(20) DEFAULT NULL,
  `icon` VARCHAR(60) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Client Logos (trusted by)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `client_logos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `logo` VARCHAR(255) DEFAULT NULL,
  `url` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- SEED DATA
-- ==========================================================

-- Roles
INSERT INTO `roles` (`id`,`name`,`slug`,`description`) VALUES
(1,'Super Admin','super-admin','Full access to everything'),
(2,'Developer / SEO Manager','seo-manager','Manage SEO, services, redirects, technical settings'),
(3,'Content Manager','content-manager','Manage blogs, pages, media, FAQs'),
(4,'Sales Manager','sales-manager','Manage leads, inquiries, packages');

-- Admins (password for all demo accounts: Admin@123)
-- Hash generated with password_hash('Admin@123', PASSWORD_DEFAULT)
INSERT INTO `admins` (`id`,`role_id`,`name`,`email`,`password`,`status`) VALUES
(1,1,'Super Admin','admin@bharatseo.com','$2y$12$pbzx2p3kB6fTKuUoAHyxF.tnHdegvWhbhVlp5GsHJnFtCKJzG8/Su','active'),
(2,2,'SEO Manager','seo@bharatseo.com','$2y$12$pbzx2p3kB6fTKuUoAHyxF.tnHdegvWhbhVlp5GsHJnFtCKJzG8/Su','active'),
(3,3,'Content Manager','content@bharatseo.com','$2y$12$pbzx2p3kB6fTKuUoAHyxF.tnHdegvWhbhVlp5GsHJnFtCKJzG8/Su','active'),
(4,4,'Sales Manager','sales@bharatseo.com','$2y$12$pbzx2p3kB6fTKuUoAHyxF.tnHdegvWhbhVlp5GsHJnFtCKJzG8/Su','active');

-- Settings
INSERT INTO `settings` (`group_name`,`setting_key`,`setting_value`) VALUES
('general','site_name','Bharat SEO'),
('general','tagline','Grow Your Brand. Scale Your Reach.'),
('general','logo',''),
('general','favicon',''),
('general','footer_about','Bharat SEO is a full-service digital marketing agency helping brands grow with data-driven social media marketing, SEO, paid advertising, branding, web design and lead generation.'),
('general','copyright_text','Bharat SEO. All rights reserved.'),
('general','maintenance_mode','0'),
('general','theme_default','dark'),
('general','announcement_enabled','1'),
('general','announcement_text','Get a Free Digital Marketing Audit for Your Business — Limited Slots Available!'),
('general','announcement_link','/free-audit'),
('contact','contact_email','hello@bharatseo.com'),
('contact','contact_phone','+91 90000 00000'),
('contact','whatsapp_number','919000000000'),
('contact','whatsapp_message','Hi Bharat SEO, I would like to know more about your services.'),
('contact','address','123 Business Avenue, Mumbai, Maharashtra, India 400001'),
('contact','map_embed','<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3768.6!2d72.8!3d19.0\" width=\"100%\" height=\"320\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\"></iframe>'),
('contact','booking_url','https://calendly.com/'),
('seo','meta_title','Bharat SEO — Social Media Marketing, SEO & Lead Generation Agency'),
('seo','meta_description','Bharat SEO is a results-driven digital marketing agency offering social media marketing, SEO, paid ads, branding, web design and lead generation to grow your brand.'),
('seo','meta_keywords','social media marketing, SEO agency, digital marketing, paid ads, lead generation, branding, web design'),
('seo','og_image',''),
('seo','google_analytics_id',''),
('seo','google_search_console',''),
('seo','facebook_pixel',''),
('seo','robots_txt',''),
('integrations','custom_head',''),
('integrations','custom_body',''),
('integrations','custom_footer',''),
('smtp','smtp_enabled','0'),
('smtp','smtp_host',''),
('smtp','smtp_port','587'),
('smtp','smtp_user',''),
('smtp','smtp_pass',''),
('smtp','smtp_secure','tls'),
('smtp','smtp_from','no-reply@bharatseo.com'),
('smtp','smtp_from_name','Bharat SEO'),
('smtp','notify_email','leads@bharatseo.com'),
('payment','razorpay_enabled','0'),
('payment','razorpay_key',''),
('payment','razorpay_secret',''),
('payment','payu_enabled','0'),
('payment','payu_key',''),
('payment','payu_salt','');

-- Service Categories
INSERT INTO `service_categories` (`id`,`name`,`slug`,`description`,`sort_order`) VALUES
(1,'Social Media','social-media','Social media marketing and management services',1),
(2,'Paid Advertising','paid-advertising','Performance marketing and paid ads',2),
(3,'SEO','seo','Search engine optimization services',3),
(4,'Creative & Web','creative-web','Web design, branding and content',4),
(5,'Growth','growth','Lead generation and growth services',5);

-- Services (the 12 service landing pages requested)
INSERT INTO `services` (`category_id`,`title`,`slug`,`icon`,`short_description`,`overview`,`benefits`,`deliverables`,`process`,`is_featured`,`sort_order`,`meta_title`,`meta_description`) VALUES
(1,'Social Media Marketing','social-media-marketing','share-nodes','End-to-end social media strategy, content and community management that turns followers into customers.','We build full-funnel social media strategies across Instagram, Facebook, LinkedIn and YouTube that grow your audience and drive measurable business results.','Consistent brand presence|Higher engagement & reach|Community growth|More qualified leads','Content calendar|Creative design|Community management|Monthly analytics report','Audit|Strategy|Content creation|Publishing|Engagement|Reporting',1,1,'Social Media Marketing Agency | Bharat SEO','Grow your brand with data-driven social media marketing across Instagram, Facebook, LinkedIn and more.'),
(1,'Instagram Marketing','instagram-marketing','instagram','Reels, stories, and growth campaigns that build a magnetic Instagram presence.','We help brands win on Instagram with scroll-stopping creatives, reels strategy, influencer collaborations and conversion-focused campaigns.','Faster follower growth|Viral-ready reels|Higher engagement|Direct DM leads','Reels strategy|Story design|Hashtag research|Growth campaigns','Audit|Content plan|Creative production|Posting|Optimization',1,2,'Instagram Marketing Services | Bharat SEO','Grow your Instagram with reels, stories and conversion campaigns built by Bharat SEO.'),
(2,'Facebook & Meta Ads','facebook-meta-ads','facebook','High-ROAS Meta advertising across Facebook and Instagram to drive leads and sales.','Our performance team builds and optimizes Meta ad campaigns engineered for low CPL and high return on ad spend.','Lower cost per lead|Higher ROAS|Precise audience targeting|Scalable campaigns','Campaign setup|Creative testing|Audience research|Weekly optimization','Research|Setup|Creative|Launch|Optimize|Scale',1,3,'Facebook & Meta Ads Agency | Bharat SEO','Drive leads and sales with high-ROAS Facebook and Instagram ad campaigns.'),
(2,'Google Ads / PPC','google-ads-ppc','google','Search, display and YouTube campaigns that capture high-intent buyers.','We manage Google Ads campaigns that put your brand in front of customers actively searching for what you offer.','Instant visibility|High-intent traffic|Measurable ROI|Full funnel coverage','Keyword research|Ad copywriting|Landing page guidance|Conversion tracking','Audit|Strategy|Setup|Launch|Optimize|Report',1,4,'Google Ads & PPC Management | Bharat SEO','Capture high-intent buyers with expertly managed Google Ads and PPC campaigns.'),
(3,'Search Engine Optimization','search-engine-optimization','magnifying-glass-chart','Technical, on-page and off-page SEO that ranks you on page one and keeps you there.','Our SEO process combines technical excellence, content strategy and authority building to grow your organic traffic sustainably.','Higher rankings|Organic traffic growth|Better domain authority|Long-term ROI','Technical audit|Keyword strategy|On-page optimization|Link building','Audit|Keyword research|On-page|Content|Off-page|Reporting',1,5,'SEO Agency | Search Engine Optimization | Bharat SEO','Rank higher on Google with technical, on-page and off-page SEO from Bharat SEO.'),
(3,'Local SEO','local-seo','location-dot','Dominate local search and Google Maps to win nearby customers.','We optimize your Google Business Profile, local citations and reviews so your business shows up when nearby customers search.','Google Maps visibility|More calls & directions|Local pack rankings|Review growth','GMB optimization|Citation building|Review strategy|Local content','Audit|GMB setup|Citations|Reviews|Optimization',0,6,'Local SEO Services | Google Maps Ranking | Bharat SEO','Win nearby customers with local SEO and Google Maps optimization.'),
(4,'Website Design & Development','website-design-development','code','Fast, mobile-first, conversion-focused websites built to rank and sell.','We design and develop premium, SEO-ready websites that load fast, look stunning and convert visitors into customers.','Premium design|Mobile-first|SEO-ready|Fast loading','UI/UX design|Responsive build|CMS setup|Speed optimization','Discovery|Design|Development|Launch|Support',1,7,'Website Design & Development | Bharat SEO','Premium, fast, mobile-first websites that rank on Google and convert visitors.'),
(4,'Branding & Graphic Design','branding-graphic-design','palette','Memorable brand identities and scroll-stopping creatives.','From logo to complete brand systems, we craft identities that make your business unforgettable.','Strong brand identity|Consistent visuals|Higher recall|Premium perception','Logo design|Brand guidelines|Social creatives|Marketing collateral','Discovery|Concept|Design|Refine|Deliver',0,8,'Branding & Graphic Design | Bharat SEO','Memorable brand identities and premium creatives that make you unforgettable.'),
(4,'Content Creation','content-creation','pen-nib','Photo, video and copy that stops the scroll and drives action.','Our creative team produces high-quality content tailored to each platform and built to convert.','Scroll-stopping content|Platform-optimized|Consistent output|Higher engagement','Content strategy|Photo & video|Copywriting|Editing','Plan|Produce|Edit|Publish|Analyze',0,9,'Content Creation Services | Bharat SEO','Photo, video and copy that stops the scroll and drives action.'),
(5,'Lead Generation','lead-generation','filter','Predictable, high-quality lead pipelines for B2B and B2C brands.','We build full-funnel lead generation systems that deliver qualified leads on demand.','Predictable pipeline|Qualified leads|Lower CPL|Higher conversion','Funnel design|Landing pages|Ad campaigns|CRM integration','Research|Funnel|Campaign|Optimize|Scale',1,10,'Lead Generation Agency | Bharat SEO','Build a predictable pipeline of qualified leads with Bharat SEO.'),
(1,'Influencer Marketing','influencer-marketing','users','Creator collaborations that build trust and amplify your reach.','We connect your brand with the right creators to drive authentic reach and conversions.','Authentic reach|Trust building|Targeted audiences|Content amplification','Creator sourcing|Campaign management|Content rights|Performance tracking','Brief|Source|Negotiate|Launch|Report',0,11,'Influencer Marketing Agency | Bharat SEO','Creator collaborations that build trust and amplify your brand reach.'),
(5,'Reputation Management','reputation-management','shield-halved','Protect and strengthen your brand reputation online.','We monitor, manage and improve your online reputation across reviews and search results.','Better reviews|Positive search results|Crisis handling|Brand trust','Review monitoring|Response management|Reputation repair|Reporting','Audit|Monitor|Respond|Improve|Report',0,12,'Online Reputation Management | Bharat SEO','Protect and strengthen your brand reputation online with Bharat SEO.');

-- Portfolio
INSERT INTO `portfolio` (`title`,`slug`,`category`,`client_industry`,`services_provided`,`result_metric`,`description`,`project_url`,`sort_order`) VALUES
('FitLife Gym — Social Growth','fitlife-gym-social-growth','Social Media','Fitness','Social Media Marketing, Instagram Marketing','+340% engagement in 90 days','A complete social media revamp for a fitness chain that tripled engagement and filled membership slots.','',1),
('UrbanCart — Meta Ads Scale','urbancart-meta-ads','Paid Advertising','E-commerce','Facebook & Meta Ads, Lead Generation','5.8x ROAS at scale','Scaled an e-commerce brand from 2x to 5.8x ROAS with creative testing and funnel optimization.','',2),
('GreenLeaf Clinic — Local SEO','greenleaf-clinic-local-seo','SEO','Healthcare','Local SEO, Reputation Management','Top 3 Google Maps ranking','Helped a multi-location clinic dominate local search and double appointment bookings.','',3),
('TechNova — Website & Branding','technova-website-branding','Creative & Web','SaaS','Website Design & Development, Branding','+72% conversion rate','Designed and built a premium SaaS website with a refreshed brand identity.','',4),
('StyleHub — Influencer Campaign','stylehub-influencer-campaign','Social Media','Fashion','Influencer Marketing, Content Creation','2.1M reach generated','Ran a multi-creator influencer campaign that generated over 2 million impressions.','',5),
('LeadPro — PPC Lead Engine','leadpro-ppc-lead-engine','Paid Advertising','B2B Services','Google Ads / PPC, Lead Generation','-43% cost per lead','Rebuilt a B2B PPC engine and cut cost per lead by 43% while increasing volume.','',6);

-- Case Studies
INSERT INTO `case_studies` (`title`,`slug`,`client_name`,`industry`,`challenge`,`strategy`,`execution`,`results`,`reach_growth`,`leads_growth`,`revenue_impact`,`roi_metric`,`before_metric`,`after_metric`,`testimonial`,`sort_order`) VALUES
('How UrbanCart Hit 5.8x ROAS','urbancart-5-8x-roas','UrbanCart','E-commerce','UrbanCart was stuck at a 2x ROAS and could not scale spend profitably.','We restructured the ad account, built a creative testing system and rebuilt the retargeting funnel.','Launched 40+ creative variations, implemented advantage+ shopping campaigns and optimized the post-click experience.','ROAS climbed from 2x to 5.8x while monthly ad spend tripled — all profitable.','+410%','+260%','+3.2x revenue','5.8x ROAS','2.0x ROAS','5.8x ROAS','Bharat SEO transformed our paid media. We finally scaled without losing profitability.',1),
('FitLife Gym 3x Social Engagement','fitlife-3x-engagement','FitLife Gym','Fitness','Low engagement and stagnant membership sign-ups from social channels.','Reels-first content strategy with community management and local awareness campaigns.','Produced 3 reels per week, ran story polls and launched a referral campaign.','Engagement grew 340% and membership inquiries from social tripled in 90 days.','+340%','+300%','+2.1x sign-ups','4.5x ROI','1.2% eng. rate','5.3% eng. rate','Our gym has never been this busy. The content team just gets our brand.',2);

-- Testimonials
INSERT INTO `testimonials` (`client_name`,`company`,`designation`,`rating`,`review`,`sort_order`) VALUES
('Rohan Mehta','UrbanCart','Founder',5,'Bharat SEO scaled our Meta ads from 2x to nearly 6x ROAS. Their team is data-obsessed and genuinely cares about results.',1),
('Priya Sharma','FitLife Gym','Marketing Head',5,'Our social media finally looks premium and our inbox is full of membership inquiries. Highly recommend.',2),
('Aman Verma','TechNova','CEO',5,'The new website and branding lifted our conversion rate by over 70%. Professional, fast and creative.',3),
('Neha Gupta','GreenLeaf Clinic','Director',5,'We now rank top 3 on Google Maps across all our locations. Appointments have doubled.',4),
('Karan Singh','LeadPro','Growth Lead',5,'Cost per lead dropped 43% within two months. The team is responsive and transparent with reporting.',5);

-- Team
INSERT INTO `team_members` (`name`,`designation`,`bio`,`sort_order`) VALUES
('Arjun Nair','Founder & Growth Strategist','10+ years scaling brands with performance marketing and SEO.',1),
('Sneha Iyer','Head of Social Media','Turns brands into communities with reels-first content strategy.',2),
('Vikram Rao','SEO Director','Technical SEO expert obsessed with page-one rankings.',3),
('Divya Menon','Creative Director','Leads branding, design and high-converting creatives.',4);

-- Pricing Packages
INSERT INTO `pricing_packages` (`name`,`price`,`currency`,`duration`,`description`,`features`,`is_popular`,`cta_text`,`sort_order`) VALUES
('Starter','24,999','INR','/month','Perfect for small businesses starting their digital journey.','2 social platforms|12 posts per month|Basic SEO setup|Monthly report|Email support',0,'Get Started',1),
('Growth','49,999','INR','/month','For growing brands ready to scale reach and leads.','4 social platforms|20 posts + 8 reels|On-page SEO|Meta ads management|Bi-weekly reports|Priority support',1,'Choose Growth',2),
('Scale','99,999','INR','/month','For established brands focused on aggressive growth.','All platforms|Daily content|Full SEO + Local SEO|Google + Meta ads|Dedicated manager|Weekly strategy calls',0,'Scale Now',3),
('Custom Enterprise','Custom','INR','','Tailored programs for enterprises and multi-location brands.','Custom strategy|Dedicated team|Advanced analytics|Quarterly business reviews|SLA support',0,'Contact Sales',4);

-- Blog Categories
INSERT INTO `blog_categories` (`id`,`name`,`slug`,`description`) VALUES
(1,'SEO','seo','Search engine optimization tips and strategies'),
(2,'Social Media','social-media','Social media marketing insights'),
(3,'Paid Ads','paid-ads','Performance marketing and advertising'),
(4,'Growth','growth','Business growth and lead generation');

-- Blogs
INSERT INTO `blogs` (`category_id`,`title`,`slug`,`excerpt`,`content`,`author`,`reading_time`,`tags`,`status`,`published_at`) VALUES
(1,'10 SEO Trends That Will Define 2026','seo-trends-2026','From AI search to topical authority, here are the SEO trends shaping rankings in 2026.','<p>Search is evolving faster than ever. In this guide we break down the ten most important SEO trends for 2026 and how your brand can stay ahead.</p><h2>1. AI-Powered Search</h2><p>Search engines increasingly surface AI-generated answers. Optimize for clarity, structure and authority.</p><h2>2. Topical Authority</h2><p>Covering a topic comprehensively beats publishing scattered keyword pages.</p><h2>3. Core Web Vitals</h2><p>Speed and stability remain ranking factors. Optimize images, defer scripts and reduce layout shift.</p><p>Want a free audit of your site? <a href=\"/free-audit\">Request one here</a>.</p>','Vikram Rao',6,'seo,trends,2026','published',NOW()),
(2,'How to Make Reels That Actually Convert','reels-that-convert','Reels drive reach — but do they drive sales? Here is our proven framework.','<p>Reels are the fastest way to grow on Instagram, but reach without conversion is vanity. Here is how we turn reels into revenue.</p><h2>Hook in 3 seconds</h2><p>Your first frame decides everything. Lead with the payoff.</p><h2>Value, then offer</h2><p>Educate or entertain first, then introduce a clear call to action.</p>','Sneha Iyer',5,'instagram,reels,social media','published',NOW()),
(3,'Cut Your Cost Per Lead in Half With This Funnel','cut-cost-per-lead','A step-by-step breakdown of the funnel we use to slash CPL for our clients.','<p>High cost per lead kills growth. In this post we share the exact funnel structure we use to cut CPL by up to 50%.</p><h2>Offer-first creative</h2><p>Lead with a compelling, specific offer.</p><h2>Frictionless landing pages</h2><p>Fewer fields, faster load, clear value.</p>','Arjun Nair',7,'meta ads,ppc,lead generation','published',NOW());

-- FAQs
INSERT INTO `faqs` (`question`,`answer`,`category`,`sort_order`) VALUES
('How soon will I see results?','SEO typically shows momentum in 3–6 months, while paid ads and social campaigns can drive leads within the first few weeks. We set clear, realistic milestones from day one.','general',1),
('Do you work with small businesses?','Absolutely. Our Starter package is designed specifically for small businesses, and we scale your plan as you grow.','general',2),
('How do you report on performance?','You get transparent dashboards and regular reports covering reach, leads, rankings, ROAS and revenue impact — no vanity metrics.','general',3),
('Do I need a long-term contract?','Most of our plans are monthly. We earn your business with results, not lock-in contracts.','general',4),
('Which industries do you serve?','We work across e-commerce, healthcare, real estate, education, SaaS, fitness, hospitality and more.','general',5);

-- Menus (header)
INSERT INTO `menus` (`location`,`title`,`url`,`sort_order`,`is_button`) VALUES
('header','Home','/',1,0),
('header','About','/about',2,0),
('header','Services','/services',3,0),
('header','Portfolio','/portfolio',4,0),
('header','Case Studies','/case-studies',5,0),
('header','Pricing','/pricing',6,0),
('header','Blog','/blog',7,0),
('header','Contact','/contact',8,0),
('header','Get Free Audit','/free-audit',9,1),
('footer','Privacy Policy','/privacy-policy',1,0),
('footer','Terms & Conditions','/terms-conditions',2,0),
('footer','Disclaimer','/disclaimer',3,0),
('footer','Sitemap','/sitemap',4,0);

-- Social Links
INSERT INTO `social_links` (`platform`,`icon`,`url`,`sort_order`) VALUES
('Facebook','facebook','https://facebook.com/',1),
('Instagram','instagram','https://instagram.com/',2),
('LinkedIn','linkedin','https://linkedin.com/',3),
('YouTube','youtube','https://youtube.com/',4),
('Twitter','x-twitter','https://twitter.com/',5);

-- Agency Stats
INSERT INTO `agency_stats` (`label`,`value`,`suffix`,`icon`,`sort_order`) VALUES
('Projects Delivered','450','+','rocket',1),
('Happy Clients','280','+','face-smile',2),
('Leads Generated','120','K+','filter',3),
('Average ROI','4.8','x','chart-line',4),
('Social Reach','85','M+','share-nodes',5);

-- Client Logos
INSERT INTO `client_logos` (`name`,`sort_order`) VALUES
('UrbanCart',1),('FitLife',2),('TechNova',3),('GreenLeaf',4),('StyleHub',5),('LeadPro',6);

-- Pages (system/legal)
INSERT INTO `pages` (`title`,`slug`,`content`,`is_system`) VALUES
('About Us','about','<p>Bharat SEO is a results-driven digital marketing agency on a mission to turn attention into measurable business growth. We combine strategy, creativity and performance marketing to help brands scale.</p>',1),
('Privacy Policy','privacy-policy','<p>This Privacy Policy explains how Bharat SEO collects, uses and protects your information when you use our website and services.</p><h2>Information We Collect</h2><p>We collect information you provide through our forms, such as your name, email, phone number and business details.</p><h2>How We Use Information</h2><p>We use your information to respond to inquiries, provide services and improve our offerings.</p>',1),
('Terms & Conditions','terms-conditions','<p>By using the Bharat SEO website you agree to these Terms & Conditions.</p><h2>Use of Service</h2><p>You agree to use our website lawfully and not to misuse any content or services.</p>',1),
('Disclaimer','disclaimer','<p>The information provided by Bharat SEO is for general informational purposes only. Results may vary based on industry, market and effort.</p>',1);

-- Page SEO for key routes
INSERT INTO `page_seo` (`page_key`,`meta_title`,`meta_description`) VALUES
('home','Bharat SEO — Social Media Marketing, SEO & Lead Generation Agency','Grow your brand with data-driven social media marketing, SEO, paid ads, branding and lead generation from Bharat SEO.'),
('about','About Bharat SEO — Our Story & Team','Learn about Bharat SEO, a results-driven digital marketing agency helping brands scale online.'),
('services','Our Services — Digital Marketing Agency | Bharat SEO','Explore Bharat SEO services: social media marketing, SEO, paid ads, web design, branding and lead generation.'),
('portfolio','Our Work & Portfolio | Bharat SEO','See real results we have delivered across social media, SEO, paid ads and web design.'),
('case-studies','Case Studies & Client Results | Bharat SEO','Read in-depth case studies showing how Bharat SEO drives reach, leads and revenue.'),
('pricing','Pricing & Packages | Bharat SEO','Transparent digital marketing packages for businesses of every size.'),
('blog','Digital Marketing Blog | Bharat SEO','Actionable SEO, social media and paid ads insights from the Bharat SEO team.'),
('contact','Contact Bharat SEO','Get in touch with Bharat SEO for a free strategy call and digital marketing audit.'),
('free-audit','Free Website & Marketing Audit | Bharat SEO','Request a free, no-obligation digital marketing audit for your business.'),
('industries','Industries We Serve | Bharat SEO','Bharat SEO serves e-commerce, healthcare, real estate, SaaS, fitness and more.');

SET FOREIGN_KEY_CHECKS = 1;
