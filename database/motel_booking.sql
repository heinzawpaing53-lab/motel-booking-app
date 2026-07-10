-- Create Database
CREATE DATABASE IF NOT EXISTS `motel_booking` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `motel_booking`;

-- 1. ROLES TABLE
CREATE TABLE IF NOT EXISTS `roles` (
    `role_id` INT AUTO_INCREMENT PRIMARY KEY,
    `role_name` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. USERS TABLE
CREATE TABLE IF NOT EXISTS `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `role_id` INT NOT NULL,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
    `nationality` VARCHAR(50) NOT NULL,
    `address` TEXT NOT NULL,
    `profile_image` VARCHAR(255) DEFAULT 'default_user.png',
    `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`role_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. FLOORS TABLE
CREATE TABLE IF NOT EXISTS `floors` (
    `floor_id` INT AUTO_INCREMENT PRIMARY KEY,
    `floor_name` VARCHAR(50) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `sort_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. ROOM_TYPES TABLE
CREATE TABLE IF NOT EXISTS `room_types` (
    `type_id` INT AUTO_INCREMENT PRIMARY KEY,
    `type_name` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT NOT NULL,
    `price_per_night` DECIMAL(10,2) NOT NULL,
    `max_capacity` INT NOT NULL,
    `bed_type` VARCHAR(50) NOT NULL,
    `room_size` VARCHAR(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. ROOMS TABLE
CREATE TABLE IF NOT EXISTS `rooms` (
    `room_id` INT AUTO_INCREMENT PRIMARY KEY,
    `type_id` INT NOT NULL,
    `floor_id` INT NOT NULL,
    `room_number` VARCHAR(10) NOT NULL UNIQUE,
    `room_name` VARCHAR(100) NOT NULL,
    `status` ENUM('Available', 'Occupied', 'Maintenance', 'Reserved', 'Cleaning') DEFAULT 'Available',
    `description` TEXT NULL,
    FOREIGN KEY (`type_id`) REFERENCES `room_types`(`type_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (`floor_id`) REFERENCES `floors`(`floor_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. ROOM_IMAGES TABLE
CREATE TABLE IF NOT EXISTS `room_images` (
    `image_id` INT AUTO_INCREMENT PRIMARY KEY,
    `room_id` INT NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    FOREIGN KEY (`room_id`) REFERENCES `rooms`(`room_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. AMENITIES TABLE
CREATE TABLE IF NOT EXISTS `amenities` (
    `amenity_id` INT AUTO_INCREMENT PRIMARY KEY,
    `amenity_name` VARCHAR(100) NOT NULL UNIQUE,
    `icon` VARCHAR(100) DEFAULT 'fa-tag'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. ROOM_AMENITIES TABLE (Many-to-Many Bridge)
CREATE TABLE IF NOT EXISTS `room_amenities` (
    `room_id` INT NOT NULL,
    `amenity_id` INT NOT NULL,
    PRIMARY KEY (`room_id`, `amenity_id`),
    FOREIGN KEY (`room_id`) REFERENCES `rooms`(`room_id`) ON DELETE CASCADE,
    FOREIGN KEY (`amenity_id`) REFERENCES `amenities`(`amenity_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. SPECIAL_REQUESTS TABLE
CREATE TABLE IF NOT EXISTS `special_requests` (
    `request_id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_name` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. RESERVATIONS TABLE
CREATE TABLE IF NOT EXISTS `reservations` (
    `reservation_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `room_id` INT NOT NULL,
    `check_in_date` DATE NOT NULL,
    `check_out_date` DATE NOT NULL,
    `adults` INT NOT NULL DEFAULT 1,
    `children` INT NOT NULL DEFAULT 0,
    `total_guests` INT NOT NULL,
    `total_nights` INT NOT NULL,
    `room_price` DECIMAL(10,2) NOT NULL,
    `total_price` DECIMAL(10,2) NOT NULL,
    `booking_status` ENUM('Pending', 'Approved', 'Rejected', 'Checked In', 'Checked Out', 'Cancelled', 'Completed') DEFAULT 'Pending',
    `payment_status` ENUM('Unpaid', 'Paid', 'Refunded') DEFAULT 'Unpaid',
    `early_check_in_time` TIME NULL,
    `late_check_out_time` TIME NULL,
    `special_notes` TEXT NULL,
    `customer_hidden` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (`room_id`) REFERENCES `rooms`(`room_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_dates (check_in_date, check_out_date),
    INDEX idx_status (booking_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. RESERVATION_REQUEST TABLE (Many-to-Many Bridge for Special Requests per Booking)
CREATE TABLE IF NOT EXISTS `reservation_requests` (
    `reservation_req_id` INT AUTO_INCREMENT PRIMARY KEY,
    `reservation_id` INT NOT NULL,
    `request_id` INT NOT NULL,
    `remarks` VARCHAR(255) NULL,
    FOREIGN KEY (`reservation_id`) REFERENCES `reservations`(`reservation_id`) ON DELETE CASCADE,
    FOREIGN KEY (`request_id`) REFERENCES `special_requests`(`request_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. SETTINGS TABLE
CREATE TABLE IF NOT EXISTS `settings` (
    `setting_key` VARCHAR(50) PRIMARY KEY,
    `setting_value` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. ACTIVITY LOGS TABLE
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `log_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `action` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Seed Roles
INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'Admin'),
(2, 'Customer')
ON DUPLICATE KEY UPDATE `role_name` = VALUES(`role_name`);

-- Seed Users - All passwords are 'password123' (bcrypt hash)
-- Admin login: admin@luxurymotel.com / password123
INSERT INTO `users` (`user_id`, `role_id`, `first_name`, `last_name`, `email`, `password`, `phone`, `gender`, `nationality`, `address`, `profile_image`, `status`) VALUES
(1, 1, 'System', 'Administrator', 'admin@luxurymotel.com', '$2y$10$I59gNIfVmJDGuz5jEv0JEO1v/K7KJPckiuLF3Z/CbpMrETh1itcle', '+1234567890', 'Other', 'Global', 'HQ Admin Suite 101, City', 'default_user.png', 'Active'),
(2, 2, 'John', 'Smith', 'john.smith@email.com', '$2y$10$I59gNIfVmJDGuz5jEv0JEO1v/K7KJPckiuLF3Z/CbpMrETh1itcle', '+1-555-0101', 'Male', 'American', '123 Oak Street, Los Angeles, CA', 'default_user.png', 'Active'),
(3, 2, 'Sarah', 'Johnson', 'sarah.j@email.com', '$2y$10$I59gNIfVmJDGuz5jEv0JEO1v/K7KJPckiuLF3Z/CbpMrETh1itcle', '+1-555-0102', 'Female', 'Canadian', '456 Maple Ave, Toronto, ON', 'default_user.png', 'Active'),
(4, 2, 'Mohammed', 'Ali', 'm.ali@email.com', '$2y$10$I59gNIfVmJDGuz5jEv0JEO1v/K7KJPckiuLF3Z/CbpMrETh1itcle', '+1-555-0103', 'Male', 'Egyptian', '789 Palm Drive, Cairo', 'default_user.png', 'Active'),
(5, 2, 'Emily', 'Brown', 'emily.b@email.com', '$2y$10$I59gNIfVmJDGuz5jEv0JEO1v/K7KJPckiuLF3Z/CbpMrETh1itcle', '+1-555-0104', 'Female', 'British', '55 London Road, London, UK', 'default_user.png', 'Active'),
(6, 2, 'Carlos', 'Garcia', 'carlos.g@email.com', '$2y$10$I59gNIfVmJDGuz5jEv0JEO1v/K7KJPckiuLF3Z/CbpMrETh1itcle', '+1-555-0105', 'Male', 'Spanish', 'Calle Mayor 10, Madrid, Spain', 'default_user.png', 'Active'),
(7, 2, 'Yuki', 'Tanaka', 'yuki.t@email.com', '$2y$10$I59gNIfVmJDGuz5jEv0JEO1v/K7KJPckiuLF3Z/CbpMrETh1itcle', '+1-555-0106', 'Female', 'Japanese', '1-1-1 Shibuya, Tokyo, Japan', 'default_user.png', 'Active'),
(8, 2, 'David', 'Wilson', 'david.w@email.com', '$2y$10$I59gNIfVmJDGuz5jEv0JEO1v/K7KJPckiuLF3Z/CbpMrETh1itcle', '+1-555-0107', 'Male', 'Australian', '100 George St, Sydney, AU', 'default_user.png', 'Inactive')
ON DUPLICATE KEY UPDATE `email` = VALUES(`email`);

-- Seed Floors
INSERT INTO `floors` (`floor_id`, `floor_name`, `description`, `sort_order`) VALUES
(1, 'Ground Floor', 'Ground level rooms with easy access', 1),
(2, '1st Floor', 'First floor rooms', 2),
(3, '2nd Floor', 'Second floor rooms', 3),
(4, '3rd Floor', 'Third floor rooms with panoramic views', 4)
ON DUPLICATE KEY UPDATE `floor_name` = VALUES(`floor_name`);

-- Seed Room Types
INSERT INTO `room_types` (`type_id`, `type_name`, `description`, `price_per_night`, `max_capacity`, `bed_type`, `room_size`) VALUES
(1, 'Standard Room', 'Comfortable standard room with essential amenities. Perfect for budget-conscious travelers seeking quality accommodation.', 1200.00, 2, 'Queen Bed', '25 sqm'),
(2, 'Deluxe Room', 'Spacious deluxe room with premium furnishings and marble bathroom. Enjoy upgraded amenities and elegant decor.', 1800.00, 3, 'King Bed', '35 sqm'),
(3, 'Superior Room', 'Superior room with excellent views and premium bedding. Features a dedicated work desk and seating area.', 1500.00, 2, 'Queen Bed', '30 sqm'),
(4, 'Family Room', 'Large family room with two queen beds. Ideal for families of up to 5 with separate living space.', 2500.00, 5, '2 Queen Beds', '45 sqm'),
(5, 'Suite Room', 'Luxury suite with separate living room and dining area. Features premium amenities and jacuzzi bathroom.', 3500.00, 4, 'King Bed + Sofa', '55 sqm'),
(6, 'VIP Room', 'Exclusive VIP room with private terrace and butler service. The ultimate luxury experience with panoramic views.', 5000.00, 2, 'Emperor King Bed', '60 sqm')
ON DUPLICATE KEY UPDATE `type_name` = VALUES(`type_name`);

-- Seed Rooms
INSERT INTO `rooms` (`room_id`, `type_id`, `floor_id`, `room_number`, `room_name`, `status`, `description`) VALUES
(1, 1, 1, 'G01', 'Standard Ground 1', 'Available', 'Cozy standard room on ground floor with garden view. Quiet and convenient.'),
(2, 1, 1, 'G02', 'Standard Ground 2', 'Available', 'Comfortable standard room with easy access to lobby and restaurant.'),
(3, 1, 1, 'G03', 'Standard Garden View', 'Available', 'Standard room overlooking our beautiful garden courtyard.'),
(4, 2, 1, 'G04', 'Deluxe Ground Suite', 'Occupied', 'Spacious deluxe room with premium king bed and marble bathroom. Currently occupied.'),
(5, 2, 2, '101', 'Deluxe 1st Front', 'Available', 'Deluxe room facing the front with city views and premium furnishings.'),
(6, 2, 2, '102', 'Deluxe 1st Corner', 'Reserved', 'Corner deluxe room with windows on two sides. Reserved for upcoming guest.'),
(7, 3, 2, '103', 'Superior 1st 1', 'Available', 'Superior room with queen bed, work desk, and excellent natural lighting.'),
(8, 3, 2, '104', 'Superior 1st 2', 'Maintenance', 'Superior room currently under maintenance. Fresh paint and new furniture being installed.'),
(9, 3, 2, '105', 'Superior Garden View', 'Available', 'Superior room with balcony overlooking the garden and pool area.'),
(10, 4, 3, '201', 'Family Room 2nd', 'Available', 'Large family room with two queen beds. Perfect for families of up to 5.'),
(11, 4, 3, '202', 'Family Suite', 'Available', 'Spacious family suite with separate sleeping area for children.'),
(12, 5, 3, '203', 'Suite Room 2nd', 'Cleaning', 'Elegant suite with separate living room, dining area, and premium amenities. Being cleaned.'),
(13, 5, 3, '204', 'Executive Suite', 'Available', 'Executive suite with panoramic views, work area, and luxury bathroom with jacuzzi.'),
(14, 6, 4, '301', 'VIP Room 3rd', 'Available', 'Our flagship VIP room with emperor king bed, private terrace, and butler service.'),
(15, 6, 4, '302', 'Presidential VIP', 'Available', 'The ultimate luxury. Presidential suite with private sauna, terrace, and 24/7 concierge.'),
(16, 1, 1, 'G05', 'Standard Garden Wing', 'Available', 'Standard room in the garden wing with patio access and lush views.'),
(17, 3, 2, '106', 'Superior View Room', 'Available', 'Superior room with elevated garden and city views.'),
(18, 5, 3, '205', 'Suite Corner', 'Available', 'Corner suite with panoramic dual-aspect views and separate lounge.'),
(19, 3, 3, '206', 'Superior Suite', 'Available', 'Superior suite on the second floor with extra seating and work area.'),
(20, 6, 4, '303', 'VIP Penthouse', 'Available', 'Exclusive VIP penthouse with private rooftop terrace and skyline panorama.')
ON DUPLICATE KEY UPDATE `room_number` = VALUES(`room_number`);

-- Seed Room Images
INSERT INTO `room_images` (`image_id`, `room_id`, `image_path`) VALUES
(1, 1, 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=800'),
(2, 1, 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?w=800'),
(3, 2, 'https://images.unsplash.com/photo-1587985064135-0366536eab42?w=800'),
(4, 2, 'https://images.unsplash.com/photo-1595576508898-0ad5c879a061?w=800'),
(5, 3, 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=800'),
(6, 3, 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800'),
(7, 4, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800'),
(8, 4, 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=800'),
(9, 5, 'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=800'),
(10, 5, 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800'),
(11, 6, 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=800'),
(12, 7, 'https://images.unsplash.com/photo-1584132967334-10e028bd69f7?w=800'),
(13, 7, 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=800'),
(14, 10, 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?w=800'),
(15, 10, 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800'),
(16, 14, 'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=800'),
(17, 14, 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800')
ON DUPLICATE KEY UPDATE `image_path` = VALUES(`image_path`);

-- Seed Amenities
INSERT INTO `amenities` (`amenity_id`, `amenity_name`, `icon`) VALUES
(1, 'Free WiFi', 'fa-wifi'),
(2, 'Air Conditioning', 'fa-snowflake'),
(3, 'Flat Screen TV', 'fa-tv'),
(4, 'Mini Bar', 'fa-glass-martini-alt'),
(5, 'Room Service', 'fa-concierge-bell'),
(6, 'Safe Deposit Box', 'fa-lock'),
(7, 'Coffee Maker', 'fa-coffee'),
(8, 'Work Desk', 'fa-briefcase')
ON DUPLICATE KEY UPDATE `amenity_name` = VALUES(`amenity_name`);

-- Seed Room Amenities (Many-to-Many)
INSERT IGNORE INTO `room_amenities` (`room_id`, `amenity_id`) VALUES
-- Standard rooms (1-3)
(1,1),(1,2),(1,3),(1,7),(1,8),
(2,1),(2,2),(2,3),(2,7),(2,8),
(3,1),(3,2),(3,3),(3,7),(3,8),
-- Deluxe rooms (4-6) - all amenities
(4,1),(4,2),(4,3),(4,4),(4,5),(4,6),(4,7),(4,8),
(5,1),(5,2),(5,3),(5,4),(5,5),(5,6),(5,7),(5,8),
(6,1),(6,2),(6,3),(6,4),(6,5),(6,6),(6,7),(6,8),
-- Superior rooms (7-9)
(7,1),(7,2),(7,3),(7,5),(7,7),(7,8),
(8,1),(8,2),(8,3),(8,5),(8,7),(8,8),
(9,1),(9,2),(9,3),(9,5),(9,7),(9,8),
-- Family rooms (10-11)
(10,1),(10,2),(10,3),(10,5),(10,7),(10,8),
(11,1),(11,2),(11,3),(11,5),(11,7),(11,8),
-- Suites (12-13) - all amenities
(12,1),(12,2),(12,3),(12,4),(12,5),(12,6),(12,7),(12,8),
(13,1),(13,2),(13,3),(13,4),(13,5),(13,6),(13,7),(13,8),
-- VIP rooms (14-15) - all amenities
(14,1),(14,2),(14,3),(14,4),(14,5),(14,6),(14,7),(14,8),
(15,1),(15,2),(15,3),(15,4),(15,5),(15,6),(15,7),(15,8);

-- Seed Special Requests
INSERT INTO `special_requests` (`request_id`, `request_name`, `description`, `active`) VALUES
(1, 'High Floor', 'Room on a higher floor for better views', 1),
(2, 'Lower Floor', 'Room on a lower floor for easy access', 1),
(3, 'Near Elevator', 'Room located close to the elevator', 1),
(4, 'Quiet Room', 'Quiet room away from elevators and noise', 1),
(5, 'Non-Smoking', 'Non-smoking room required', 1),
(6, 'Smoking', 'Smoking room required', 1),
(7, 'Twin Beds', 'Room with twin beds instead of double', 1),
(8, 'King Bed', 'Room with a king size bed', 1),
(9, 'Extra Pillow', 'Extra pillows for the room', 1),
(10, 'Extra Blanket', 'Extra blankets for the room', 1),
(11, 'Baby Cot', 'Baby cot / crib for infant', 1),
(12, 'Wheelchair Accessible', 'Wheelchair accessible room required', 1),
(13, 'Birthday Decoration', 'Birthday decoration setup in room', 1),
(14, 'Anniversary Decoration', 'Anniversary decoration setup in room', 1)
ON DUPLICATE KEY UPDATE `request_name` = VALUES(`request_name`);

-- Seed Reservations (using relative dates)
INSERT INTO `reservations` (`reservation_id`, `user_id`, `room_id`, `check_in_date`, `check_out_date`, `adults`, `children`, `total_guests`, `total_nights`, `room_price`, `total_price`, `booking_status`, `payment_status`, `early_check_in_time`, `late_check_out_time`, `special_notes`, `created_at`) VALUES
-- John Smith - completed stay
(1, 2, 5, DATE_ADD(CURDATE(), INTERVAL -10 DAY), DATE_ADD(CURDATE(), INTERVAL -8 DAY), 2, 0, 2, 2, 1800.00, 3600.00, 'Completed', 'Paid', NULL, NULL, 'Great stay! Would love to come again.', DATE_ADD(CURDATE(), INTERVAL -20 DAY)),
-- Sarah Johnson - currently checked in
(2, 3, 4, DATE_ADD(CURDATE(), INTERVAL -2 DAY), DATE_ADD(CURDATE(), INTERVAL 1 DAY), 2, 1, 3, 3, 1800.00, 5400.00, 'Checked In', 'Paid', '12:00:00', NULL, 'Traveling with toddler. Early check-in requested.', DATE_ADD(CURDATE(), INTERVAL -10 DAY)),
-- Mohammed Ali - approved upcoming
(3, 4, 14, DATE_ADD(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 8 DAY), 2, 0, 2, 3, 5000.00, 15000.00, 'Approved', 'Unpaid', NULL, '18:00:00', 'Anniversary celebration! Late checkout at 18:00.', DATE_ADD(CURDATE(), INTERVAL -3 DAY)),
-- Emily Brown - pending approval
(4, 5, 7, DATE_ADD(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 13 DAY), 1, 0, 1, 3, 1500.00, 4500.00, 'Pending', 'Unpaid', '11:00:00', '15:00:00', 'Business trip. Early check-in 11:00, late checkout 15:00.', DATE_ADD(CURDATE(), INTERVAL -1 DAY)),
-- Carlos Garcia - rejected
(5, 6, 10, DATE_ADD(CURDATE(), INTERVAL -5 DAY), DATE_ADD(CURDATE(), INTERVAL -3 DAY), 2, 3, 5, 2, 2500.00, 5000.00, 'Rejected', 'Refunded', NULL, NULL, '', DATE_ADD(CURDATE(), INTERVAL -15 DAY)),
-- Yuki Tanaka - cancelled
(6, 7, 3, DATE_ADD(CURDATE(), INTERVAL 2 DAY), DATE_ADD(CURDATE(), INTERVAL 4 DAY), 1, 0, 1, 2, 1200.00, 2400.00, 'Cancelled', 'Refunded', NULL, NULL, 'Sorry, change of travel plans.', DATE_ADD(CURDATE(), INTERVAL -5 DAY)),
-- John Smith - another upcoming booking
(7, 2, 9, DATE_ADD(CURDATE(), INTERVAL 20 DAY), DATE_ADD(CURDATE(), INTERVAL 25 DAY), 2, 0, 2, 5, 1500.00, 7500.00, 'Pending', 'Unpaid', NULL, NULL, 'Business trip - need quiet room with good desk.', DATE_ADD(CURDATE(), INTERVAL -2 DAY)),
-- Sarah Johnson - past completed
(8, 3, 1, DATE_ADD(CURDATE(), INTERVAL -30 DAY), DATE_ADD(CURDATE(), INTERVAL -28 DAY), 2, 0, 2, 2, 1200.00, 2400.00, 'Completed', 'Paid', NULL, NULL, '', DATE_ADD(CURDATE(), INTERVAL -35 DAY)),
-- Mohammed Ali - recently checked out
(9, 4, 11, DATE_ADD(CURDATE(), INTERVAL -4 DAY), DATE_ADD(CURDATE(), INTERVAL -1 DAY), 2, 2, 4, 3, 2500.00, 7500.00, 'Checked Out', 'Paid', NULL, '14:00:00', 'Late checkout approved. Family enjoyed the stay!', DATE_ADD(CURDATE(), INTERVAL -10 DAY));

-- Seed Reservation Special Requests
INSERT INTO `reservation_requests` (`reservation_id`, `request_id`, `remarks`) VALUES
(1, 5, 'Non-smoking room preferred'),
(1, 8, 'King bed if available'),
(2, 5, 'Non-smoking - traveling with baby'),
(2, 12, 'Need wheelchair accessible path for stroller'),
(3, 9, 'Extra pillows for anniversary setup'),
(3, 14, 'Anniversary decoration - roses and chocolates'),
(4, 5, 'Non-smoking floor'),
(4, 3, 'Near elevator for quick access'),
(4, 9, 'Extra pillows'),
(5, 6, 'Smoking room requested'),
(5, 10, 'Extra blankets'),
(7, 5, 'Non-smoking'),
(7, 7, 'Work desk with good lighting'),
(8, 5, 'Non-smoking'),
(9, 14, 'Anniversary decoration setup');

-- Seed Activity Logs
INSERT INTO `activity_logs` (`user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES
(1, 'Login', 'Admin logged in to dashboard', '192.168.1.1', DATE_ADD(CURDATE(), INTERVAL -1 DAY)),
(2, 'Registration', 'New user registered', '192.168.1.10', DATE_ADD(CURDATE(), INTERVAL -25 DAY)),
(2, 'New Reservation', 'Created reservation #1', '192.168.1.10', DATE_ADD(CURDATE(), INTERVAL -20 DAY)),
(1, 'Update Booking Status', 'Approved reservation #1', '192.168.1.1', DATE_ADD(CURDATE(), INTERVAL -19 DAY)),
(1, 'Check-in', 'Checked in reservation #1', '192.168.1.1', DATE_ADD(CURDATE(), INTERVAL -10 DAY)),
(1, 'Check-out', 'Checked out reservation #1', '192.168.1.1', DATE_ADD(CURDATE(), INTERVAL -8 DAY)),
(3, 'Registration', 'New user registered', '192.168.1.20', DATE_ADD(CURDATE(), INTERVAL -15 DAY)),
(3, 'New Reservation', 'Created reservation #2', '192.168.1.20', DATE_ADD(CURDATE(), INTERVAL -10 DAY)),
(1, 'Update Booking Status', 'Approved reservation #2 with early check-in', '192.168.1.1', DATE_ADD(CURDATE(), INTERVAL -9 DAY)),
(1, 'Check-in', 'Checked in reservation #2', '192.168.1.1', DATE_ADD(CURDATE(), INTERVAL -2 DAY)),
(4, 'Registration', 'New user registered', '192.168.1.30', DATE_ADD(CURDATE(), INTERVAL -7 DAY)),
(4, 'New Reservation', 'Created reservation #3', '192.168.1.30', DATE_ADD(CURDATE(), INTERVAL -3 DAY)),
(1, 'Update Booking Status', 'Approved reservation #3', '192.168.1.1', DATE_ADD(CURDATE(), INTERVAL -2 DAY)),
(5, 'Registration', 'New user registered', '192.168.1.40', DATE_ADD(CURDATE(), INTERVAL -5 DAY)),
(5, 'New Reservation', 'Created reservation #4', '192.168.1.40', DATE_ADD(CURDATE(), INTERVAL -1 DAY)),
(1, 'Update Booking Status', 'Rejected reservation #5 - dates unavailable', '192.168.1.1', DATE_ADD(CURDATE(), INTERVAL -12 DAY)),
(6, 'Registration', 'New user registered', '192.168.1.50', DATE_ADD(CURDATE(), INTERVAL -20 DAY)),
(7, 'Registration', 'New user registered', '192.168.1.60', DATE_ADD(CURDATE(), INTERVAL -8 DAY)),
(7, 'New Reservation', 'Created reservation #6 then cancelled', '192.168.1.60', DATE_ADD(CURDATE(), INTERVAL -5 DAY)),
(2, 'New Reservation', 'Created reservation #7', '192.168.1.10', DATE_ADD(CURDATE(), INTERVAL -2 DAY)),
(1, 'Login', 'Admin logged in to dashboard', '192.168.1.1', DATE_ADD(CURDATE(), INTERVAL 0 DAY));

-- Seed Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'Luxury Motel'),
('site_email', 'info@luxurymotel.com'),
('site_phone', '+1234567890'),
('site_address', '123 Luxury Street, City'),
('check_in_time', '14:00'),
('check_out_time', '12:00'),
('tax_rate', '10'),
('currency', 'USD'),
('max_guests_per_room', '5')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);
