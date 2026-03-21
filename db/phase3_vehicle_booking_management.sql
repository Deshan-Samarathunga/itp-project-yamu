ALTER TABLE `vehicles`
  ADD COLUMN `owner_user_id` INT(11) DEFAULT NULL AFTER `vehicle_id`,
  ADD COLUMN `location` VARCHAR(150) DEFAULT NULL AFTER `capacity`,
  ADD COLUMN `registration_number` VARCHAR(100) DEFAULT NULL AFTER `location`,
  ADD COLUMN `listing_status` VARCHAR(20) NOT NULL DEFAULT 'approved' AFTER `vehicle_status`,
  ADD COLUMN `availability_status` VARCHAR(20) NOT NULL DEFAULT 'available' AFTER `listing_status`,
  ADD COLUMN `maintenance_status` VARCHAR(30) NOT NULL DEFAULT 'good' AFTER `availability_status`,
  ADD COLUMN `service_date` DATE DEFAULT NULL AFTER `maintenance_status`,
  ADD COLUMN `next_service_date` DATE DEFAULT NULL AFTER `service_date`,
  ADD COLUMN `service_notes` LONGTEXT DEFAULT NULL AFTER `next_service_date`,
  ADD COLUMN `service_cost` DECIMAL(10,2) DEFAULT NULL AFTER `service_notes`,
  ADD COLUMN `approved_by` INT(11) DEFAULT NULL AFTER `service_cost`,
  ADD COLUMN `approved_at` DATETIME DEFAULT NULL AFTER `approved_by`,
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL AFTER `approved_at`;

UPDATE `vehicles`
SET
  `owner_user_id` = COALESCE(`owner_user_id`, (SELECT MIN(`user_id`) FROM `users` WHERE `role` = 'admin')),
  `listing_status` = CASE
    WHEN `vehicle_status` = 1 THEN 'approved'
    ELSE 'inactive'
  END,
  `availability_status` = CASE
    WHEN `vehicle_status` = 1 THEN 'available'
    ELSE 'unavailable'
  END,
  `maintenance_status` = COALESCE(NULLIF(`maintenance_status`, ''), 'good'),
  `approved_by` = COALESCE(`approved_by`, (SELECT MIN(`user_id`) FROM `users` WHERE `role` = 'admin')),
  `approved_at` = COALESCE(`approved_at`, `reg_date`, NOW()),
  `updated_at` = COALESCE(`updated_at`, `updatation_date`, `reg_date`, NOW());

ALTER TABLE `booking`
  ADD COLUMN `driver_id` INT(11) DEFAULT NULL AFTER `user_ID`,
  ADD COLUMN `booking_status` VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER `status`,
  ADD COLUMN `payment_status` VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER `booking_status`,
  ADD COLUMN `created_at` DATETIME DEFAULT NULL AFTER `booking_Date`,
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL AFTER `created_at`,
  ADD COLUMN `cancelled_at` DATETIME DEFAULT NULL AFTER `updated_at`,
  ADD COLUMN `completed_at` DATETIME DEFAULT NULL AFTER `cancelled_at`;

UPDATE `booking` b
LEFT JOIN `vehicles` v ON v.`vehicle_id` = b.`vehicle_ID`
SET
  b.`driver_id` = COALESCE(b.`driver_id`, v.`owner_user_id`),
  b.`booking_status` = CASE
    WHEN b.`status` = 1 THEN 'confirmed'
    ELSE 'pending'
  END,
  b.`payment_status` = COALESCE(NULLIF(b.`payment_status`, ''), 'pending'),
  b.`created_at` = COALESCE(b.`created_at`, b.`booking_Date`, NOW()),
  b.`updated_at` = COALESCE(b.`updated_at`, b.`update_Date`, b.`booking_Date`, NOW());

UPDATE `vehicles`
SET `availability_status` = 'unavailable'
WHERE `listing_status` <> 'approved'
   OR `maintenance_status` IN ('under maintenance', 'unavailable');

UPDATE `vehicles` v
SET v.`availability_status` = 'booked'
WHERE v.`listing_status` = 'approved'
  AND v.`maintenance_status` NOT IN ('under maintenance', 'unavailable')
  AND EXISTS (
    SELECT 1
    FROM `booking` b
    WHERE b.`vehicle_ID` = v.`vehicle_id`
      AND b.`booking_status` IN ('pending', 'confirmed')
  );
