ALTER TABLE `users`
  MODIFY COLUMN `password` VARCHAR(255) DEFAULT NULL,
  MODIFY COLUMN `role` ENUM('admin','staff','driver','customer') NOT NULL DEFAULT 'customer',
  MODIFY COLUMN `account_status` VARCHAR(20) NOT NULL DEFAULT 'active',
  MODIFY COLUMN `verification_status` VARCHAR(20) NOT NULL DEFAULT 'verified',
  MODIFY COLUMN `phone` VARCHAR(20) DEFAULT NULL,
  MODIFY COLUMN `profile_pic` VARCHAR(255) DEFAULT 'avatar.png',
  ADD COLUMN `last_login_at` DATETIME DEFAULT NULL AFTER `updated_at`;

UPDATE `users`
SET
  `account_status` = CASE
    WHEN LOWER(COALESCE(`account_status`, '')) IN ('active', 'verified') THEN 'active'
    WHEN LOWER(COALESCE(`account_status`, '')) IN ('pending') THEN 'pending'
    WHEN LOWER(COALESCE(`account_status`, '')) IN ('suspended') THEN 'suspended'
    WHEN LOWER(COALESCE(`account_status`, '')) IN ('rejected') THEN 'rejected'
    WHEN LOWER(COALESCE(`account_status`, '')) IN ('deactivated') THEN 'deactivated'
    ELSE 'active'
  END,
  `verification_status` = CASE
    WHEN LOWER(COALESCE(`verification_status`, '')) IN ('unverified', 'pending', 'approved', 'rejected', 'verified') THEN LOWER(`verification_status`)
    ELSE CASE
      WHEN `role` IN ('driver', 'staff') THEN 'pending'
      ELSE 'verified'
    END
  END,
  `created_at` = COALESCE(`created_at`, `rag_date`, NOW()),
  `updated_at` = COALESCE(`updated_at`, `rag_date`, NOW());

CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` INT(11) NOT NULL AUTO_INCREMENT,
  `role_key` VARCHAR(20) NOT NULL,
  `role_name` VARCHAR(80) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `is_system` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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
  `user_role_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `role_key` VARCHAR(20) NOT NULL,
  `role_status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `verification_status` VARCHAR(20) NOT NULL DEFAULT 'verified',
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `assigned_by_user_id` INT(11) DEFAULT NULL,
  `verified_by_user_id` INT(11) DEFAULT NULL,
  `verified_at` DATETIME DEFAULT NULL,
  `notes` LONGTEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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
  END AS role_status,
  CASE
    WHEN LOWER(COALESCE(u.`verification_status`, '')) IN ('unverified', 'pending', 'approved', 'rejected', 'verified')
      THEN LOWER(u.`verification_status`)
    WHEN u.`role` IN ('driver', 'staff')
      THEN 'pending'
    ELSE 'verified'
  END AS verification_status,
  1 AS is_primary,
  COALESCE(u.`created_at`, u.`rag_date`, NOW()) AS created_at,
  COALESCE(u.`updated_at`, u.`rag_date`, NOW()) AS updated_at
FROM `users` u
LEFT JOIN `user_roles` ur
  ON ur.`user_id` = u.`user_id`
 AND ur.`role_key` = u.`role`
WHERE ur.`user_role_id` IS NULL;

CREATE TABLE IF NOT EXISTS `customer_profiles` (
  `customer_profile_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `preferences` LONGTEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`customer_profile_id`),
  UNIQUE KEY `uk_customer_profile_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `driver_profiles` (
  `driver_profile_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `driving_license_number` VARCHAR(100) DEFAULT NULL,
  `license_expiry_date` DATE DEFAULT NULL,
  `nic_id` VARCHAR(100) DEFAULT NULL,
  `service_area` VARCHAR(255) DEFAULT NULL,
  `verification_status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `verified_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`driver_profile_id`),
  UNIQUE KEY `uk_driver_profile_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `staff_profiles` (
  `staff_profile_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `store_name` VARCHAR(255) DEFAULT NULL,
  `store_owner` VARCHAR(255) DEFAULT NULL,
  `business_registration_number` VARCHAR(100) DEFAULT NULL,
  `store_address` LONGTEXT DEFAULT NULL,
  `store_contact_number` VARCHAR(30) DEFAULT NULL,
  `store_email` VARCHAR(255) DEFAULT NULL,
  `verification_status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `verified_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`staff_profile_id`),
  UNIQUE KEY `uk_staff_profile_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `admin_profiles` (
  `admin_profile_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `system_permissions` LONGTEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_profile_id`),
  UNIQUE KEY `uk_admin_profile_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `customer_profiles` (`user_id`, `created_at`, `updated_at`)
