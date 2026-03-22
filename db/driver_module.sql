-- Yamu driver schema
-- Dependencies: users, user_roles

CREATE TABLE IF NOT EXISTS `driver_profiles` (
  `driver_profile_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `driving_license_number` varchar(100) DEFAULT NULL,
  `license_expiry_date` date DEFAULT NULL,
  `nic_id` varchar(100) DEFAULT NULL,
  `service_area` varchar(255) DEFAULT NULL,
  `provider_details` longtext DEFAULT NULL,
  `verification_status` varchar(20) NOT NULL DEFAULT 'pending',
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`driver_profile_id`),
  UNIQUE KEY `uk_driver_profile_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

CREATE TABLE IF NOT EXISTS `driver_ads` (
  `driver_ad_id` int(11) NOT NULL AUTO_INCREMENT,
  `driver_user_id` int(11) NOT NULL,
  `ad_title` varchar(150) DEFAULT NULL,
  `tagline` varchar(255) DEFAULT NULL,
  `service_location` varchar(150) DEFAULT NULL,
  `languages` varchar(255) DEFAULT NULL,
  `experience_years` int(11) NOT NULL DEFAULT '0',
  `daily_rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `max_group_size` int(11) NOT NULL DEFAULT '1',
  `availability_status` varchar(20) NOT NULL DEFAULT 'available',
  `advertisement_status` varchar(20) NOT NULL DEFAULT 'draft',
  `specialties` longtext,
  `description` longtext,
  `contact_preference` varchar(20) NOT NULL DEFAULT 'both',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`driver_ad_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
