-- Phase 9: move sample vehicle listings away from admin users
-- Run this after importing db/yamu.sql if the sample vehicles still belong to admin accounts.

INSERT INTO `users` (
  `username`,
  `password`,
  `role`,
  `full_name`,
  `email`,
  `address`,
  `city`,
  `phone`,
  `dob`,
  `license_or_nic`,
  `verification_status`,
  `bio`,
  `profile_pic`,
  `account_status`,
  `rag_date`,
  `created_at`,
  `updated_at`
)
SELECT
  'sample.staff@yamu.com',
  '827ccb0eea8a706c4c34a16891f84e7b',
  'staff',
  'Yamu Rental Center',
  'sample.staff@yamu.com',
  '155 Galle Road, Colombo 03',
  'Colombo',
  '0775550100',
  '',
  NULL,
  'verified',
  'Sample seeded rental center account for vehicle listings.',
  'avatar.png',
  'active',
  '2023-06-11 12:45:00',
  '2023-06-11 12:45:00',
  '2023-06-11 12:45:00'
WHERE NOT EXISTS (
  SELECT 1 FROM `users` WHERE `email` = 'sample.staff@yamu.com'
);

INSERT INTO `user_roles` (
  `user_id`,
  `role_key`,
  `role_status`,
  `verification_status`,
  `is_primary`,
  `created_at`,
  `updated_at`
)
SELECT
  u.`user_id`,
  'staff',
  'active',
  'verified',
  1,
  COALESCE(u.`created_at`, NOW()),
  COALESCE(u.`updated_at`, NOW())
FROM `users` u
LEFT JOIN `user_roles` ur
  ON ur.`user_id` = u.`user_id`
 AND ur.`role_key` = 'staff'
WHERE u.`email` = 'sample.staff@yamu.com'
  AND ur.`user_role_id` IS NULL;

INSERT INTO `staff_profiles` (
  `user_id`,
  `store_name`,
  `store_owner`,
  `business_registration_number`,
  `store_address`,
  `store_contact_number`,
  `store_email`,
  `verification_status`,
  `verified_at`,
  `created_at`,
  `updated_at`
)
SELECT
  u.`user_id`,
  'Yamu Rental Center',
  'Yamu Rental Center',
  'YAMU-STF-001',
  '155 Galle Road, Colombo 03',
  '0775550100',
  'sample.staff@yamu.com',
  'verified',
  NOW(),
  COALESCE(u.`created_at`, NOW()),
  COALESCE(u.`updated_at`, NOW())
FROM `users` u
LEFT JOIN `staff_profiles` sp ON sp.`user_id` = u.`user_id`
WHERE u.`email` = 'sample.staff@yamu.com'
  AND sp.`staff_profile_id` IS NULL;

UPDATE `staff_profiles` sp
JOIN `users` u ON u.`user_id` = sp.`user_id`
SET
  sp.`store_name` = 'Yamu Rental Center',
  sp.`store_owner` = 'Yamu Rental Center',
  sp.`business_registration_number` = 'YAMU-STF-001',
  sp.`store_address` = '155 Galle Road, Colombo 03',
  sp.`store_contact_number` = '0775550100',
  sp.`store_email` = 'sample.staff@yamu.com',
  sp.`verification_status` = 'verified',
  sp.`verified_at` = COALESCE(sp.`verified_at`, NOW()),
  sp.`updated_at` = NOW()
WHERE u.`email` = 'sample.staff@yamu.com';

UPDATE `vehicles`
SET `owner_user_id` = (
    SELECT u.`user_id`
    FROM `users` u
    WHERE u.`email` = 'sample.staff@yamu.com'
    LIMIT 1
)
WHERE `registration_number` IN ('CAR-1001', 'CAR-1002', 'CAR-1003', 'CAR-1004', 'CAR-1005');

UPDATE `vehicles` v
JOIN `users` admin_users
  ON admin_users.`user_id` = v.`owner_user_id`
 AND admin_users.`role` = 'admin'
JOIN `users` sample_staff
  ON sample_staff.`email` = 'sample.staff@yamu.com'
SET v.`owner_user_id` = sample_staff.`user_id`;

UPDATE `booking`
SET `driver_id` = (
    SELECT u.`user_id`
    FROM `users` u
    WHERE u.`email` = 'sample.staff@yamu.com'
    LIMIT 1
)
WHERE `vehicle_ID` IN (
    SELECT `vehicle_id`
    FROM `vehicles`
    WHERE `registration_number` IN ('CAR-1001', 'CAR-1002', 'CAR-1003', 'CAR-1004', 'CAR-1005')
);

UPDATE `booking` b
JOIN `vehicles` v ON v.`vehicle_id` = b.`vehicle_ID`
JOIN `users` sample_staff
  ON sample_staff.`email` = 'sample.staff@yamu.com'
SET b.`driver_id` = sample_staff.`user_id`
WHERE b.`vehicle_ID` IS NOT NULL
  AND v.`owner_user_id` = sample_staff.`user_id`;
