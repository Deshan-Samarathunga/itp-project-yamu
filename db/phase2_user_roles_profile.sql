ALTER TABLE `users`
  ADD COLUMN `role` ENUM('admin','staff','driver','customer') NOT NULL DEFAULT 'customer' AFTER `password`,
  MODIFY COLUMN `account_status` VARCHAR(20) NOT NULL DEFAULT 'active',
  ADD COLUMN `license_or_nic` VARCHAR(100) DEFAULT NULL AFTER `dob`,
  ADD COLUMN `verification_status` VARCHAR(20) NOT NULL DEFAULT 'verified' AFTER `license_or_nic`,
  ADD COLUMN `bio` LONGTEXT DEFAULT NULL AFTER `verification_status`,
  ADD COLUMN `created_at` DATETIME DEFAULT NULL AFTER `rag_date`,
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL AFTER `created_at`;

UPDATE `users`
SET
  `role` = 'customer',
  `account_status` = CASE
    WHEN `account_status` IN ('0', 'suspended') THEN 'suspended'
    WHEN `account_status` IN ('pending') THEN 'pending'
    ELSE 'active'
  END,
  `verification_status` = 'verified',
  `created_at` = COALESCE(`created_at`, `rag_date`, NOW()),
  `updated_at` = COALESCE(`updated_at`, `rag_date`, NOW());

INSERT INTO `users`
(`username`, `password`, `role`, `full_name`, `email`, `address`, `city`, `phone`, `dob`, `profile_pic`, `account_status`, `rag_date`, `license_or_nic`, `verification_status`, `bio`, `created_at`, `updated_at`)
SELECT
  `username`,
  `password`,
  'admin',
  `name`,
  `email`,
  `address`,
  `city`,
  `phone`,
  '',
  COALESCE(`profile_pic`, 'avatar.png'),
  'active',
  NOW(),
  NULL,
  'verified',
  NULL,
  NOW(),
  NOW()
FROM `admin`
WHERE `email` NOT IN (SELECT `email` FROM `users`);
