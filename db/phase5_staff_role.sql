ALTER TABLE `users`
  MODIFY COLUMN `role` ENUM('admin','staff','driver','customer') NOT NULL DEFAULT 'customer';
