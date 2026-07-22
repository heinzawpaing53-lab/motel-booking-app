<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}
define('PAGE_TITLE', 'Customer Details');

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ? AND role_id = 2");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    redirect('admin/users/index.php');
}

$bookingsStmt = $pdo->prepare("
    SELECT r.*, rm.room_number, rm.room_name
    FROM reservations r
    JOIN rooms rm ON r.room_id = rm.room_id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$bookingsStmt->execute([$id]);
$bookings = $bookingsStmt->fetchAll();

$totalSpent = $pdo->prepare("SELECT COALESCE(SUM(total_price), 0) FROM reservations WHERE user_id = ? AND booking_status IN ('Approved', 'Checked In', 'Checked Out', 'Completed')");
$totalSpent->execute([$id]);
$totalSpent = $totalSpent->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo PAGE_TITLE . ' - ' . SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
</head>
<body class="admin-layout font-[Inter] flex h-screen w-screen overflow-hidden bg-slate-100">
<?php include '../../includes/sidebar.php'; ?>
<div class="flex-1 flex flex-col h-full overflow-hidden">
<?php include '../../includes/admin-topbar.php'; ?>
<main class="flex-1 overflow-y-auto bg-slate-50">
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="index.php" class="text-blue-600 hover:text-blue-800 text-sm"><i class="fas fa-arrow-left mr-1"></i>Back to Customers</a>
            <h1 class="text-2xl font-bold text-gray-800 mt-1">Customer Details</h1>
        </div>
        <a href="edit.php?id=<?php echo $user['user_id']; ?>" class="px-5 py-2.5 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition font-semibold"><i class="fas fa-edit mr-2"></i>Edit Customer</a>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-user mr-2 text-blue-600"></i>Personal Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider">First Name</label>
                        <p class="text-gray-800 font-medium"><?php echo sanitize($user['first_name']); ?></p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider">Last Name</label>
                        <p class="text-gray-800 font-medium"><?php echo sanitize($user['last_name']); ?></p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider">Email</label>
                        <p class="text-gray-800"><?php echo sanitize($user['email']); ?></p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider">Phone</label>
                        <p class="text-gray-800"><?php echo sanitize($user['phone'] ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider">Gender</label>
                        <p class="text-gray-800"><?php echo sanitize($user['gender'] ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider">Nationality</label>
                        <p class="text-gray-800"><?php echo sanitize($user['nationality'] ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider">Status</label>
                        <p><span class="badge-status badge-<?php echo badgeClass($user['status']); ?>"><?php echo $user['status']; ?></span></p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider">Registered</label>
                        <p class="text-gray-800"><?php echo formatDate($user['created_at']); ?></p>
                    </div>
                </div>
                <?php if ($user['address']): ?>
                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <label class="text-xs text-gray-400 uppercase tracking-wider">Address</label>
                    <p class="text-gray-700 mt-1"><?php echo nl2br(sanitize($user['address'])); ?></p>
                </div>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-calendar-check mr-2 text-blue-600"></i>Booking History (<?php echo count($bookings); ?>)</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b bg-gray-50">
                                <th class="p-3 font-semibold">#</th>
                                <th class="p-3 font-semibold">Room</th>
                                <th class="p-3 font-semibold">Check In</th>
                                <th class="p-3 font-semibold">Check Out</th>
                                <th class="p-3 font-semibold">Total</th>
                                <th class="p-3 font-semibold">Status</th>
                                <th class="p-3 font-semibold">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $i => $b): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="p-3 text-gray-500"><?php echo $i + 1; ?></td>
                                <td class="p-3 font-medium"><?php echo sanitize($b['room_name'] ?: $b['room_number']); ?></td>
                                <td class="p-3"><?php echo formatDate($b['check_in_date']); ?></td>
                                <td class="p-3"><?php echo formatDate($b['check_out_date']); ?></td>
                                <td class="p-3 font-semibold"><?php echo formatCurrency($b['total_price']); ?></td>
                                <td class="p-3"><span class="badge-status badge-<?php echo badgeClass($b['booking_status']); ?>"><?php echo $b['booking_status']; ?></span></td>
                                <td class="p-3"><a href="../bookings/view.php?id=<?php echo $b['reservation_id']; ?>" class="text-blue-600 hover:text-blue-800 text-xs font-semibold">View</a></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($bookings)): ?>
                            <tr><td colspan="7" class="p-6 text-center text-gray-400">No bookings found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-chart-pie mr-2 text-blue-600"></i>Quick Stats</h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                        <div>
                            <p class="text-sm text-gray-500">Total Bookings</p>
                            <p class="text-2xl font-bold text-blue-600"><?php echo count($bookings); ?></p>
                        </div>
                        <i class="fas fa-calendar-check text-blue-400 text-3xl"></i>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                        <div>
                            <p class="text-sm text-gray-500">Total Spent</p>
                            <p class="text-2xl font-bold text-green-600"><?php echo formatCurrency($totalSpent); ?></p>
                        </div>
                        <i class="fas fa-dollar-sign text-green-400 text-3xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
