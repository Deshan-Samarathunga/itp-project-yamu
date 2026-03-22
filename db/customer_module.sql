-- Yamu customer schema
-- Dependencies: users, roles, user_roles

CREATE TABLE IF NOT EXISTS `customer_profiles` (
  `customer_profile_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `preferences` longtext DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`customer_profile_id`),
  UNIQUE KEY `uk_customer_profile_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `customer_profiles` (`user_id`, `created_at`, `updated_at`)
SELECT u.`user_id`, COALESCE(u.`created_at`, NOW()), COALESCE(u.`updated_at`, NOW())
FROM `users` u
JOIN `user_roles` ur ON ur.`user_id` = u.`user_id` AND ur.`role_key` = 'customer'
LEFT JOIN `customer_profiles` cp ON cp.`user_id` = u.`user_id`
WHERE cp.`customer_profile_id` IS NULL;
