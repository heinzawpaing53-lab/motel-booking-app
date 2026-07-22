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
<body class="admin-layout font-[Inter] flex h-screen w-screen overflow-hidden bg-slate-100">
<?php include '../../includes/sidebar.php'; ?>
<div class="flex-1 flex flex-col h-full overflow-hidden">
<?php include '../../includes/admin-topbar.php'; ?>
<main class="flex-1 overflow-y-auto bg-slate-50">
<div class="p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><i class="fas fa-chart-bar text-amber-600 mr-3"></i>Reports</h1>
                <p class="text-stone-500 mt-1">View and analyze booking data</p>
            </div>
        </div>

        <!-- Floating Pill Tab Navigation -->
        <div class="mb-8">
            <nav class="bg-stone-900/90 backdrop-blur-md p-1.5 rounded-2xl inline-flex items-center gap-2 border border-stone-800 shadow-lg">
                <a href="?tab=daily" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm transition-all <?php echo $tab === 'daily' ? 'bg-amber-600 text-white font-semibold shadow-md' : 'text-stone-400 hover:text-stone-200 hover:bg-stone-800/50'; ?>"><i class="fas fa-calendar-day"></i>Daily</a>
                <a href="?tab=monthly" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm transition-all <?php echo $tab === 'monthly' ? 'bg-amber-600 text-white font-semibold shadow-md' : 'text-stone-400 hover:text-stone-200 hover:bg-stone-800/50'; ?>"><i class="fas fa-calendar-alt"></i>Monthly</a>
                <a href="?tab=yearly" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm transition-all <?php echo $tab === 'yearly' ? 'bg-amber-600 text-white font-semibold shadow-md' : 'text-stone-400 hover:text-stone-200 hover:bg-stone-800/50'; ?>"><i class="fas fa-calendar"></i>Yearly</a>
                <a href="?tab=revenue" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm transition-all <?php echo $tab === 'revenue' ? 'bg-amber-600 text-white font-semibold shadow-md' : 'text-stone-400 hover:text-stone-200 hover:bg-stone-800/50'; ?>"><i class="fas fa-dollar-sign"></i>Revenue</a>
                <a href="?tab=occupancy" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm transition-all <?php echo $tab === 'occupancy' ? 'bg-amber-600 text-white font-semibold shadow-md' : 'text-stone-400 hover:text-stone-200 hover:bg-stone-800/50'; ?>"><i class="fas fa-bed"></i>Occupancy</a>
                <a href="?tab=customers" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm transition-all <?php echo $tab === 'customers' ? 'bg-amber-600 text-white font-semibold shadow-md' : 'text-stone-400 hover:text-stone-200 hover:bg-stone-800/50'; ?>"><i class="fas fa-users"></i>Customers</a>
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
<div class="mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-4">
        <h2 class="text-xl font-bold text-stone-800"><i class="fas fa-calendar-day text-amber-600 mr-2"></i>Daily Booking Report</h2>
    </div>
    <form method="GET" class="bg-white p-4 rounded-2xl border border-stone-200 shadow-sm flex flex-wrap items-center justify-between gap-4 mb-6">
        <input type="hidden" name="tab" value="daily">
        <div class="flex items-center gap-3">
            <label class="text-sm text-stone-600 font-medium">Select Date:</label>
            <input type="date" name="date" value="<?php echo $dailyDate; ?>" class="border border-stone-300 rounded-xl px-3.5 py-2 text-sm text-stone-800 bg-stone-50 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 outline-none transition-all">
            <button type="submit" class="bg-amber-700 hover:bg-amber-800 text-white font-medium px-5 py-2 rounded-xl text-sm transition-all shadow-sm"><i class="fas fa-search mr-1"></i>View</button>
        </div>
        <button type="button" onclick="var i=document.createElement('input');i.name='export';i.value='1';i.type='hidden';this.form.appendChild(i);this.form.submit();" class="inline-flex items-center gap-2 bg-emerald-700 hover:bg-emerald-800 text-white font-medium px-4 py-2 rounded-xl text-sm transition-all shadow-sm cursor-pointer"><i class="fas fa-file-csv"></i>Export CSV</button>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border border-stone-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all">
            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider mb-1">Total Bookings</p>
            <p class="text-3xl font-bold text-amber-700"><?php echo count($dailyBookings); ?></p>
        </div>
        <div class="bg-white border border-stone-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all">
            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider mb-1">Total Revenue</p>
            <p class="text-3xl font-bold text-emerald-700"><?php echo formatCurrency($dailyTotal); ?></p>
        </div>
        <div class="bg-white border border-stone-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all">
            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider mb-1">Guests Today</p>
            <p class="text-3xl font-bold text-stone-800"><?php echo array_sum(array_column($dailyBookings, 'total_guests')); ?></p>
        </div>
    </div>

    <?php if ($dailyBookings): ?>
    <div class="bg-white border border-stone-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-stone-100/80 border-b border-stone-200 text-stone-600 text-xs font-semibold uppercase tracking-wider px-4">
                        <th class="text-left py-3.5 px-4">ID</th>
                        <th class="text-left py-3.5 px-4">Guest</th>
                        <th class="text-left py-3.5 px-4">Room</th>
                        <th class="text-left py-3.5 px-4">Type</th>
                        <th class="text-left py-3.5 px-4">Check In</th>
                        <th class="text-left py-3.5 px-4">Check Out</th>
                        <th class="text-center py-3.5 px-4">Guests</th>
                        <th class="text-right py-3.5 px-4">Total</th>
                        <th class="text-center py-3.5 px-4">Status</th>
                        <th class="text-center py-3.5 px-4">Payment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dailyBookings as $b): ?>
                    <tr class="hover:bg-amber-50/30 transition-colors border-b border-stone-100 last:border-none">
                        <td class="py-3 px-4 font-semibold text-stone-800">#<?php echo $b['reservation_id']; ?></td>
                        <td class="py-3 px-4 text-stone-700"><?php echo sanitize($b['first_name'] . ' ' . $b['last_name']); ?></td>
                        <td class="py-3 px-4 text-stone-700"><?php echo sanitize($b['room_number']); ?></td>
                        <td class="py-3 px-4 text-stone-700"><?php echo sanitize($b['type_name']); ?></td>
                        <td class="py-3 px-4 text-stone-600"><?php echo formatDate($b['check_in_date']); ?></td>
                        <td class="py-3 px-4 text-stone-600"><?php echo formatDate($b['check_out_date']); ?></td>
                        <td class="py-3 px-4 text-center text-stone-700"><?php echo $b['total_guests']; ?></td>
                        <td class="py-3 px-4 text-right font-semibold text-stone-800"><?php echo formatCurrency($b['total_price']); ?></td>
                        <td class="py-3 px-4 text-center"><span class="badge-status badge-<?php echo badgeClass($b['booking_status']); ?>"><?php echo $b['booking_status']; ?></span></td>
                        <td class="py-3 px-4 text-center"><span class="badge-status badge-<?php echo badgeClass($b['payment_status']); ?>"><?php echo $b['payment_status']; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-stone-50 font-bold border-t border-stone-200">
                        <td colspan="7" class="py-3 px-4 text-right text-stone-600">Total:</td>
                        <td class="py-3 px-4 text-right text-stone-800"><?php echo formatCurrency($dailyTotal); ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-white border border-stone-200/80 rounded-2xl shadow-sm text-center py-12 text-stone-400">
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
<div class="mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-4">
        <h2 class="text-xl font-bold text-stone-800"><i class="fas fa-calendar-alt text-amber-600 mr-2"></i>Monthly Booking Report</h2>
    </div>
    <form method="GET" class="bg-white p-4 rounded-2xl border border-stone-200 shadow-sm flex flex-wrap items-center justify-between gap-4 mb-6">
        <input type="hidden" name="tab" value="monthly">
        <div class="flex items-center gap-3">
            <select name="month" class="border border-stone-300 rounded-xl px-3.5 py-2 text-sm text-stone-800 bg-stone-50 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 outline-none transition-all">
                <?php foreach ($months as $val => $label): ?>
                <option value="<?php echo $val; ?>" <?php echo $month === $val ? 'selected' : ''; ?>><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
            <select name="year" class="border border-stone-300 rounded-xl px-3.5 py-2 text-sm text-stone-800 bg-stone-50 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 outline-none transition-all">
                <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                <option value="<?php echo $y; ?>" <?php echo (int)$year === $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="bg-amber-700 hover:bg-amber-800 text-white font-medium px-5 py-2 rounded-xl text-sm transition-all shadow-sm"><i class="fas fa-search mr-1"></i>View</button>
        </div>
        <button type="button" onclick="var i=document.createElement('input');i.name='export';i.value='1';i.type='hidden';this.form.appendChild(i);this.form.submit();" class="inline-flex items-center gap-2 bg-emerald-700 hover:bg-emerald-800 text-white font-medium px-4 py-2 rounded-xl text-sm transition-all shadow-sm cursor-pointer"><i class="fas fa-file-csv"></i>Export CSV</button>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border border-stone-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all">
            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider mb-1">Total Bookings (Active)</p>
            <p class="text-3xl font-bold text-amber-700"><?php echo $monthlyCount; ?></p>
        </div>
        <div class="bg-white border border-stone-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all">
            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider mb-1">Total Revenue</p>
            <p class="text-3xl font-bold text-emerald-700"><?php echo formatCurrency($monthlyTotal); ?></p>
        </div>
        <div class="bg-white border border-stone-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all">
            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider mb-1">Avg Booking Value</p>
            <p class="text-3xl font-bold text-stone-800"><?php echo $monthlyCount > 0 ? formatCurrency($monthlyTotal / $monthlyCount) : formatCurrency(0); ?></p>
        </div>
    </div>

    <?php if ($monthlyBookings): ?>
    <div class="bg-white border border-stone-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-stone-100/80 border-b border-stone-200 text-stone-600 text-xs font-semibold uppercase tracking-wider px-4">
                        <th class="text-left py-3.5 px-4">ID</th>
                        <th class="text-left py-3.5 px-4">Guest</th>
                        <th class="text-left py-3.5 px-4">Room</th>
                        <th class="text-left py-3.5 px-4">Type</th>
                        <th class="text-left py-3.5 px-4">Check In</th>
                        <th class="text-left py-3.5 px-4">Check Out</th>
                        <th class="text-center py-3.5 px-4">Guests</th>
                        <th class="text-right py-3.5 px-4">Total</th>
                        <th class="text-center py-3.5 px-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($monthlyBookings as $b): ?>
                    <tr class="hover:bg-amber-50/30 transition-colors border-b border-stone-100 last:border-none">
                        <td class="py-3 px-4 font-semibold text-stone-800">#<?php echo $b['reservation_id']; ?></td>
                        <td class="py-3 px-4 text-stone-700"><?php echo sanitize($b['first_name'] . ' ' . $b['last_name']); ?></td>
                        <td class="py-3 px-4 text-stone-700"><?php echo sanitize($b['room_number']); ?></td>
                        <td class="py-3 px-4 text-stone-700"><?php echo sanitize($b['type_name']); ?></td>
                        <td class="py-3 px-4 text-stone-600"><?php echo formatDate($b['check_in_date']); ?></td>
                        <td class="py-3 px-4 text-stone-600"><?php echo formatDate($b['check_out_date']); ?></td>
                        <td class="py-3 px-4 text-center text-stone-700"><?php echo $b['total_guests']; ?></td>
                        <td class="py-3 px-4 text-right font-semibold text-stone-800"><?php echo formatCurrency($b['total_price']); ?></td>
                        <td class="py-3 px-4 text-center"><span class="badge-status badge-<?php echo badgeClass($b['booking_status']); ?>"><?php echo $b['booking_status']; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-stone-50 font-bold border-t border-stone-200">
                        <td colspan="7" class="py-3 px-4 text-right text-stone-600">Total (Active):</td>
                        <td class="py-3 px-4 text-right text-stone-800"><?php echo formatCurrency($monthlyTotal); ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-white border border-stone-200/80 rounded-2xl shadow-sm text-center py-12 text-stone-400">
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
<div class="mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-4">
        <h2 class="text-xl font-bold text-stone-800"><i class="fas fa-calendar text-amber-600 mr-2"></i>Yearly Report - <?php echo $yearlyYear; ?></h2>
    </div>
    <form method="GET" class="bg-white p-4 rounded-2xl border border-stone-200 shadow-sm flex flex-wrap items-center justify-between gap-4 mb-6">
        <input type="hidden" name="tab" value="yearly">
        <div class="flex items-center gap-3">
            <select name="year" class="border border-stone-300 rounded-xl px-3.5 py-2 text-sm text-stone-800 bg-stone-50 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 outline-none transition-all">
                <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                <option value="<?php echo $y; ?>" <?php echo $yearlyYear === $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="bg-amber-700 hover:bg-amber-800 text-white font-medium px-5 py-2 rounded-xl text-sm transition-all shadow-sm"><i class="fas fa-search mr-1"></i>View</button>
        </div>
        <button type="button" onclick="var i=document.createElement('input');i.name='export';i.value='1';i.type='hidden';this.form.appendChild(i);this.form.submit();" class="inline-flex items-center gap-2 bg-emerald-700 hover:bg-emerald-800 text-white font-medium px-4 py-2 rounded-xl text-sm transition-all shadow-sm cursor-pointer"><i class="fas fa-file-csv"></i>Export CSV</button>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-stone-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all">
            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider mb-1">Total Bookings</p>
            <p class="text-3xl font-bold text-amber-700"><?php echo $yearlyBookings; ?></p>
        </div>
        <div class="bg-white border border-stone-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all">
            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider mb-1">Total Revenue</p>
            <p class="text-3xl font-bold text-emerald-700"><?php echo formatCurrency($yearlyRevenue); ?></p>
        </div>
        <div class="bg-white border border-stone-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all">
            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider mb-1">Unique Guests</p>
            <p class="text-3xl font-bold text-stone-800"><?php echo $yearlyGuests; ?></p>
        </div>
        <div class="bg-white border border-stone-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all">
            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider mb-1">Avg Monthly Revenue</p>
            <p class="text-3xl font-bold text-stone-800"><?php echo count($yearlyData) > 0 ? formatCurrency($yearlyRevenue / count($yearlyData)) : formatCurrency(0); ?></p>
        </div>
    </div>

    <?php if ($yearlyData): ?>
    <div class="space-y-4 mb-6">
        <?php foreach ($yearlyData as $yd):
            $barPct = $maxRevenue > 0 ? ($yd['total_revenue'] / $maxRevenue) * 100 : 0;
        ?>
        <div>
            <div class="flex items-center justify-between text-sm mb-1">
                <span class="font-semibold text-stone-700"><?php echo date('F', strtotime($yd['month'] . '-01')); ?></span>
                <span class="text-stone-500"><?php echo $yd['total_bookings']; ?> bookings &mdash; <?php echo formatCurrency($yd['total_revenue']); ?></span>
            </div>
            <div class="w-full bg-stone-200 rounded-full h-3">
                <div class="bg-amber-500 h-3 rounded-full transition-all" style="width: <?php echo $barPct; ?>%"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="bg-white border border-stone-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-stone-100/80 border-b border-stone-200 text-stone-600 text-xs font-semibold uppercase tracking-wider px-4">
                        <th class="text-left py-3.5 px-4">Month</th>
                        <th class="text-right py-3.5 px-4">Bookings</th>
                        <th class="text-right py-3.5 px-4">Revenue</th>
                        <th class="text-right py-3.5 px-4">Avg Value</th>
                        <th class="text-right py-3.5 px-4">Unique Guests</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($yearlyData as $yd): ?>
                    <tr class="hover:bg-amber-50/30 transition-colors border-b border-stone-100 last:border-none">
                        <td class="py-3 px-4 font-semibold text-stone-800"><?php echo date('F Y', strtotime($yd['month'] . '-01')); ?></td>
                        <td class="py-3 px-4 text-right text-stone-700"><?php echo $yd['total_bookings']; ?></td>
                        <td class="py-3 px-4 text-right font-semibold text-emerald-700"><?php echo formatCurrency($yd['total_revenue']); ?></td>
                        <td class="py-3 px-4 text-right text-stone-700"><?php echo formatCurrency($yd['avg_value']); ?></td>
                        <td class="py-3 px-4 text-right text-stone-700"><?php echo $yd['unique_guests']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-stone-50 font-bold border-t border-stone-200">
                        <td class="py-3 px-4 text-stone-600">Total</td>
                        <td class="py-3 px-4 text-right text-stone-800"><?php echo $yearlyBookings; ?></td>
                        <td class="py-3 px-4 text-right text-emerald-700"><?php echo formatCurrency($yearlyRevenue); ?></td>
                        <td class="py-3 px-4 text-right text-stone-800"><?php echo $yearlyBookings > 0 ? formatCurrency($yearlyRevenue / $yearlyBookings) : formatCurrency(0); ?></td>
                        <td class="py-3 px-4 text-right text-stone-800"><?php echo $yearlyGuests; ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-white border border-stone-200/80 rounded-2xl shadow-sm text-center py-12 text-stone-400">
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
<div class="mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-4">
        <h2 class="text-xl font-bold text-stone-800"><i class="fas fa-dollar-sign text-amber-600 mr-2"></i>Revenue Report</h2>
    </div>
    <form method="GET" class="bg-white p-4 rounded-2xl border border-stone-200 shadow-sm flex flex-wrap items-center justify-between gap-4 mb-6">
        <input type="hidden" name="tab" value="revenue">
        <div class="flex items-center gap-3">
            <label class="text-sm text-stone-600 font-medium">From:</label>
            <input type="date" name="start_date" value="<?php echo $startDate; ?>" class="border border-stone-300 rounded-xl px-3.5 py-2 text-sm text-stone-800 bg-stone-50 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 outline-none transition-all">
            <label class="text-sm text-stone-600 font-medium">To:</label>
            <input type="date" name="end_date" value="<?php echo $endDate; ?>" class="border border-stone-300 rounded-xl px-3.5 py-2 text-sm text-stone-800 bg-stone-50 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 outline-none transition-all">
            <button type="submit" class="bg-amber-700 hover:bg-amber-800 text-white font-medium px-5 py-2 rounded-xl text-sm transition-all shadow-sm"><i class="fas fa-search mr-1"></i>View</button>
        </div>
        <button type="button" onclick="var i=document.createElement('input');i.name='export';i.value='1';i.type='hidden';this.form.appendChild(i);this.form.submit();" class="inline-flex items-center gap-2 bg-emerald-700 hover:bg-emerald-800 text-white font-medium px-4 py-2 rounded-xl text-sm transition-all shadow-sm cursor-pointer"><i class="fas fa-file-csv"></i>Export CSV</button>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-stone-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all">
            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider mb-1">Total Revenue</p>
            <p class="text-2xl font-bold text-emerald-700"><?php echo formatCurrency($totalRevenue); ?></p>
        </div>
        <div class="bg-white border border-stone-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all">
            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider mb-1">Paid</p>
            <p class="text-2xl font-bold text-amber-700"><?php echo formatCurrency($totalPaid); ?></p>
        </div>
        <div class="bg-white border border-stone-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all">
            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider mb-1">Unpaid</p>
            <p class="text-2xl font-bold text-stone-800"><?php echo formatCurrency($totalUnpaid); ?></p>
        </div>
        <div class="bg-white border border-stone-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all">
            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider mb-1">Refunded</p>
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
                <span class="font-semibold text-stone-700"><?php echo formatDate($rd['date']); ?></span>
                <span class="text-stone-500"><?php echo $rd['active_bookings']; ?> bookings &mdash; <?php echo formatCurrency($rd['revenue']); ?></span>
            </div>
            <div class="w-full bg-stone-200 rounded-full h-2.5">
                <div class="bg-emerald-500 h-2.5 rounded-full transition-all" style="width: <?php echo $barPct; ?>%"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="bg-white border border-stone-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-stone-100/80 border-b border-stone-200 text-stone-600 text-xs font-semibold uppercase tracking-wider px-4">
                        <th class="text-left py-3.5 px-4">Date</th>
                        <th class="text-right py-3.5 px-4">Active Bookings</th>
                        <th class="text-right py-3.5 px-4">Revenue</th>
                        <th class="text-right py-3.5 px-4">Paid</th>
                        <th class="text-right py-3.5 px-4">Pending</th>
                        <th class="text-right py-3.5 px-4">Refunded</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($revenueData as $rd): ?>
                    <tr class="hover:bg-amber-50/30 transition-colors border-b border-stone-100 last:border-none">
                        <td class="py-3 px-4 font-semibold text-stone-800"><?php echo formatDate($rd['date']); ?></td>
                        <td class="py-3 px-4 text-right text-stone-700"><?php echo $rd['active_bookings']; ?></td>
                        <td class="py-3 px-4 text-right font-semibold text-emerald-700"><?php echo formatCurrency($rd['revenue']); ?></td>
                        <td class="py-3 px-4 text-right text-amber-700"><?php echo formatCurrency($rd['paid']); ?></td>
                        <td class="py-3 px-4 text-right text-stone-600"><?php echo formatCurrency($rd['unpaid']); ?></td>
                        <td class="py-3 px-4 text-right text-red-600"><?php echo formatCurrency($rd['refunded']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-stone-50 font-bold border-t border-stone-200">
                        <td class="py-3 px-4 text-stone-600">Total</td>
                        <td class="py-3 px-4 text-right text-stone-800"><?php echo $totalActive; ?></td>
                        <td class="py-3 px-4 text-right text-emerald-700"><?php echo formatCurrency($totalRevenue); ?></td>
                        <td class="py-3 px-4 text-right text-amber-700"><?php echo formatCurrency($totalPaid); ?></td>
                        <td class="py-3 px-4 text-right text-stone-600"><?php echo formatCurrency($totalUnpaid); ?></td>
                        <td class="py-3 px-4 text-right text-red-600"><?php echo formatCurrency($totalRefunded); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-white border border-stone-200/80 rounded-2xl shadow-sm text-center py-12 text-stone-400">
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
<div class="mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-4">
        <h2 class="text-xl font-bold text-stone-800"><i class="fas fa-bed text-amber-600 mr-2"></i>Occupancy Report</h2>
    </div>
    <form method="GET" class="bg-white p-4 rounded-2xl border border-stone-200 shadow-sm flex flex-wrap items-center justify-between gap-4 mb-6">
        <input type="hidden" name="tab" value="occupancy">
        <div class="flex items-center gap-3">
            <label class="text-sm text-stone-600 font-medium">Date:</label>
            <input type="date" name="occ_date" value="<?php echo $occDate; ?>" class="border border-stone-300 rounded-xl px-3.5 py-2 text-sm text-stone-800 bg-stone-50 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 outline-none transition-all">
            <button type="submit" class="bg-amber-700 hover:bg-amber-800 text-white font-medium px-5 py-2 rounded-xl text-sm transition-all shadow-sm"><i class="fas fa-search mr-1"></i>View</button>
        </div>
        <button type="button" onclick="var i=document.createElement('input');i.name='export';i.value='1';i.type='hidden';this.form.appendChild(i);this.form.submit();" class="inline-flex items-center gap-2 bg-emerald-700 hover:bg-emerald-800 text-white font-medium px-4 py-2 rounded-xl text-sm transition-all shadow-sm cursor-pointer"><i class="fas fa-file-csv"></i>Export CSV</button>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border border-stone-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all">
            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider mb-1">Total Rooms</p>
            <p class="text-3xl font-bold text-amber-700"><?php echo $stmtTotal; ?></p>
        </div>
        <div class="bg-white border border-stone-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all">
            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider mb-1">Occupied</p>
            <p class="text-3xl font-bold text-emerald-700"><?php echo $totalOccupied; ?></p>
        </div>
        <div class="bg-white border border-stone-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all">
            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider mb-1">Occupancy Rate</p>
            <p class="text-3xl font-bold text-stone-800"><?php echo $overallPct; ?>%</p>
        </div>
    </div>

    <?php if ($overallPct > 0): ?>
    <div class="mb-6">
        <div class="flex items-center justify-between text-sm mb-1">
            <span class="font-semibold text-stone-700">Overall Occupancy</span>
            <span class="text-stone-500"><?php echo $totalOccupied; ?> / <?php echo $stmtTotal; ?> rooms (<?php echo $overallPct; ?>%)</span>
        </div>
        <div class="w-full bg-stone-200 rounded-full h-4">
            <div class="bg-gradient-to-r from-emerald-400 via-amber-400 to-red-500 h-4 rounded-full transition-all" style="width: <?php echo min($overallPct, 100); ?>%"></div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($occData): ?>
    <div class="bg-white border border-stone-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-stone-100/80 border-b border-stone-200 text-stone-600 text-xs font-semibold uppercase tracking-wider px-4">
                        <th class="text-left py-3.5 px-4">Room Type</th>
                        <th class="text-right py-3.5 px-4">Total Rooms</th>
                        <th class="text-right py-3.5 px-4">Occupied</th>
                        <th class="text-right py-3.5 px-4">Available</th>
                        <th class="text-right py-3.5 px-4">Occupancy %</th>
                        <th class="py-3.5 px-4">Visual</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($occData as $od):
                        $avail = $od['total_rooms'] - $od['occupied_rooms'];
                        $pct = $od['total_rooms'] > 0 ? round(($od['occupied_rooms'] / $od['total_rooms']) * 100, 1) : 0;
                        $barColor = $pct > 75 ? 'bg-red-500' : ($pct > 50 ? 'bg-amber-500' : 'bg-emerald-500');
                    ?>
                    <tr class="hover:bg-amber-50/30 transition-colors border-b border-stone-100 last:border-none">
                        <td class="py-3 px-4 font-semibold text-stone-800"><?php echo sanitize($od['type_name']); ?></td>
                        <td class="py-3 px-4 text-right text-stone-700"><?php echo $od['total_rooms']; ?></td>
                        <td class="py-3 px-4 text-right text-emerald-700 font-semibold"><?php echo $od['occupied_rooms']; ?></td>
                        <td class="py-3 px-4 text-right text-stone-700"><?php echo $avail; ?></td>
                        <td class="py-3 px-4 text-right font-semibold text-stone-800"><?php echo $pct; ?>%</td>
                        <td class="py-3 px-4">
                            <div class="w-full bg-stone-200 rounded-full h-2.5">
                                <div class="<?php echo $barColor; ?> h-2.5 rounded-full" style="width: <?php echo min($pct, 100); ?>%"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-stone-50 font-bold border-t border-stone-200">
                        <td class="py-3 px-4 text-stone-600">Total</td>
                        <td class="py-3 px-4 text-right text-stone-800"><?php echo $stmtTotal; ?></td>
                        <td class="py-3 px-4 text-right text-emerald-700"><?php echo $totalOccupied; ?></td>
                        <td class="py-3 px-4 text-right text-stone-800"><?php echo $stmtTotal - $totalOccupied; ?></td>
                        <td class="py-3 px-4 text-right text-stone-800"><?php echo $overallPct; ?>%</td>
                        <td class="py-3 px-4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-white border border-stone-200/80 rounded-2xl shadow-sm text-center py-12 text-stone-400">
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
<div class="mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-4">
        <h2 class="text-xl font-bold text-stone-800"><i class="fas fa-users text-amber-600 mr-2"></i>Customer Registration Report</h2>
    </div>
    <form method="GET" class="bg-white p-4 rounded-2xl border border-stone-200 shadow-sm flex flex-wrap items-center justify-between gap-4 mb-6">
        <input type="hidden" name="tab" value="customers">
        <div class="flex items-center gap-3">
            <select name="year" class="border border-stone-300 rounded-xl px-3.5 py-2 text-sm text-stone-800 bg-stone-50 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 outline-none transition-all">
                <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                <option value="<?php echo $y; ?>" <?php echo $custYear === $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="bg-amber-700 hover:bg-amber-800 text-white font-medium px-5 py-2 rounded-xl text-sm transition-all shadow-sm"><i class="fas fa-search mr-1"></i>View</button>
        </div>
        <button type="button" onclick="var i=document.createElement('input');i.name='export';i.value='1';i.type='hidden';this.form.appendChild(i);this.form.submit();" class="inline-flex items-center gap-2 bg-emerald-700 hover:bg-emerald-800 text-white font-medium px-4 py-2 rounded-xl text-sm transition-all shadow-sm cursor-pointer"><i class="fas fa-file-csv"></i>Export CSV</button>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border border-stone-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all">
            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider mb-1">Total Customers</p>
            <p class="text-3xl font-bold text-amber-700"><?php echo $stmtTotalUsers; ?></p>
        </div>
        <div class="bg-white border border-stone-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all">
            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider mb-1">Active Customers</p>
            <p class="text-3xl font-bold text-emerald-700"><?php echo $stmtActiveUsers; ?></p>
        </div>
        <div class="bg-white border border-stone-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all">
            <p class="text-xs font-medium text-stone-500 uppercase tracking-wider mb-1">New in <?php echo $custYear; ?></p>
            <p class="text-3xl font-bold text-stone-800"><?php echo $totalRegistrations; ?></p>
        </div>
    </div>

    <?php if ($custData): ?>
    <div class="space-y-3 mb-6">
        <?php foreach ($custData as $cd):
            $barPct = $maxCust > 0 ? ($cd['total'] / $maxCust) * 100 : 0;
        ?>
        <div>
            <div class="flex items-center justify-between text-sm mb-1">
                <span class="font-semibold text-stone-700"><?php echo date('F Y', strtotime($cd['month'] . '-01')); ?></span>
                <span class="text-stone-500"><?php echo $cd['total']; ?> new registrations</span>
            </div>
            <div class="w-full bg-stone-200 rounded-full h-3">
                <div class="bg-amber-500 h-3 rounded-full transition-all" style="width: <?php echo $barPct; ?>%"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="bg-white border border-stone-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-stone-100/80 border-b border-stone-200 text-stone-600 text-xs font-semibold uppercase tracking-wider px-4">
                        <th class="text-left py-3.5 px-4">Month</th>
                        <th class="text-right py-3.5 px-4">New Registrations</th>
                        <th class="py-3.5 px-4">Visual</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($custData as $cd):
                        $barPct = $maxCust > 0 ? ($cd['total'] / $maxCust) * 100 : 0;
                    ?>
                    <tr class="hover:bg-amber-50/30 transition-colors border-b border-stone-100 last:border-none">
                        <td class="py-3 px-4 font-semibold text-stone-800"><?php echo date('F Y', strtotime($cd['month'] . '-01')); ?></td>
                        <td class="py-3 px-4 text-right text-stone-700"><?php echo $cd['total']; ?></td>
                        <td class="py-3 px-4">
                            <div class="w-full bg-stone-200 rounded-full h-2.5">
                                <div class="bg-amber-500 h-2.5 rounded-full" style="width: <?php echo $barPct; ?>%"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-stone-50 font-bold border-t border-stone-200">
                        <td class="py-3 px-4 text-stone-600">Total</td>
                        <td class="py-3 px-4 text-right text-stone-800"><?php echo $totalRegistrations; ?></td>
                        <td class="py-3 px-4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-white border border-stone-200/80 rounded-2xl shadow-sm text-center py-12 text-stone-400">
        <i class="fas fa-users text-5xl mb-4"></i>
        <p class="text-lg font-medium">No registration data for <?php echo $custYear; ?>.</p>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php logActivity($pdo, $userId, 'Viewed Reports', 'Viewed tab: ' . $tab); ?>
<?php include '../../includes/admin-footer.php'; ?>
