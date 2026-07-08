<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}
define('PAGE_TITLE', 'Bookings');
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
<body class="font-[Inter] bg-gray-50">
<?php include '../../includes/sidebar.php'; ?>

<div class="ml-64 p-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Bookings</h1>
            <p class="text-gray-500 text-sm">Manage all reservations</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-semibold text-gray-600 mb-1">Search</label>
                <input type="text" name="search" value="<?php echo sanitize($_GET['search'] ?? ''); ?>" placeholder="Search by guest name or email..." class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">Status</label>
                <select name="status" class="px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">All Statuses</option>
                    <option value="Pending" <?php echo ($_GET['status'] ?? '') === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Approved" <?php echo ($_GET['status'] ?? '') === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="Rejected" <?php echo ($_GET['status'] ?? '') === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                    <option value="Checked In" <?php echo ($_GET['status'] ?? '') === 'Checked In' ? 'selected' : ''; ?>>Checked In</option>
                    <option value="Checked Out" <?php echo ($_GET['status'] ?? '') === 'Checked Out' ? 'selected' : ''; ?>>Checked Out</option>
                    <option value="Cancelled" <?php echo ($_GET['status'] ?? '') === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    <option value="Completed" <?php echo ($_GET['status'] ?? '') === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"><i class="fas fa-search mr-2"></i>Filter</button>
            <a href="index.php" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition"><i class="fas fa-times mr-2"></i>Clear</a>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b bg-gray-50">
                        <th class="p-4 font-semibold">Guest</th>
                        <th class="p-4 font-semibold">Room</th>
                        <th class="p-4 font-semibold">Check In</th>
                        <th class="p-4 font-semibold">Check Out</th>
                        <th class="p-4 font-semibold">Nights</th>
                        <th class="p-4 font-semibold">Total</th>
                        <th class="p-4 font-semibold">Payment</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $conditions = [];
                    $params = [];

                    if (!empty($_GET['search'])) {
                        $search = '%' . $_GET['search'] . '%';
                        $conditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR u.email LIKE ?)";
                        $params = array_merge($params, [$search, $search, $search, $search]);
                    }

                    if (!empty($_GET['status'])) {
                        $conditions[] = "r.booking_status = ?";
                        $params[] = $_GET['status'];
                    }

                    $where = '';
                    if (!empty($conditions)) {
                        $where = 'WHERE ' . implode(' AND ', $conditions);
                    }

                    $sql = "SELECT r.*, u.first_name, u.last_name, u.email, rm.room_number, rm.room_name
                            FROM reservations r
                            JOIN users u ON r.user_id = u.user_id
                            JOIN rooms rm ON r.room_id = rm.room_id
                            $where
                            ORDER BY r.created_at DESC";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $bookings = $stmt->fetchAll();

                    foreach ($bookings as $b):
                    ?>
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="p-4">
                            <div class="font-semibold text-gray-800"><?php echo sanitize($b['first_name'] . ' ' . $b['last_name']); ?></div>
                            <div class="text-gray-400 text-xs"><?php echo sanitize($b['email']); ?></div>
                        </td>
                        <td class="p-4"><?php echo sanitize($b['room_name'] ?: $b['room_number']); ?></td>
                        <td class="p-4"><?php echo formatDate($b['check_in_date']); ?></td>
                        <td class="p-4"><?php echo formatDate($b['check_out_date']); ?></td>
                        <td class="p-4"><?php echo $b['total_nights']; ?></td>
                        <td class="p-4 font-semibold"><?php echo formatCurrency($b['total_price']); ?></td>
                        <td class="p-4">
                            <span class="badge-status <?php
                                echo $b['payment_status'] === 'Paid' ? 'badge-approved' : ($b['payment_status'] === 'Refunded' ? 'badge-cancelled' : 'badge-pending');
                            ?>"><?php echo $b['payment_status']; ?></span>
                        </td>
                        <td class="p-4">
                            <span class="badge-status badge-<?php echo badgeClass($b['booking_status']); ?>"><?php echo $b['booking_status']; ?></span>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center space-x-2">
                                <a href="view.php?id=<?php echo $b['reservation_id']; ?>" class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition text-xs font-semibold"><i class="fas fa-eye mr-1"></i>View</a>
                                <?php if ($b['booking_status'] === 'Pending'): ?>
                                    <a href="action.php?action=approve&id=<?php echo $b['reservation_id']; ?>" class="px-3 py-1.5 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition text-xs font-semibold" onclick="return confirm('Approve this booking?')"><i class="fas fa-check mr-1"></i>Approve</a>
                                    <a href="action.php?action=reject&id=<?php echo $b['reservation_id']; ?>" class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition text-xs font-semibold" onclick="return confirm('Reject this booking?')"><i class="fas fa-times mr-1"></i>Reject</a>
                                <?php elseif ($b['booking_status'] === 'Approved'): ?>
                                    <a href="action.php?action=checkin&id=<?php echo $b['reservation_id']; ?>" class="px-3 py-1.5 bg-teal-100 text-teal-700 rounded-lg hover:bg-teal-200 transition text-xs font-semibold" onclick="return confirm('Mark as checked in?')"><i class="fas fa-sign-in-alt mr-1"></i>Check In</a>
                                    <a href="action.php?action=cancel&id=<?php echo $b['reservation_id']; ?>" class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition text-xs font-semibold" onclick="return confirm('Cancel this booking?')"><i class="fas fa-ban mr-1"></i>Cancel</a>
                                <?php elseif ($b['booking_status'] === 'Checked In'): ?>
                                    <a href="action.php?action=checkout&id=<?php echo $b['reservation_id']; ?>" class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition text-xs font-semibold" onclick="return confirm('Mark as checked out?')"><i class="fas fa-sign-out-alt mr-1"></i>Check Out</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($bookings)): ?>
                    <tr><td colspan="9" class="p-8 text-center text-gray-400">No bookings found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