SELECT u.`user_id`, COALESCE(u.`created_at`, NOW()), COALESCE(u.`updated_at`, NOW())
FROM `users` u
JOIN `user_roles` ur ON ur.`user_id` = u.`user_id` AND ur.`role_key` = 'customer'
LEFT JOIN `customer_profiles` cp ON cp.`user_id` = u.`user_id`
WHERE cp.`customer_profile_id` IS NULL;

INSERT INTO `driver_profiles` (`user_id`, `driving_license_number`, `nic_id`, `verification_status`, `created_at`, `updated_at`)
SELECT
  u.`user_id`,
  NULLIF(u.`license_or_nic`, ''),
  NULLIF(u.`license_or_nic`, ''),
  CASE
    WHEN LOWER(COALESCE(u.`verification_status`, '')) IN ('approved', 'verified') THEN 'verified'
    WHEN LOWER(COALESCE(u.`verification_status`, '')) IN ('rejected') THEN 'rejected'
    ELSE 'pending'
  END,
  COALESCE(u.`created_at`, NOW()),
  COALESCE(u.`updated_at`, NOW())
FROM `users` u
JOIN `user_roles` ur ON ur.`user_id` = u.`user_id` AND ur.`role_key` = 'driver'
LEFT JOIN `driver_profiles` dp ON dp.`user_id` = u.`user_id`
WHERE dp.`driver_profile_id` IS NULL;

INSERT INTO `staff_profiles` (`user_id`, `store_name`, `store_owner`, `store_address`, `store_contact_number`, `store_email`, `verification_status`, `created_at`, `updated_at`)
SELECT
  u.`user_id`,
  NULL,
  u.`full_name`,
  u.`address`,
  u.`phone`,
  u.`email`,
  CASE
    WHEN LOWER(COALESCE(u.`verification_status`, '')) IN ('approved', 'verified') THEN 'verified'
    WHEN LOWER(COALESCE(u.`verification_status`, '')) IN ('rejected') THEN 'rejected'
    ELSE 'pending'
  END,
  COALESCE(u.`created_at`, NOW()),
  COALESCE(u.`updated_at`, NOW())
FROM `users` u
JOIN `user_roles` ur ON ur.`user_id` = u.`user_id` AND ur.`role_key` = 'staff'
LEFT JOIN `staff_profiles` sp ON sp.`user_id` = u.`user_id`
WHERE sp.`staff_profile_id` IS NULL;

INSERT INTO `admin_profiles` (`user_id`, `system_permissions`, `created_at`, `updated_at`)
SELECT
  u.`user_id`,
  'all',
  COALESCE(u.`created_at`, NOW()),
  COALESCE(u.`updated_at`, NOW())
FROM `users` u
JOIN `user_roles` ur ON ur.`user_id` = u.`user_id` AND ur.`role_key` = 'admin'
LEFT JOIN `admin_profiles` ap ON ap.`user_id` = u.`user_id`
WHERE ap.`admin_profile_id` IS NULL;

CREATE TABLE IF NOT EXISTS `password_resets` (
  `password_reset_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) DEFAULT NULL,
  `email` VARCHAR(255) NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`password_reset_id`),
  UNIQUE KEY `uk_password_resets_token_hash` (`token_hash`),
  KEY `idx_password_resets_email` (`email`),
  KEY `idx_password_resets_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

