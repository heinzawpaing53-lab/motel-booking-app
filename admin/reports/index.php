<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) { redirect('login.php'); }

$tab = $_GET['tab'] ?? 'daily';
define('PAGE_TITLE', 'Reports');

$userId = $_SESSION['user_id'];
$csrfToken = generateCsrfToken();

$months = [
    '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
    '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
    '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
];

// Export CSV
if (isset($_GET['export']) && isset($_GET['tab'])) {
    $expTab = $_GET['tab'];
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $expTab . '_report.csv"');
    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF");

    if ($expTab === 'daily') {
        $date = $_GET['date'] ?? date('Y-m-d');
        fputcsv($output, ['Reservation ID', 'Guest Name', 'Room', 'Check In', 'Check Out', 'Guests', 'Total Price', 'Status', 'Payment']);
        $stmt = $pdo->prepare("SELECT r.reservation_id, u.first_name, u.last_name, rm.room_number, r.check_in_date, r.check_out_date, r.total_guests, r.total_price, r.booking_status, r.payment_status FROM reservations r JOIN users u ON r.user_id = u.user_id JOIN rooms rm ON r.room_id = rm.room_id WHERE DATE(r.check_in_date) = ? OR DATE(r.check_out_date) = ? ORDER BY r.check_in_date");
        $stmt->execute([$date, $date]);
        while ($row = $stmt->fetch()) {
            fputcsv($output, [$row['reservation_id'], $row['first_name'] . ' ' . $row['last_name'], $row['room_number'], $row['check_in_date'], $row['check_out_date'], $row['total_guests'], number_format($row['total_price'], 2), $row['booking_status'], $row['payment_status']]);
        }
    } elseif ($expTab === 'monthly') {
        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');
        fputcsv($output, ['Reservation ID', 'Guest Name', 'Room', 'Check In', 'Check Out', 'Total Price', 'Status']);
        $stmt = $pdo->prepare("SELECT r.reservation_id, u.first_name, u.last_name, rm.room_number, r.check_in_date, r.check_out_date, r.total_price, r.booking_status FROM reservations r JOIN users u ON r.user_id = u.user_id JOIN rooms rm ON r.room_id = rm.room_id WHERE MONTH(r.check_in_date) = ? AND YEAR(r.check_in_date) = ? ORDER BY r.check_in_date");
        $stmt->execute([$month, $year]);
        while ($row = $stmt->fetch()) {
            fputcsv($output, [$row['reservation_id'], $row['first_name'] . ' ' . $row['last_name'], $row['room_number'], $row['check_in_date'], $row['check_out_date'], number_format($row['total_price'], 2), $row['booking_status']]);
        }
    } elseif ($expTab === 'yearly') {
        $year = $_GET['year'] ?? date('Y');
        fputcsv($output, ['Month', 'Total Bookings', 'Total Revenue', 'Avg Booking Value']);
        $stmt = $pdo->prepare("SELECT DATE_FORMAT(r.check_in_date, '%Y-%m') as month, COUNT(*) as total_bookings, COALESCE(SUM(r.total_price), 0) as total_revenue, COALESCE(AVG(r.total_price), 0) as avg_value FROM reservations r WHERE YEAR(r.check_in_date) = ? AND r.booking_status NOT IN ('Cancelled','Rejected') GROUP BY DATE_FORMAT(r.check_in_date, '%Y-%m') ORDER BY month");
        $stmt->execute([$year]);
        while ($row = $stmt->fetch()) {
            fputcsv($output, [$row['month'], $row['total_bookings'], number_format($row['total_revenue'], 2), number_format($row['avg_value'], 2)]);
        }
    } elseif ($expTab === 'revenue') {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        fputcsv($output, ['Date', 'Bookings', 'Revenue', 'Payment: Paid', 'Payment: Unpaid', 'Payment: Refunded']);
        $stmt = $pdo->prepare("SELECT DATE(r.check_in_date) as date, COUNT(*) as bookings, COALESCE(SUM(r.total_price), 0) as revenue, COALESCE(SUM(CASE WHEN r.payment_status = 'Paid' THEN r.total_price ELSE 0 END), 0) as paid, COALESCE(SUM(CASE WHEN r.payment_status = 'Unpaid' THEN r.total_price ELSE 0 END), 0) as unpaid, COALESCE(SUM(CASE WHEN r.payment_status = 'Refunded' THEN r.total_price ELSE 0 END), 0) as refunded FROM reservations r WHERE r.booking_status NOT IN ('Cancelled','Rejected') AND DATE(r.check_in_date) BETWEEN ? AND ? GROUP BY DATE(r.check_in_date) ORDER BY date");
        $stmt->execute([$startDate, $endDate]);
        while ($row = $stmt->fetch()) {
            fputcsv($output, [$row['date'], $row['bookings'], number_format($row['revenue'], 2), number_format($row['paid'], 2), number_format($row['unpaid'], 2), number_format($row['refunded'], 2)]);
        }
    } elseif ($expTab === 'occupancy') {
        $occDate = $_GET['occ_date'] ?? date('Y-m-d');
        fputcsv($output, ['Room Type', 'Total Rooms', 'Occupied', 'Available', 'Occupancy %']);
        $stmt = $pdo->prepare("SELECT rt.type_name, COUNT(DISTINCT rm.room_id) as total, COUNT(DISTINCT CASE WHEN occupied.room_id IS NOT NULL OR rm.status = 'Occupied' THEN rm.room_id END) as occupied FROM rooms rm JOIN room_types rt ON rm.type_id = rt.type_id LEFT JOIN (SELECT DISTINCT r.room_id FROM reservations r WHERE r.booking_status = 'Checked In' AND r.check_in_date <= ? AND r.check_out_date > ?) occupied ON rm.room_id = occupied.room_id GROUP BY rt.type_id, rt.type_name ORDER BY rt.type_name");
        $stmt->execute([$occDate, $occDate]);
        while ($row = $stmt->fetch()) {
            $pct = $row['total'] > 0 ? round(($row['occupied'] / $row['total']) * 100, 1) : 0;
            fputcsv($output, [$row['type_name'], $row['total'], $row['occupied'], $row['total'] - $row['occupied'], $pct . '%']);
        }
    } elseif ($expTab === 'customers') {
        $year = $_GET['year'] ?? date('Y');
        fputcsv($output, ['Month', 'New Registrations']);
        $stmt = $pdo->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total FROM users WHERE YEAR(created_at) = ? GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month");
        $stmt->execute([$year]);
        while ($row = $stmt->fetch()) {
            fputcsv($output, [$row['month'], $row['total']]);
        }
    }

    fclose($output);
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo PAGE_TITLE . ' - ' . SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
</head>
<body class="bg-gray-100">
<?php include '../../includes/sidebar.php'; ?>
<?php include '../../includes/admin-topbar.php'; ?>
<div class="ml-64 p-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><i class="fas fa-chart-bar text-blue-600 mr-3"></i>Reports</h1>
                <p class="text-gray-500 mt-1">View and analyze booking data</p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="mb-8 border-b border-gray-200">
            <nav class="flex space-x-6 overflow-x-auto">
                <a href="?tab=daily" class="pb-3 px-1 text-sm font-semibold border-b-2 transition whitespace-nowrap <?php echo $tab === 'daily' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?>"><i class="fas fa-calendar-day mr-2"></i>Daily</a>
                <a href="?tab=monthly" class="pb-3 px-1 text-sm font-semibold border-b-2 transition whitespace-nowrap <?php echo $tab === 'monthly' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?>"><i class="fas fa-calendar-alt mr-2"></i>Monthly</a>
                <a href="?tab=yearly" class="pb-3 px-1 text-sm font-semibold border-b-2 transition whitespace-nowrap <?php echo $tab === 'yearly' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?>"><i class="fas fa-calendar mr-2"></i>Yearly</a>
                <a href="?tab=revenue" class="pb-3 px-1 text-sm font-semibold border-b-2 transition whitespace-nowrap <?php echo $tab === 'revenue' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?>"><i class="fas fa-dollar-sign mr-2"></i>Revenue</a>
                <a href="?tab=occupancy" class="pb-3 px-1 text-sm font-semibold border-b-2 transition whitespace-nowrap <?php echo $tab === 'occupancy' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?>"><i class="fas fa-bed mr-2"></i>Occupancy</a>
                <a href="?tab=customers" class="pb-3 px-1 text-sm font-semibold border-b-2 transition whitespace-nowrap <?php echo $tab === 'customers' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?>"><i class="fas fa-users mr-2"></i>Customers</a>
                <a href="?tab=export" class="pb-3 px-1 text-sm font-semibold border-b-2 transition whitespace-nowrap <?php echo $tab === 'export' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?>"><i class="fas fa-download mr-2"></i>Export</a>
            </nav>
        </div>

