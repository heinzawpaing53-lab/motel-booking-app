<?php
/**
 * Luxury Motel Booking System - Database Seeder
 * Run this script to reset and re-seed the database with sample data.
 * Access via: http://localhost/motel-app/database/seed.php
 * Or run: php database/seed.php
 */

require_once __DIR__ . '/../config/db.php';

echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>Database Seeder</title>";
echo "<script src='https://cdn.tailwindcss.com'></script>";
echo "<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css'>";
echo "</head><body class='bg-gray-100 p-8'>";
echo "<div class='max-w-3xl mx-auto'>";
echo "<div class='bg-white rounded-xl shadow-sm p-8 mb-6'>";
echo "<h1 class='text-2xl font-bold mb-2'><i class='fas fa-seedling text-green-600 mr-2'></i>Database Seeder</h1>";
echo "<p class='text-gray-500 mb-6'>Reseeding the <code class='bg-gray-100 px-2 py-1 rounded'>motel_booking</code> database...</p>";

$messages = [];

try {
    // Create tables that may not exist yet (activity_logs is used by the codebase
    // but may not be in the user's original schema)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `activity_logs` (
        `log_id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT DEFAULT NULL,
        `action` VARCHAR(255) NOT NULL,
        `description` TEXT DEFAULT NULL,
        `ip_address` VARCHAR(45) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $messages[] = "<span class='text-blue-600'><i class='fas fa-plus-circle mr-1'></i></span> Created `activity_logs` table";

    // Add column if missing from existing schema (e.g., `icon` was missing from `amenities`)
    try {
        $pdo->exec("ALTER TABLE `amenities` ADD COLUMN `icon` VARCHAR(100) DEFAULT 'fa-tag' AFTER `amenity_name`");
        $messages[] = "<span class='text-green-600'><i class='fas fa-check-circle mr-1'></i></span> Added `icon` column to `amenities`";
    } catch (PDOException $e) {
        // Column already exists — ignore
    }

    // Migrate payments table columns to match PHP code expectations
    try {
        $pdo->exec("ALTER TABLE `payments` CHANGE COLUMN `amount` `amount_paid` DECIMAL(10,2) NOT NULL");
        $messages[] = "<span class='text-green-600'><i class='fas fa-check-circle mr-1'></i></span> Renamed `payments.amount` → `amount_paid`";
    } catch (PDOException $e) {
        // Column may already be renamed
    }
    try {
        $pdo->exec("ALTER TABLE `payments` CHANGE COLUMN `status` `payment_status` ENUM('Completed','Pending','Failed','Refunded') DEFAULT 'Completed'");
        $messages[] = "<span class='text-green-600'><i class='fas fa-check-circle mr-1'></i></span> Renamed `payments.status` → `payment_status`";
    } catch (PDOException $e) {
        // Column may already be renamed
    }
    try {
        $pdo->exec("ALTER TABLE `payments` ADD COLUMN `transaction_reference` VARCHAR(100) DEFAULT NULL AFTER `payment_method`");
        $messages[] = "<span class='text-green-600'><i class='fas fa-check-circle mr-1'></i></span> Added `transaction_reference` column to `payments`";
    } catch (PDOException $e) {
        // Column already exists
    }

    // Disable foreign key checks for truncation
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Truncate all tables in reverse dependency order
    // Wrap each in try-catch so missing tables don't block the seeder
    $tables = [
        'activity_logs',
        'reservation_requests',
        'payments',
        'reservations',
        'room_amenities',
        'room_images',
        'rooms',
        'room_types',
        'floors',
        'users',
        'roles',
        'special_requests',
        'amenities',
        'settings',
    ];

    foreach ($tables as $table) {
        try {
            $pdo->exec("TRUNCATE TABLE `$table`");
            $messages[] = "<span class='text-green-600'><i class='fas fa-check-circle mr-1'></i></span> Truncated `$table`";
        } catch (PDOException $e) {
            $messages[] = "<span class='text-yellow-600'><i class='fas fa-exclamation-circle mr-1'></i></span> Skipped `$table` (not found)";
        }
    }

    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // ---------------------------------------------------------
    // 1. SEED ROLES
    // ---------------------------------------------------------
    $pdo->exec("INSERT INTO `roles` (`role_id`, `role_name`) VALUES
        (1, 'Admin'),
        (2, 'Customer')
        ON DUPLICATE KEY UPDATE `role_name` = VALUES(`role_name`)");
    $messages[] = "<span class='text-green-600'><i class='fas fa-check-circle mr-1'></i></span> Seeded `roles`";

    // ---------------------------------------------------------
    // 2. SEED USERS
    // ---------------------------------------------------------
    // Admin password: password123
    $pdo->exec("INSERT INTO `users` (`user_id`, `role_id`, `first_name`, `last_name`, `email`, `password`, `phone`, `gender`, `nationality`, `address`, `status`) VALUES
        (1, 1, 'System', 'Administrator', 'admin@luxurymotel.com', '\$2y\$10\$I59gNIfVmJDGuz5jEv0JEO1v/K7KJPckiuLF3Z/CbpMrETh1itcle', '+1234567890', 'Other', 'Global', 'HQ Admin Suite 101, City', 'Active'),
        (2, 2, 'Jane', 'Doe', 'jane.doe@example.com', '\$2y\$10\$CegoPydrwzNQ/t7U7cDhhegJNWvbTET0czitXSPxczA2MMVs1tIoC', '+1987654321', 'Female', 'American', '456 Guest Lane, California', 'Active')
        ON DUPLICATE KEY UPDATE `email` = VALUES(`email`)");
    $messages[] = "<span class='text-green-600'><i class='fas fa-check-circle mr-1'></i></span> Seeded `users` (Admin: admin@luxurymotel.com / password123, Customer: jane.doe@example.com / Guest@Luxury2026)";

    // ---------------------------------------------------------
    // 3. SEED FLOORS
    // ---------------------------------------------------------
    $pdo->exec("INSERT INTO `floors` (`floor_id`, `floor_name`, `description`, `sort_order`) VALUES
        (1, 'Ground Floor', 'Easy access, close to the lobby and pool area.', 1),
        (2, 'First Floor', 'Standard quiet rooms with courtyard views.', 2),
        (3, 'Second Floor', 'Premium height rooms offering clear skyline views.', 3)
        ON DUPLICATE KEY UPDATE `floor_name` = VALUES(`floor_name`)");
    $messages[] = "<span class='text-green-600'><i class='fas fa-check-circle mr-1'></i></span> Seeded `floors`";

    // ---------------------------------------------------------
    // 4. SEED ROOM_TYPES
    // ---------------------------------------------------------
    $pdo->exec("INSERT INTO `room_types` (`type_id`, `type_name`, `description`, `price_per_night`, `max_capacity`, `bed_type`, `room_size`) VALUES
        (1, 'Standard Room', 'A cozy, budget-friendly room with essential amenities perfect for solo travelers or couples.', 75.00, 2, '1 Queen Bed', '24 sqm'),
        (2, 'Deluxe Suite', 'Spacious room featuring a separate seating area, premium layout, and luxury finishes.', 135.00, 3, '1 King Bed', '38 sqm'),
        (3, 'Family Executive', 'Designed for families, offering multiple bedding setups and an expansive bathroom layout.', 190.00, 5, '2 Queen Beds + 1 Sofa Bed', '52 sqm')
        ON DUPLICATE KEY UPDATE `type_name` = VALUES(`type_name`)");
    $messages[] = "<span class='text-green-600'><i class='fas fa-check-circle mr-1'></i></span> Seeded `room_types`";

    // ---------------------------------------------------------
    // 5. SEED ROOMS
    // ---------------------------------------------------------
    $pdo->exec("INSERT INTO `rooms` (`room_id`, `type_id`, `floor_id`, `room_number`, `room_name`, `status`, `description`) VALUES
        -- Ground Floor Rooms
        (1, 1, 1, '101', 'G-Standard 101', 'Available', 'Close to the main entrance and reception.'),
        (2, 1, 1, '102', 'G-Standard 102', 'Cleaning', 'Currently being serviced by housekeeping.'),
        (3, 2, 1, '103', 'G-Deluxe 103', 'Available', 'Poolside access view.'),
        (4, 3, 1, '104', 'G-Family 104', 'Available', 'Family room on ground floor steps from the garden.'),
        (5, 1, 1, '105', 'G-Standard 105', 'Available', 'Standard room with easy lobby and restaurant access.'),
        -- First Floor Rooms
        (6, 1, 2, '201', 'F1-Standard 201', 'Reserved', 'Quiet room at the end of the hall.'),
        (7, 2, 2, '202', 'F1-Deluxe 202', 'Occupied', 'Occupied by guest.'),
        (8, 3, 2, '203', 'F1-Family 203', 'Available', 'Spacious floor plan next to the garden overlook.'),
        (9, 1, 2, '204', 'F1-Standard 204', 'Available', 'Standard room with a peaceful courtyard view.'),
        (10, 2, 2, '205', 'F1-Deluxe 205', 'Available', 'Deluxe corner suite with extra windows and natural light.'),
        (11, 3, 2, '206', 'F1-Family 206', 'Available', 'Large family room ideal for groups with connecting door option.'),
        -- Second Floor Rooms
        (12, 2, 3, '301', 'F2-Deluxe 301', 'Available', 'High ceiling option with panoramic balcony view.'),
        (13, 3, 3, '302', 'F2-Family 302', 'Maintenance', 'AC unit maintenance ongoing.'),
        (14, 1, 3, '303', 'F2-Standard 303', 'Available', 'Standard room with an elevated skyline view.'),
        (15, 2, 3, '304', 'F2-Deluxe 304', 'Available', 'Premium deluxe room with private balcony access.'),
        (16, 3, 3, '305', 'F2-Family 305', 'Available', 'Top-floor family suite with panoramic city panorama.'),
        (17, 1, 3, '306', 'F2-Standard 306', 'Available', 'Cozy standard room near the sky lounge.'),
        (18, 2, 3, '307', 'F2-Deluxe 307', 'Available', 'Executive deluxe with dedicated work area and lounge chair.'),
        (19, 3, 3, '308', 'F2-Family 308', 'Available', 'Spacious family executive with premium bath amenities.'),
        (20, 1, 3, '309', 'F2-Standard 309', 'Available', 'Peaceful standard room away from elevator noise.')
        ON DUPLICATE KEY UPDATE `room_number` = VALUES(`room_number`)");
    $messages[] = "<span class='text-green-600'><i class='fas fa-check-circle mr-1'></i></span> Seeded `rooms`";

    // ---------------------------------------------------------
    // 6. SEED AMENITIES
    // ---------------------------------------------------------
    $pdo->exec("INSERT INTO `amenities` (`amenity_id`, `amenity_name`, `icon`) VALUES
        (1, 'High-Speed Wi-Fi', 'fa-wifi'),
        (2, 'Smart TV with Netflix', 'fa-tv'),
        (3, 'Air Conditioning', 'fa-snowflake'),
        (4, 'Mini Bar Fridge', 'fa-glass-martini-alt'),
        (5, 'In-room Electronic Safe', 'fa-lock'),
        (6, 'Espresso Coffee Machine', 'fa-coffee')
        ON DUPLICATE KEY UPDATE `amenity_name` = VALUES(`amenity_name`)");
    $messages[] = "<span class='text-green-600'><i class='fas fa-check-circle mr-1'></i></span> Seeded `amenities`";

    // ---------------------------------------------------------
    // 7. MAP ROOM AMENITIES
    // ---------------------------------------------------------
    $pdo->exec("INSERT IGNORE INTO `room_amenities` (`room_id`, `amenity_id`) VALUES
        -- Ground Floor
        (1, 1), (1, 3),
        (2, 1), (2, 3),
        (3, 1), (3, 2), (3, 3), (3, 4),
        (4, 1), (4, 2), (4, 3), (4, 4),
        (5, 1), (5, 3),
        -- First Floor
        (6, 1), (6, 3),
        (7, 1), (7, 2), (7, 3), (7, 4), (7, 5), (7, 6),
        (8, 1), (8, 2), (8, 3), (8, 4),
        (9, 1), (9, 3),
        (10, 1), (10, 2), (10, 3), (10, 4), (10, 5),
        (11, 1), (11, 2), (11, 3), (11, 4), (11, 6),
        -- Second Floor
        (12, 1), (12, 2), (12, 3), (12, 4), (12, 5), (12, 6),
        (14, 1), (14, 3),
        (15, 1), (15, 2), (15, 3), (15, 4), (15, 5),
        (16, 1), (16, 2), (16, 3), (16, 4), (16, 5), (16, 6),
        (17, 1), (17, 3),
        (18, 1), (18, 2), (18, 3), (18, 4), (18, 5),
        (19, 1), (19, 2), (19, 3), (19, 4), (19, 6),
        (20, 1), (20, 3)");
    $messages[] = "<span class='text-green-600'><i class='fas fa-check-circle mr-1'></i></span> Seeded `room_amenities`";

    // ---------------------------------------------------------
    // 8. SEED SPECIAL_REQUESTS
    // ---------------------------------------------------------
    $pdo->exec("INSERT INTO `special_requests` (`request_id`, `request_name`, `description`, `active`) VALUES
        (1, 'Quiet Room', 'Away from elevators, stairwells, and mechanical rooms.', 1),
        (2, 'Extra Blankets', 'Provide additional heavy linens during room prep.', 1),
        (3, 'Non-Smoking Room', 'Strict requirement for allergen-free environment.', 1),
        (4, 'Baby Cot / Crib', 'Install complimentary infant bedding crib inside the room.', 1)
        ON DUPLICATE KEY UPDATE `request_name` = VALUES(`request_name`)");
    $messages[] = "<span class='text-green-600'><i class='fas fa-check-circle mr-1'></i></span> Seeded `special_requests`";

    // ---------------------------------------------------------
    // 9. SEED SAMPLE RESERVATIONS
    // ---------------------------------------------------------
    $pdo->exec("INSERT INTO `reservations` (`reservation_id`, `user_id`, `room_id`, `check_in_date`, `check_out_date`, `adults`, `children`, `total_guests`, `total_nights`, `room_price`, `total_price`, `booking_status`, `payment_status`, `special_notes`, `created_at`) VALUES
        (1, 2, 5, DATE_ADD(CURDATE(), INTERVAL -4 DAY), DATE_ADD(CURDATE(), INTERVAL -1 DAY), 2, 0, 2, 3, 135.00, 405.00, 'Checked Out', 'Paid', 'Loved the Deluxe Suite! Will return.', DATE_ADD(CURDATE(), INTERVAL -10 DAY)),
        (2, 2, 3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 2, 0, 2, 2, 135.00, 270.00, 'Approved', 'Unpaid', 'Poolside view requested.', DATE_ADD(CURDATE(), INTERVAL -2 DAY)),
        (3, 2, 1, DATE_ADD(CURDATE(), INTERVAL 14 DAY), DATE_ADD(CURDATE(), INTERVAL 17 DAY), 1, 0, 1, 3, 75.00, 225.00, 'Pending', 'Unpaid', 'Business trip - need a quiet room.', DATE_ADD(CURDATE(), INTERVAL -1 DAY))");
    $messages[] = "<span class='text-green-600'><i class='fas fa-check-circle mr-1'></i></span> Seeded `reservations`";

    // ---------------------------------------------------------
    // 10. SEED RESERVATION REQUESTS
    // ---------------------------------------------------------
    $pdo->exec("INSERT INTO `reservation_requests` (`reservation_id`, `request_id`, `remarks`) VALUES
        (1, 3, 'Non-smoking required'),
        (1, 2, 'Extra blankets please'),
        (2, 1, 'Quiet room away from elevator'),
        (3, 1, 'Need absolute quiet for work calls')");
    $messages[] = "<span class='text-green-600'><i class='fas fa-check-circle mr-1'></i></span> Seeded `reservation_requests`";

    // ---------------------------------------------------------
    // 11. SEED ACTIVITY LOGS
    // ---------------------------------------------------------
    $pdo->exec("INSERT INTO `activity_logs` (`user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES
        (1, 'Login', 'Admin logged in', '127.0.0.1', DATE_ADD(CURDATE(), INTERVAL -2 DAY)),
        (2, 'Registration', 'New customer registered', '192.168.1.50', DATE_ADD(CURDATE(), INTERVAL -15 DAY)),
        (2, 'New Reservation', 'Booked Deluxe Suite #202', '192.168.1.50', DATE_ADD(CURDATE(), INTERVAL -10 DAY)),
        (1, 'Update Booking Status', 'Approved reservation #2', '127.0.0.1', DATE_ADD(CURDATE(), INTERVAL -1 DAY))");
    $messages[] = "<span class='text-green-600'><i class='fas fa-check-circle mr-1'></i></span> Seeded `activity_logs`";

    // ---------------------------------------------------------
    // 12. SEED SETTINGS
    // ---------------------------------------------------------
    $pdo->exec("INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
        ('site_name', 'Luxury Motel'),
        ('site_email', 'info@luxurymotel.com'),
        ('site_phone', '+1234567890'),
        ('site_address', '123 Luxury Street, City'),
        ('check_in_time', '14:00'),
        ('check_out_time', '12:00'),
        ('tax_rate', '10'),
        ('currency', 'USD'),
        ('max_guests_per_room', '5')
        ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)");
    $messages[] = "<span class='text-green-600'><i class='fas fa-check-circle mr-1'></i></span> Seeded `settings`";

    // ---------------------------------------------------------
    // Summary
    // ---------------------------------------------------------
    echo "<div class='bg-green-50 border-l-4 border-green-500 text-green-800 p-4 mb-6 rounded-lg font-semibold'>";
    echo "<i class='fas fa-check-circle mr-2'></i> Database reseeded successfully!</div>";

    echo "<div class='bg-white rounded-xl shadow-sm p-6 mb-6'>";
    echo "<h2 class='font-semibold text-lg mb-4'><i class='fas fa-list mr-2 text-blue-600'></i>Seeded Tables</h2>";
    echo "<ul class='space-y-1 text-sm'>";
    foreach ($messages as $msg) {
        echo "<li>$msg</li>";
    }
    echo "</ul></div>";

    echo "<div class='bg-gray-50 rounded-xl p-6 mb-6'>";
    echo "<h2 class='font-semibold text-lg mb-4'><i class='fas fa-users mr-2 text-blue-600'></i>Login Credentials</h2>";
    echo "<table class='w-full text-sm'>";
    echo "<thead><tr class='border-b text-left'><th class='py-2 font-semibold'>Role</th><th class='py-2 font-semibold'>Email</th><th class='py-2 font-semibold'>Password</th></tr></thead>";
    echo "<tbody>";
    echo "<tr class='border-b'><td class='py-2'>Admin</td><td>admin@luxurymotel.com</td><td>password123</td></tr>";
    echo "<tr><td class='py-2'>Customer</td><td>jane.doe@example.com</td><td>Guest@Luxury2026</td></tr>";
    echo "</tbody></table></div>";

    echo "<div class='flex space-x-4'>";
    echo "<a href='" . SITE_URL . "index.php' class='bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition inline-block'><i class='fas fa-home mr-2'></i>Go to Homepage</a>";
    echo "<a href='" . SITE_URL . "admin/dashboard.php' class='bg-gray-800 text-white px-6 py-3 rounded-lg hover:bg-gray-900 transition inline-block'><i class='fas fa-tachometer-alt mr-2'></i>Go to Admin Panel</a>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div class='bg-red-50 border-l-4 border-red-500 text-red-800 p-4 mb-6 rounded-lg font-semibold'>";
    echo "<i class='fas fa-exclamation-triangle mr-2'></i> Seeding failed: " . $e->getMessage() . "</div>";
}

echo "</div></div></body></html>";
