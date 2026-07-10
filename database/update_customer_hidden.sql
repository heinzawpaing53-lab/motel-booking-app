ALTER TABLE `reservations`
ADD COLUMN `customer_hidden` TINYINT(1) NOT NULL DEFAULT 0 AFTER `special_notes`;