<?php if ($tab === 'daily'): ?>
<?php
$dailyDate = $_GET['date'] ?? date('Y-m-d');
$stmt = $pdo->prepare("SELECT r.*, u.first_name, u.last_name, u.email, u.phone, rm.room_number, rt.type_name FROM reservations r JOIN users u ON r.user_id = u.user_id JOIN rooms rm ON r.room_id = rm.room_id JOIN room_types rt ON rm.type_id = rt.type_id WHERE (DATE(r.check_in_date) = ? OR DATE(r.check_out_date) = ?) ORDER BY r.check_in_date");
$stmt->execute([$dailyDate, $dailyDate]);
$dailyBookings = $stmt->fetchAll();
$dailyTotal = 0;
foreach ($dailyBookings as $b) { $dailyTotal += $b['total_price']; }
?>
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
        <h2 class="text-xl font-bold text-gray-800"><i class="fas fa-calendar-day text-blue-600 mr-2"></i>Daily Booking Report</h2>
        <form method="GET" class="flex items-center gap-3">
            <input type="hidden" name="tab" value="daily">
            <label class="text-sm text-gray-600 font-medium">Select Date:</label>
            <input type="date" name="date" value="<?php echo $dailyDate; ?>" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition"><i class="fas fa-search mr-1"></i>View</button>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-50 rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500 font-medium">Total Bookings</p>
            <p class="text-3xl font-bold text-blue-600"><?php echo count($dailyBookings); ?></p>
        </div>
        <div class="bg-green-50 rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500 font-medium">Total Revenue</p>
            <p class="text-3xl font-bold text-green-600"><?php echo formatCurrency($dailyTotal); ?></p>
        </div>
        <div class="bg-purple-50 rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500 font-medium">Guests Today</p>
            <p class="text-3xl font-bold text-purple-600"><?php echo array_sum(array_column($dailyBookings, 'total_guests')); ?></p>
        </div>
    </div>

    <?php if ($dailyBookings): ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b-2 border-gray-200 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="text-left py-3 px-2">ID</th>
                    <th class="text-left py-3 px-2">Guest</th>
                    <th class="text-left py-3 px-2">Room</th>
                    <th class="text-left py-3 px-2">Type</th>
                    <th class="text-left py-3 px-2">Check In</th>
                    <th class="text-left py-3 px-2">Check Out</th>
                    <th class="text-center py-3 px-2">Guests</th>
                    <th class="text-right py-3 px-2">Total</th>
                    <th class="text-center py-3 px-2">Status</th>
                    <th class="text-center py-3 px-2">Payment</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dailyBookings as $b): ?>
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                    <td class="py-3 px-2 font-semibold">#<?php echo $b['reservation_id']; ?></td>
                    <td class="py-3 px-2"><?php echo sanitize($b['first_name'] . ' ' . $b['last_name']); ?></td>
                    <td class="py-3 px-2"><?php echo sanitize($b['room_number']); ?></td>
                    <td class="py-3 px-2"><?php echo sanitize($b['type_name']); ?></td>
                    <td class="py-3 px-2"><?php echo formatDate($b['check_in_date']); ?></td>
                    <td class="py-3 px-2"><?php echo formatDate($b['check_out_date']); ?></td>
                    <td class="py-3 px-2 text-center"><?php echo $b['total_guests']; ?></td>
                    <td class="py-3 px-2 text-right font-semibold"><?php echo formatCurrency($b['total_price']); ?></td>
                    <td class="py-3 px-2 text-center"><span class="badge-status badge-<?php echo badgeClass($b['booking_status']); ?>"><?php echo $b['booking_status']; ?></span></td>
                    <td class="py-3 px-2 text-center"><span class="badge-status badge-<?php echo badgeClass($b['payment_status']); ?>"><?php echo $b['payment_status']; ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="bg-gray-50 font-bold">
                    <td colspan="7" class="py-3 px-2 text-right">Total:</td>
                    <td class="py-3 px-2 text-right"><?php echo formatCurrency($dailyTotal); ?></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php else: ?>
    <div class="text-center py-12 text-gray-400">
        <i class="fas fa-calendar-day text-5xl mb-4"></i>
        <p class="text-lg font-medium">No bookings found for this date.</p>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($tab === 'monthly'): ?>
