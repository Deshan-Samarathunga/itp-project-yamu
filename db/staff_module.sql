-- Yamu staff and rental inventory schema
-- Dependencies: users, user_roles

CREATE TABLE IF NOT EXISTS `staff_profiles` (
  `staff_profile_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `store_name` varchar(255) DEFAULT NULL,
  `store_owner` varchar(255) DEFAULT NULL,
  `business_registration_number` varchar(100) DEFAULT NULL,
  `store_address` longtext DEFAULT NULL,
  `store_contact_number` varchar(30) DEFAULT NULL,
  `store_email` varchar(255) DEFAULT NULL,
  `verification_status` varchar(20) NOT NULL DEFAULT 'pending',
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`staff_profile_id`),
  UNIQUE KEY `uk_staff_profile_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

CREATE TABLE IF NOT EXISTS `brands` (
  `brand_id` int(11) NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(50) DEFAULT NULL,
  `brand_logo` varchar(255) DEFAULT NULL,
  `creation_date` datetime DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  `brand_status` int(11) DEFAULT '1',
  PRIMARY KEY (`brand_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `vehicles` (
  `vehicle_id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_user_id` int(11) DEFAULT NULL,
  `vehicle_title` varchar(50) DEFAULT NULL,
  `vehicle_brand` varchar(50) DEFAULT NULL,
  `vehicle_desc` longtext,
  `price` float DEFAULT NULL,
  `transmission` varchar(50) DEFAULT NULL,
  `fuel_type` varchar(50) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `engine_capacity` varchar(50) DEFAULT NULL,
  `capacity` int(3) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `registration_number` varchar(100) DEFAULT NULL,
  `vImg1` varchar(255) DEFAULT NULL,
  `vImg2` varchar(255) DEFAULT NULL,
  `vImg3` varchar(255) DEFAULT NULL,
  `vImg4` varchar(255) DEFAULT NULL,
  `airConditioner` int(1) DEFAULT NULL,
  `powerdoorlocks` int(1) DEFAULT NULL,
  `antilockbrakingsys` int(1) DEFAULT NULL,
  `brakeassist` int(1) DEFAULT NULL,
  `powersteering` int(1) DEFAULT NULL,
  `driverairbag` int(1) DEFAULT NULL,
  `passengerairbag` int(1) DEFAULT NULL,
  `powerwindow` int(1) DEFAULT NULL,
  `cdplayer` int(1) DEFAULT NULL,
  `reg_date` datetime DEFAULT NULL,
  `updatation_date` datetime DEFAULT NULL,
  `vehicle_status` int(1) DEFAULT '1',
  `listing_status` varchar(20) NOT NULL DEFAULT 'approved',
  `availability_status` varchar(20) NOT NULL DEFAULT 'available',
  `maintenance_status` varchar(30) NOT NULL DEFAULT 'good',
  `service_date` date DEFAULT NULL,
  `next_service_date` date DEFAULT NULL,
  `service_notes` longtext,
  `service_cost` decimal(10,2) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`vehicle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
