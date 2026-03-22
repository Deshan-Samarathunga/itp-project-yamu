-- Yamu shared auth, role, and account schema
-- Dependencies: none

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','staff','driver','customer') NOT NULL DEFAULT 'customer',
  `full_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` longtext,
  `city` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `dob` varchar(50) DEFAULT NULL,
  `license_or_nic` varchar(100) DEFAULT NULL,
  `verification_status` varchar(20) NOT NULL DEFAULT 'verified',
  `bio` longtext,
  `profile_pic` varchar(255) DEFAULT 'avatar.png',
  `account_status` varchar(20) NOT NULL DEFAULT 'active',
  `rag_date` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_key` varchar(20) NOT NULL,
  `role_name` varchar(80) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `uk_roles_key` (`role_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `roles` (`role_key`, `role_name`, `description`, `is_system`)
VALUES
  ('customer', 'Customer', 'Person who books vehicles and/or drivers', 1),
  ('driver', 'Driver', 'Driver who provides driving service', 1),
  ('staff', 'Staff', 'Rental provider or store operator', 1),
  ('admin', 'Admin', 'System administrator with full platform control', 1);

CREATE TABLE IF NOT EXISTS `user_roles` (
  `user_role_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `role_key` varchar(20) NOT NULL,
  `role_status` varchar(20) NOT NULL DEFAULT 'active',
  `verification_status` varchar(20) NOT NULL DEFAULT 'verified',
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `assigned_by_user_id` int(11) DEFAULT NULL,
  `verified_by_user_id` int(11) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `notes` longtext DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_role_id`),
  UNIQUE KEY `uk_user_roles_user_role` (`user_id`, `role_key`),
  KEY `idx_user_roles_role_key` (`role_key`),
  KEY `idx_user_roles_role_status` (`role_status`),
  KEY `idx_user_roles_verification_status` (`verification_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `user_roles` (`user_id`, `role_key`, `role_status`, `verification_status`, `is_primary`, `created_at`, `updated_at`)
SELECT
  u.`user_id`,
  u.`role`,
  CASE
    WHEN LOWER(COALESCE(u.`account_status`, '')) IN ('active', 'verified') THEN 'active'
    WHEN LOWER(COALESCE(u.`account_status`, '')) IN ('pending') THEN 'pending'
    WHEN LOWER(COALESCE(u.`account_status`, '')) IN ('suspended') THEN 'suspended'
    WHEN LOWER(COALESCE(u.`account_status`, '')) IN ('rejected') THEN 'rejected'
    WHEN LOWER(COALESCE(u.`account_status`, '')) IN ('deactivated') THEN 'deactivated'
    ELSE 'active'
  END,
  CASE
    WHEN LOWER(COALESCE(u.`verification_status`, '')) IN ('unverified', 'pending', 'approved', 'rejected', 'verified')
      THEN LOWER(u.`verification_status`)
    WHEN u.`role` IN ('driver', 'staff')
      THEN 'pending'
    ELSE 'verified'
  END,
  1,
  COALESCE(u.`created_at`, u.`rag_date`, NOW()),
  COALESCE(u.`updated_at`, u.`rag_date`, NOW())
FROM `users` u
LEFT JOIN `user_roles` ur
  ON ur.`user_id` = u.`user_id`
 AND ur.`role_key` = u.`role`
WHERE ur.`user_role_id` IS NULL;

CREATE TABLE IF NOT EXISTS `password_resets` (
  `password_reset_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`password_reset_id`),
  UNIQUE KEY `uk_password_resets_token_hash` (`token_hash`),
  KEY `idx_password_resets_email` (`email`),
  KEY `idx_password_resets_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