<?php
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$stmt = $pdo->prepare("SELECT r.*, u.first_name, u.last_name, rm.room_number, rt.type_name FROM reservations r JOIN users u ON r.user_id = u.user_id JOIN rooms rm ON r.room_id = rm.room_id JOIN room_types rt ON rm.type_id = rt.type_id WHERE MONTH(r.check_in_date) = ? AND YEAR(r.check_in_date) = ? ORDER BY r.check_in_date");
$stmt->execute([$month, $year]);
$monthlyBookings = $stmt->fetchAll();
$monthlyTotal = 0;
$monthlyCount = 0;
foreach ($monthlyBookings as $b) {
    if (!in_array($b['booking_status'], ['Cancelled', 'Rejected'])) {
        $monthlyTotal += $b['total_price'];
        $monthlyCount++;
    }
}
?>
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
        <h2 class="text-xl font-bold text-gray-800"><i class="fas fa-calendar-alt text-blue-600 mr-2"></i>Monthly Booking Report</h2>
        <form method="GET" class="flex items-center gap-3">
            <input type="hidden" name="tab" value="monthly">
            <select name="month" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <?php foreach ($months as $val => $label): ?>
                <option value="<?php echo $val; ?>" <?php echo $month === $val ? 'selected' : ''; ?>><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
            <select name="year" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                <option value="<?php echo $y; ?>" <?php echo (int)$year === $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition"><i class="fas fa-search mr-1"></i>View</button>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-50 rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500 font-medium">Total Bookings (Active)</p>
            <p class="text-3xl font-bold text-blue-600"><?php echo $monthlyCount; ?></p>
        </div>
        <div class="bg-green-50 rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500 font-medium">Total Revenue</p>
            <p class="text-3xl font-bold text-green-600"><?php echo formatCurrency($monthlyTotal); ?></p>
        </div>
        <div class="bg-purple-50 rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500 font-medium">Avg Booking Value</p>
            <p class="text-3xl font-bold text-purple-600"><?php echo $monthlyCount > 0 ? formatCurrency($monthlyTotal / $monthlyCount) : formatCurrency(0); ?></p>
        </div>
    </div>

    <?php if ($monthlyBookings): ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b-2 border-gray-200 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="text-left py-3 px-2">ID</th>
                    <th class="text-left py-3 px-2">Guest</th>
                    <th class="text-left py-3 px-2">Room</th>
                    <th class="text-left py-3 px-2">Type</th>
                    <th class="text-left py-3 px-2">Check In</th>
                    <th class="text-left py-3 px-2">Check Out</th>
                    <th class="text-center py-3 px-2">Guests</th>
                    <th class="text-right py-3 px-2">Total</th>
                    <th class="text-center py-3 px-2">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($monthlyBookings as $b): ?>
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                    <td class="py-3 px-2 font-semibold">#<?php echo $b['reservation_id']; ?></td>
                    <td class="py-3 px-2"><?php echo sanitize($b['first_name'] . ' ' . $b['last_name']); ?></td>
                    <td class="py-3 px-2"><?php echo sanitize($b['room_number']); ?></td>
                    <td class="py-3 px-2"><?php echo sanitize($b['type_name']); ?></td>
                    <td class="py-3 px-2"><?php echo formatDate($b['check_in_date']); ?></td>
                    <td class="py-3 px-2"><?php echo formatDate($b['check_out_date']); ?></td>
                    <td class="py-3 px-2 text-center"><?php echo $b['total_guests']; ?></td>
                    <td class="py-3 px-2 text-right font-semibold"><?php echo formatCurrency($b['total_price']); ?></td>
                    <td class="py-3 px-2 text-center"><span class="badge-status badge-<?php echo badgeClass($b['booking_status']); ?>"><?php echo $b['booking_status']; ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="bg-gray-50 font-bold">
                    <td colspan="7" class="py-3 px-2 text-right">Total (Active):</td>
                    <td class="py-3 px-2 text-right"><?php echo formatCurrency($monthlyTotal); ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php else: ?>
    <div class="text-center py-12 text-gray-400">
        <i class="fas fa-calendar-alt text-5xl mb-4"></i>
        <p class="text-lg font-medium">No bookings found for this month.</p>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($tab === 'yearly'): ?>
