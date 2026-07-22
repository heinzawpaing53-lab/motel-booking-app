<?php
require_once '../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}
define('PAGE_TITLE', 'Dashboard');
include 'header.php';
?>

<div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
                <p class="text-gray-500 text-sm">Welcome back, <?php echo htmlspecialchars($_SESSION['first_name'] ?? 'Admin'); ?>!</p>
            </div>
        </div>

        <?php
        $totalRooms = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$availableRooms = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'Available' AND room_id NOT IN (SELECT room_id FROM reservations WHERE booking_status = 'Checked In' AND check_in_date <= CURDATE() AND check_out_date > CURDATE())")->fetchColumn();
$occupiedRooms = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'Occupied' OR room_id IN (SELECT room_id FROM reservations WHERE booking_status = 'Checked In' AND check_in_date <= CURDATE() AND check_out_date > CURDATE())")->fetchColumn();
$reservedRooms = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'Reserved'")->fetchColumn();
        $totalCustomers = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id = 2")->fetchColumn();
        $totalBookings = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
$todayCheckins = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE check_in_date = CURDATE() AND booking_status IN ('Approved', 'Checked In')");
$todayCheckins->execute();
$todayCheckinsCount = $todayCheckins->fetchColumn();
$todayCheckouts = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE check_out_date = CURDATE() AND booking_status IN ('Checked In', 'Checked Out')");
$todayCheckouts->execute();
$todayCheckoutsCount = $todayCheckouts->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM reservations WHERE booking_status IN ('Checked Out', 'Completed') AND payment_status = 'Paid'")->fetchColumn();
        ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <a href="rooms/index.php" class="block bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Rooms</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?php echo $totalRooms; ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center"><i class="fas fa-bed text-blue-600 text-xl"></i></div>
                </div>
            </a>
            <a href="rooms/index.php?status=Available" class="block bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Available Rooms</p>
                        <h3 class="text-3xl font-bold text-green-600"><?php echo $availableRooms; ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center"><i class="fas fa-check-circle text-green-600 text-xl"></i></div>
                </div>
            </a>
            <a href="rooms/index.php?status=Reserved" class="block bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Reserved Rooms</p>
                        <h3 class="text-3xl font-bold text-yellow-600"><?php echo $reservedRooms; ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center"><i class="fas fa-clock text-yellow-600 text-xl"></i></div>
                </div>
            </a>
            <a href="rooms/index.php?status=Occupied" class="block bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Occupied Rooms</p>
                        <h3 class="text-3xl font-bold text-red-600"><?php echo $occupiedRooms; ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center"><i class="fas fa-user-check text-red-600 text-xl"></i></div>
                </div>
            </a>
            <a href="users/index.php" class="block bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Customers</p>
                        <h3 class="text-3xl font-bold text-purple-600"><?php echo $totalCustomers; ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center"><i class="fas fa-users text-purple-600 text-xl"></i></div>
                </div>
            </a>
            <a href="bookings/index.php" class="block bg-white rounded-xl shadow-sm p-6 border-l-4 border-indigo-500 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Bookings</p>
                        <h3 class="text-3xl font-bold text-indigo-600"><?php echo $totalBookings; ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center"><i class="fas fa-calendar-check text-indigo-600 text-xl"></i></div>
                </div>
            </a>
            <a href="bookings/index.php?filter=checkins_today" class="block bg-white rounded-xl shadow-sm p-6 border-l-4 border-teal-500 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Today's Check-ins</p>
                        <h3 class="text-3xl font-bold text-teal-600"><?php echo $todayCheckinsCount; ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center"><i class="fas fa-sign-in-alt text-teal-600 text-xl"></i></div>
                </div>
            </a>
            <a href="bookings/index.php?filter=checkouts_today" class="block bg-white rounded-xl shadow-sm p-6 border-l-4 border-cyan-500 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Today's Check-outs</p>
                        <h3 class="text-3xl font-bold text-cyan-600"><?php echo $todayCheckoutsCount; ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center"><i class="fas fa-sign-out-alt text-cyan-600 text-xl"></i></div>
                </div>
            </a>
            <a href="bookings/payments.php" class="block bg-white rounded-xl shadow-sm p-6 border-l-4 border-emerald-500 lg:col-span-1 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Revenue</p>
                        <h3 class="text-3xl font-bold text-emerald-600"><?php echo formatCurrency($totalRevenue); ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center"><i class="fas fa-dollar-sign text-emerald-600 text-xl"></i></div>
                </div>
            </a>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
            <div class="xl:col-span-2 bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Reservations</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="pb-3 font-semibold">Guest</th>
                                <th class="pb-3 font-semibold">Room</th>
                                <th class="pb-3 font-semibold">Check In</th>
                                <th class="pb-3 font-semibold">Check Out</th>
                                <th class="pb-3 font-semibold">Status</th>
                                <th class="pb-3 font-semibold">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recentReservations = $pdo->query("
                                SELECT r.*, u.first_name, u.last_name, rm.room_number, rm.room_name
                                FROM reservations r
                                JOIN users u ON r.user_id = u.user_id
                                JOIN rooms rm ON r.room_id = rm.room_id
                                ORDER BY r.created_at DESC LIMIT 10
                            ")->fetchAll();
                            foreach ($recentReservations as $res): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3"><?php echo sanitize($res['first_name'] . ' ' . $res['last_name']); ?></td>
                                <td class="py-3"><?php echo sanitize($res['room_name'] ?: $res['room_number']); ?></td>
                                <td class="py-3"><?php echo formatDate($res['check_in_date']); ?></td>
                                <td class="py-3"><?php echo formatDate($res['check_out_date']); ?></td>
                                <td class="py-3"><span class="badge-status badge-<?php echo badgeClass($res['booking_status']); ?>"><?php echo $res['booking_status']; ?></span></td>
                                <td class="py-3 font-semibold"><?php echo formatCurrency($res['total_price']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentReservations)): ?>
                            <tr><td colspan="6" class="py-6 text-center text-gray-400">No reservations yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Booking Overview</h2>
                <div class="flex items-center justify-center h-64">
                    <canvas id="bookingChart" width="300" height="300"></canvas>
                </div>
                <p class="text-center text-gray-400 text-sm mt-4">Monthly booking trends chart</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('bookingChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Available', 'Occupied', 'Reserved', 'Maintenance'],
        datasets: [{
            data: [<?php echo "$availableRooms, $occupiedRooms, $reservedRooms, " . ($totalRooms - $availableRooms - $occupiedRooms - $reservedRooms); ?>],
            backgroundColor: ['#10b981', '#ef4444', '#3b82f6', '#f59e0b'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
    }
});
</script>
<?php include '../includes/admin-footer.php'; ?>
