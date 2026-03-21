ALTER TABLE `booking`
  ADD COLUMN `promotion_id` INT(11) DEFAULT NULL AFTER `payment_status`,
  ADD COLUMN `promo_code` VARCHAR(50) DEFAULT NULL AFTER `promotion_id`,
  ADD COLUMN `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `promo_code`,
  ADD COLUMN `final_amount` DECIMAL(10,2) DEFAULT NULL AFTER `discount_amount`;

UPDATE `booking`
SET
  `discount_amount` = COALESCE(`discount_amount`, 0.00),
  `final_amount` = COALESCE(`final_amount`, `total`);

CREATE TABLE IF NOT EXISTS `reviews` (
  `review_id` INT(11) NOT NULL AUTO_INCREMENT,
  `booking_id` INT(11) NOT NULL,
  `customer_id` INT(11) NOT NULL,
  `vehicle_id` INT(11) NOT NULL,
  `driver_id` INT(11) DEFAULT NULL,
  `rating` INT(11) NOT NULL,
  `comment` LONGTEXT,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`review_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `complaints` (
  `complaint_id` INT(11) NOT NULL AUTO_INCREMENT,
  `booking_id` INT(11) NOT NULL,
  `complainant_user_id` INT(11) NOT NULL,
  `target_user_id` INT(11) DEFAULT NULL,
  `target_vehicle_id` INT(11) DEFAULT NULL,
  `subject` VARCHAR(255) DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `description` LONGTEXT,
  `attachment` VARCHAR(255) DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'open',
  `driver_response` LONGTEXT,
  `admin_notes` LONGTEXT,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`complaint_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` INT(11) NOT NULL AUTO_INCREMENT,
  `booking_id` INT(11) NOT NULL,
  `customer_id` INT(11) NOT NULL,
  `driver_id` INT(11) DEFAULT NULL,
  `promotion_id` INT(11) DEFAULT NULL,
  `promo_code` VARCHAR(50) DEFAULT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `final_amount` DECIMAL(10,2) NOT NULL,
  `payment_method` VARCHAR(50) DEFAULT NULL,
  `transaction_reference` VARCHAR(100) DEFAULT NULL,
  `payment_status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `paid_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`payment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `promotions` (
  `promotion_id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL,
  `title` VARCHAR(150) DEFAULT NULL,
  `description` LONGTEXT,
  `discount_type` VARCHAR(20) NOT NULL DEFAULT 'fixed',
  `discount_value` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `valid_from` DATETIME DEFAULT NULL,
  `valid_to` DATETIME DEFAULT NULL,
  `usage_limit` INT(11) DEFAULT NULL,
  `usage_count` INT(11) NOT NULL DEFAULT 0,
  `minimum_booking_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `applicable_vehicle_id` INT(11) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`promotion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