<?php
$yearlyYear = (int)($_GET['year'] ?? date('Y'));
$stmt = $pdo->prepare("SELECT DATE_FORMAT(r.check_in_date, '%Y-%m') as month, COUNT(*) as total_bookings, COALESCE(SUM(r.total_price), 0) as total_revenue, COALESCE(AVG(r.total_price), 0) as avg_value, COUNT(DISTINCT r.user_id) as unique_guests FROM reservations r WHERE YEAR(r.check_in_date) = ? AND r.booking_status NOT IN ('Cancelled','Rejected') GROUP BY DATE_FORMAT(r.check_in_date, '%Y-%m') ORDER BY month");
$stmt->execute([$yearlyYear]);
$yearlyData = $stmt->fetchAll();
$yearlyRevenue = array_sum(array_column($yearlyData, 'total_revenue'));
$yearlyBookings = array_sum(array_column($yearlyData, 'total_bookings'));
$yearlyGuests = array_sum(array_column($yearlyData, 'unique_guests'));
$maxRevenue = $yearlyData ? max(array_column($yearlyData, 'total_revenue')) : 0;
?>
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
        <h2 class="text-xl font-bold text-gray-800"><i class="fas fa-calendar text-blue-600 mr-2"></i>Yearly Report - <?php echo $yearlyYear; ?></h2>
        <form method="GET" class="flex items-center gap-3">
            <input type="hidden" name="tab" value="yearly">
            <select name="year" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                <option value="<?php echo $y; ?>" <?php echo $yearlyYear === $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition"><i class="fas fa-search mr-1"></i>View</button>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500 font-medium">Total Bookings</p>
            <p class="text-3xl font-bold text-blue-600"><?php echo $yearlyBookings; ?></p>
        </div>
        <div class="bg-green-50 rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500 font-medium">Total Revenue</p>
            <p class="text-3xl font-bold text-green-600"><?php echo formatCurrency($yearlyRevenue); ?></p>
        </div>
        <div class="bg-purple-50 rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500 font-medium">Unique Guests</p>
            <p class="text-3xl font-bold text-purple-600"><?php echo $yearlyGuests; ?></p>
        </div>
        <div class="bg-orange-50 rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500 font-medium">Avg Monthly Revenue</p>
            <p class="text-3xl font-bold text-orange-600"><?php echo count($yearlyData) > 0 ? formatCurrency($yearlyRevenue / count($yearlyData)) : formatCurrency(0); ?></p>
        </div>
    </div>

    <?php if ($yearlyData): ?>
    <div class="space-y-4 mb-6">
        <?php foreach ($yearlyData as $yd): 
            $barPct = $maxRevenue > 0 ? ($yd['total_revenue'] / $maxRevenue) * 100 : 0;
        ?>
        <div>
            <div class="flex items-center justify-between text-sm mb-1">
                <span class="font-semibold text-gray-700"><?php echo date('F', strtotime($yd['month'] . '-01')); ?></span>
                <span class="text-gray-500"><?php echo $yd['total_bookings']; ?> bookings - <?php echo formatCurrency($yd['total_revenue']); ?></span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-blue-600 h-3 rounded-full transition-all" style="width: <?php echo $barPct; ?>%"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b-2 border-gray-200 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="text-left py-3 px-2">Month</th>
                    <th class="text-right py-3 px-2">Bookings</th>
                    <th class="text-right py-3 px-2">Revenue</th>
                    <th class="text-right py-3 px-2">Avg Value</th>
                    <th class="text-right py-3 px-2">Unique Guests</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($yearlyData as $yd): ?>
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                    <td class="py-3 px-2 font-semibold"><?php echo date('F Y', strtotime($yd['month'] . '-01')); ?></td>
                    <td class="py-3 px-2 text-right"><?php echo $yd['total_bookings']; ?></td>
                    <td class="py-3 px-2 text-right font-semibold text-green-600"><?php echo formatCurrency($yd['total_revenue']); ?></td>
                    <td class="py-3 px-2 text-right"><?php echo formatCurrency($yd['avg_value']); ?></td>
                    <td class="py-3 px-2 text-right"><?php echo $yd['unique_guests']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="bg-gray-50 font-bold">
                    <td class="py-3 px-2">Total</td>
                    <td class="py-3 px-2 text-right"><?php echo $yearlyBookings; ?></td>
                    <td class="py-3 px-2 text-right text-green-600"><?php echo formatCurrency($yearlyRevenue); ?></td>
                    <td class="py-3 px-2 text-right"><?php echo $yearlyBookings > 0 ? formatCurrency($yearlyRevenue / $yearlyBookings) : formatCurrency(0); ?></td>
                    <td class="py-3 px-2 text-right"><?php echo $yearlyGuests; ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php else: ?>
    <div class="text-center py-12 text-gray-400">
        <i class="fas fa-calendar text-5xl mb-4"></i>
        <p class="text-lg font-medium">No data found for <?php echo $yearlyYear; ?>.</p>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($tab === 'revenue'): ?>
<?php
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');
$stmt = $pdo->prepare("SELECT DATE(r.check_in_date) as date, COUNT(*) as bookings, COALESCE(SUM(r.total_price), 0) as revenue, COALESCE(SUM(CASE WHEN r.payment_status = 'Paid' THEN r.total_price ELSE 0 END), 0) as paid, COALESCE(SUM(CASE WHEN r.payment_status = 'Unpaid' THEN r.total_price ELSE 0 END), 0) as unpaid, COALESCE(SUM(CASE WHEN r.payment_status = 'Refunded' THEN r.total_price ELSE 0 END), 0) as refunded, COALESCE(SUM(CASE WHEN r.booking_status NOT IN ('Cancelled','Rejected') THEN 1 ELSE 0 END), 0) as active_bookings FROM reservations r WHERE DATE(r.check_in_date) BETWEEN ? AND ? GROUP BY DATE(r.check_in_date) ORDER BY date");
$stmt->execute([$startDate, $endDate]);
$revenueData = $stmt->fetchAll();
$totalRevenue = array_sum(array_column($revenueData, 'revenue'));
$totalPaid = array_sum(array_column($revenueData, 'paid'));
$totalUnpaid = array_sum(array_column($revenueData, 'unpaid'));
$totalRefunded = array_sum(array_column($revenueData, 'refunded'));
$totalActive = array_sum(array_column($revenueData, 'active_bookings'));
$maxRev = $revenueData ? max(array_column($revenueData, 'revenue')) : 0;
?>
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
        <h2 class="text-xl font-bold text-gray-800"><i class="fas fa-dollar-sign text-blue-600 mr-2"></i>Revenue Report</h2>
        <form method="GET" class="flex items-center gap-3">
            <input type="hidden" name="tab" value="revenue">
            <label class="text-sm text-gray-600 font-medium">From:</label>
            <input type="date" name="start_date" value="<?php echo $startDate; ?>" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            <label class="text-sm text-gray-600 font-medium">To:</label>
            <input type="date" name="end_date" value="<?php echo $endDate; ?>" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition"><i class="fas fa-search mr-1"></i>View</button>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-green-50 rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500 font-medium">Total Revenue</p>
            <p class="text-2xl font-bold text-green-600"><?php echo formatCurrency($totalRevenue); ?></p>
        </div>
        <div class="bg-blue-50 rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500 font-medium">Paid</p>
            <p class="text-2xl font-bold text-blue-600"><?php echo formatCurrency($totalPaid); ?></p>
        </div>
        <div class="bg-yellow-50 rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500 font-medium">Unpaid</p>
            <p class="text-2xl font-bold text-yellow-600"><?php echo formatCurrency($totalUnpaid); ?></p>
        </div>
        <div class="bg-red-50 rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500 font-medium">Refunded</p>
            <p class="text-2xl font-bold text-red-600"><?php echo formatCurrency($totalRefunded); ?></p>
        </div>
    </div>

    <?php if ($revenueData): ?>
    <div class="space-y-3 mb-6">
        <?php foreach ($revenueData as $rd): 
            $barPct = $maxRev > 0 ? ($rd['revenue'] / $maxRev) * 100 : 0;
        ?>
        <div>
            <div class="flex items-center justify-between text-sm mb-1">
                <span class="font-semibold text-gray-700"><?php echo formatDate($rd['date']); ?></span>
                <span class="text-gray-500"><?php echo $rd['active_bookings']; ?> bookings - <?php echo formatCurrency($rd['revenue']); ?></span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-green-500 h-2.5 rounded-full transition-all" style="width: <?php echo $barPct; ?>%"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b-2 border-gray-200 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="text-left py-3 px-2">Date</th>
                    <th class="text-right py-3 px-2">Active Bookings</th>
                    <th class="text-right py-3 px-2">Revenue</th>
                    <th class="text-right py-3 px-2">Paid</th>
                    <th class="text-right py-3 px-2">Pending</th>
                    <th class="text-right py-3 px-2">Refunded</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($revenueData as $rd): ?>
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                    <td class="py-3 px-2 font-semibold"><?php echo formatDate($rd['date']); ?></td>
                    <td class="py-3 px-2 text-right"><?php echo $rd['active_bookings']; ?></td>
                    <td class="py-3 px-2 text-right font-semibold text-green-600"><?php echo formatCurrency($rd['revenue']); ?></td>
                    <td class="py-3 px-2 text-right text-blue-600"><?php echo formatCurrency($rd['paid']); ?></td>
                    <td class="py-3 px-2 text-right text-yellow-600"><?php echo formatCurrency($rd['unpaid']); ?></td>
                    <td class="py-3 px-2 text-right text-red-600"><?php echo formatCurrency($rd['refunded']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="bg-gray-50 font-bold">
                    <td class="py-3 px-2">Total</td>
                    <td class="py-3 px-2 text-right"><?php echo $totalActive; ?></td>
                    <td class="py-3 px-2 text-right text-green-600"><?php echo formatCurrency($totalRevenue); ?></td>
                    <td class="py-3 px-2 text-right text-blue-600"><?php echo formatCurrency($totalPaid); ?></td>
                    <td class="py-3 px-2 text-right text-yellow-600"><?php echo formatCurrency($totalUnpaid); ?></td>
                    <td class="py-3 px-2 text-right text-red-600"><?php echo formatCurrency($totalRefunded); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php else: ?>
    <div class="text-center py-12 text-gray-400">
        <i class="fas fa-chart-line text-5xl mb-4"></i>
        <p class="text-lg font-medium">No revenue data for the selected period.</p>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($tab === 'occupancy'): ?>
<?php
$occDate = $_GET['occ_date'] ?? date('Y-m-d');
$stmt = $pdo->prepare("SELECT rt.type_id, rt.type_name, COUNT(DISTINCT rm.room_id) as total_rooms, COUNT(DISTINCT CASE WHEN occupied.room_id IS NOT NULL OR rm.status = 'Occupied' THEN rm.room_id END) as occupied_rooms FROM rooms rm JOIN room_types rt ON rm.type_id = rt.type_id LEFT JOIN (SELECT DISTINCT r.room_id FROM reservations r WHERE r.booking_status = 'Checked In' AND r.check_in_date <= ? AND r.check_out_date > ?) occupied ON rm.room_id = occupied.room_id GROUP BY rt.type_id, rt.type_name ORDER BY rt.type_name");
$stmt->execute([$occDate, $occDate]);
$occData = $stmt->fetchAll();

$stmtTotal = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$stmtOcc = $pdo->prepare("SELECT COUNT(DISTINCT rm.room_id) FROM rooms rm LEFT JOIN (SELECT DISTINCT r.room_id FROM reservations r WHERE r.booking_status = 'Checked In' AND r.check_in_date <= ? AND r.check_out_date > ?) occupied ON rm.room_id = occupied.room_id WHERE occupied.room_id IS NOT NULL OR rm.status = 'Occupied'");
$stmtOcc->execute([$occDate, $occDate]);
$totalOccupied = $stmtOcc->fetchColumn();
$overallPct = $stmtTotal > 0 ? round(($totalOccupied / $stmtTotal) * 100, 1) : 0;
?>
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
        <h2 class="text-xl font-bold text-gray-800"><i class="fas fa-bed text-blue-600 mr-2"></i>Occupancy Report</h2>
        <form method="GET" class="flex items-center gap-3">
            <input type="hidden" name="tab" value="occupancy">
            <label class="text-sm text-gray-600 font-medium">Date:</label>
            <input type="date" name="occ_date" value="<?php echo $occDate; ?>" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition"><i class="fas fa-search mr-1"></i>View</button>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-50 rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500 font-medium">Total Rooms</p>
            <p class="text-3xl font-bold text-blue-600"><?php echo $stmtTotal; ?></p>
        </div>
        <div class="bg-green-50 rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500 font-medium">Occupied</p>
            <p class="text-3xl font-bold text-green-600"><?php echo $totalOccupied; ?></p>
        </div>
        <div class="bg-purple-50 rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500 font-medium">Occupancy Rate</p>
            <p class="text-3xl font-bold text-purple-600"><?php echo $overallPct; ?>%</p>
        </div>
    </div>

    <?php if ($overallPct > 0): ?>
    <div class="mb-6">
        <div class="flex items-center justify-between text-sm mb-1">
            <span class="font-semibold text-gray-700">Overall Occupancy</span>
            <span class="text-gray-500"><?php echo $totalOccupied; ?> / <?php echo $stmtTotal; ?> rooms (<?php echo $overallPct; ?>%)</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-4">
            <div class="bg-gradient-to-r from-green-400 via-yellow-400 to-red-500 h-4 rounded-full transition-all" style="width: <?php echo min($overallPct, 100); ?>%"></div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($occData): ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b-2 border-gray-200 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="text-left py-3 px-2">Room Type</th>
                    <th class="text-right py-3 px-2">Total Rooms</th>
                    <th class="text-right py-3 px-2">Occupied</th>
                    <th class="text-right py-3 px-2">Available</th>
                    <th class="text-right py-3 px-2">Occupancy %</th>
                    <th class="py-3 px-2">Visual</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($occData as $od): 
                    $avail = $od['total_rooms'] - $od['occupied_rooms'];
                    $pct = $od['total_rooms'] > 0 ? round(($od['occupied_rooms'] / $od['total_rooms']) * 100, 1) : 0;
                    $barColor = $pct > 75 ? 'bg-red-500' : ($pct > 50 ? 'bg-yellow-500' : 'bg-green-500');
                ?>
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                    <td class="py-3 px-2 font-semibold"><?php echo sanitize($od['type_name']); ?></td>
                    <td class="py-3 px-2 text-right"><?php echo $od['total_rooms']; ?></td>
                    <td class="py-3 px-2 text-right text-green-600 font-semibold"><?php echo $od['occupied_rooms']; ?></td>
                    <td class="py-3 px-2 text-right"><?php echo $avail; ?></td>
                    <td class="py-3 px-2 text-right font-semibold"><?php echo $pct; ?>%</td>
                    <td class="py-3 px-2">
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="<?php echo $barColor; ?> h-2.5 rounded-full" style="width: <?php echo min($pct, 100); ?>%"></div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="bg-gray-50 font-bold">
                    <td class="py-3 px-2">Total</td>
                    <td class="py-3 px-2 text-right"><?php echo $stmtTotal; ?></td>
                    <td class="py-3 px-2 text-right text-green-600"><?php echo $totalOccupied; ?></td>
                    <td class="py-3 px-2 text-right"><?php echo $stmtTotal - $totalOccupied; ?></td>
                    <td class="py-3 px-2 text-right"><?php echo $overallPct; ?>%</td>
                    <td class="py-3 px-2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php else: ?>
    <div class="text-center py-12 text-gray-400">
        <i class="fas fa-bed text-5xl mb-4"></i>
        <p class="text-lg font-medium">No room data available.</p>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($tab === 'customers'): ?>
<?php
$custYear = (int)($_GET['year'] ?? date('Y'));
$stmt = $pdo->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total FROM users WHERE YEAR(created_at) = ? GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month");
$stmt->execute([$custYear]);
$custData = $stmt->fetchAll();
$totalRegistrations = array_sum(array_column($custData, 'total'));
$maxCust = $custData ? max(array_column($custData, 'total')) : 0;

$stmtTotalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$stmtActiveUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'Active'")->fetchColumn();
?>
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
        <h2 class="text-xl font-bold text-gray-800"><i class="fas fa-users text-blue-600 mr-2"></i>Customer Registration Report</h2>
        <form method="GET" class="flex items-center gap-3">
            <input type="hidden" name="tab" value="customers">
            <select name="year" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                <option value="<?php echo $y; ?>" <?php echo $custYear === $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition"><i class="fas fa-search mr-1"></i>View</button>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-50 rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500 font-medium">Total Customers</p>
            <p class="text-3xl font-bold text-blue-600"><?php echo $stmtTotalUsers; ?></p>
        </div>
        <div class="bg-green-50 rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500 font-medium">Active Customers</p>
            <p class="text-3xl font-bold text-green-600"><?php echo $stmtActiveUsers; ?></p>
        </div>
        <div class="bg-purple-50 rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500 font-medium">New in <?php echo $custYear; ?></p>
            <p class="text-3xl font-bold text-purple-600"><?php echo $totalRegistrations; ?></p>
        </div>
    </div>

    <?php if ($custData): ?>
    <div class="space-y-3 mb-6">
        <?php foreach ($custData as $cd): 
            $barPct = $maxCust > 0 ? ($cd['total'] / $maxCust) * 100 : 0;
        ?>
        <div>
            <div class="flex items-center justify-between text-sm mb-1">
                <span class="font-semibold text-gray-700"><?php echo date('F Y', strtotime($cd['month'] . '-01')); ?></span>
                <span class="text-gray-500"><?php echo $cd['total']; ?> new registrations</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-blue-500 h-3 rounded-full transition-all" style="width: <?php echo $barPct; ?>%"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b-2 border-gray-200 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="text-left py-3 px-2">Month</th>
                    <th class="text-right py-3 px-2">New Registrations</th>
                    <th class="py-3 px-2">Visual</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($custData as $cd): 
                    $barPct = $maxCust > 0 ? ($cd['total'] / $maxCust) * 100 : 0;
                ?>
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                    <td class="py-3 px-2 font-semibold"><?php echo date('F Y', strtotime($cd['month'] . '-01')); ?></td>
                    <td class="py-3 px-2 text-right"><?php echo $cd['total']; ?></td>
                    <td class="py-3 px-2">
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-blue-500 h-2.5 rounded-full" style="width: <?php echo $barPct; ?>%"></div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="bg-gray-50 font-bold">
                    <td class="py-3 px-2">Total</td>
                    <td class="py-3 px-2 text-right"><?php echo $totalRegistrations; ?></td>
                    <td class="py-3 px-2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php else: ?>
    <div class="text-center py-12 text-gray-400">
        <i class="fas fa-users text-5xl mb-4"></i>
        <p class="text-lg font-medium">No registration data for <?php echo $custYear; ?>.</p>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($tab === 'export'): ?>
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h2 class="text-xl font-bold text-gray-800 mb-6"><i class="fas fa-download text-blue-600 mr-2"></i>Export Reports</h2>
    <p class="text-gray-500 mb-6">Download report data as CSV files for further analysis.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition">
            <i class="fas fa-calendar-day text-blue-500 text-3xl mb-3"></i>
            <h3 class="font-bold text-gray-800 mb-2">Daily Report</h3>
            <p class="text-sm text-gray-500 mb-4">Export bookings for a specific date.</p>
            <form method="GET" class="space-y-2">
                <input type="hidden" name="tab" value="daily">
                <input type="hidden" name="export" value="1">
                <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition"><i class="fas fa-file-csv mr-1"></i>Export CSV</button>
            </form>
        </div>

        <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition">
            <i class="fas fa-calendar-alt text-purple-500 text-3xl mb-3"></i>
            <h3 class="font-bold text-gray-800 mb-2">Monthly Report</h3>
            <p class="text-sm text-gray-500 mb-4">Export bookings for a specific month.</p>
            <form method="GET" class="space-y-2">
                <input type="hidden" name="tab" value="monthly">
                <input type="hidden" name="export" value="1">
                <select name="month" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <?php foreach ($months as $val => $label): ?>
                    <option value="<?php echo $val; ?>" <?php echo date('m') === $val ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="year" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo (int)date('Y') === $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition"><i class="fas fa-file-csv mr-1"></i>Export CSV</button>
            </form>
        </div>

        <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition">
            <i class="fas fa-calendar text-orange-500 text-3xl mb-3"></i>
            <h3 class="font-bold text-gray-800 mb-2">Yearly Report</h3>
            <p class="text-sm text-gray-500 mb-4">Export yearly stats by month.</p>
            <form method="GET" class="space-y-2">
                <input type="hidden" name="tab" value="yearly">
                <input type="hidden" name="export" value="1">
                <select name="year" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo (int)date('Y') === $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition"><i class="fas fa-file-csv mr-1"></i>Export CSV</button>
            </form>
        </div>

        <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition">
            <i class="fas fa-dollar-sign text-green-500 text-3xl mb-3"></i>
            <h3 class="font-bold text-gray-800 mb-2">Revenue Report</h3>
            <p class="text-sm text-gray-500 mb-4">Export revenue data with date range.</p>
            <form method="GET" class="space-y-2">
                <input type="hidden" name="tab" value="revenue">
                <input type="hidden" name="export" value="1">
                <input type="date" name="start_date" value="<?php echo date('Y-m-01'); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <input type="date" name="end_date" value="<?php echo date('Y-m-t'); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition"><i class="fas fa-file-csv mr-1"></i>Export CSV</button>
            </form>
        </div>

        <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition">
            <i class="fas fa-bed text-red-500 text-3xl mb-3"></i>
            <h3 class="font-bold text-gray-800 mb-2">Occupancy Report</h3>
            <p class="text-sm text-gray-500 mb-4">Export occupancy data for a date.</p>
            <form method="GET" class="space-y-2">
                <input type="hidden" name="tab" value="occupancy">
                <input type="hidden" name="export" value="1">
                <input type="date" name="occ_date" value="<?php echo date('Y-m-d'); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition"><i class="fas fa-file-csv mr-1"></i>Export CSV</button>
            </form>
        </div>

        <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition">
            <i class="fas fa-users text-indigo-500 text-3xl mb-3"></i>
            <h3 class="font-bold text-gray-800 mb-2">Customers Report</h3>
            <p class="text-sm text-gray-500 mb-4">Export customer registrations.</p>
            <form method="GET" class="space-y-2">
                <input type="hidden" name="tab" value="customers">
                <input type="hidden" name="export" value="1">
                <select name="year" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo (int)date('Y') === $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition"><i class="fas fa-file-csv mr-1"></i>Export CSV</button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

    </div>
</div>
</body>
</html>
<?php logActivity($pdo, $userId, 'Viewed Reports', 'Viewed tab: ' . $tab); ?>
