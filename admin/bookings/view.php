<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}
define('PAGE_TITLE', 'Booking Details');

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
    SELECT r.*, u.first_name, u.last_name, u.email, u.phone, u.gender, u.nationality, u.address,
           rm.room_number, rm.room_name, rt.type_name, rt.price_per_night, rt.bed_type, rt.room_size
    FROM reservations r
    JOIN users u ON r.user_id = u.user_id
    JOIN rooms rm ON r.room_id = rm.room_id
    JOIN room_types rt ON rm.type_id = rt.type_id
    WHERE r.reservation_id = ?
");
$stmt->execute([$id]);
$booking = $stmt->fetch();

if (!$booking) {
    redirect('admin/bookings/index.php');
}

$specialRequests = $pdo->prepare("
    SELECT sr.*, rr.remarks
    FROM reservation_requests rr
    JOIN special_requests sr ON rr.request_id = sr.request_id
    WHERE rr.reservation_id = ?
");
$specialRequests->execute([$id]);
$specialRequests = $specialRequests->fetchAll();

$messages = [];
if (isset($_SESSION['success'])) {
    $messages['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $messages['error'] = $_SESSION['error'];
    unset($_SESSION['error']);
}
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
<?php include '../../includes/admin-topbar.php'; ?>

<div class="ml-64 p-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="index.php" class="text-blue-600 hover:text-blue-800 text-sm"><i class="fas fa-arrow-left mr-1"></i>Back to Bookings</a>
            <h1 class="text-2xl font-bold text-gray-800 mt-1">Booking #<?php echo $booking['reservation_id']; ?></h1>
        </div>
        <span class="badge-status badge-<?php echo badgeClass($booking['booking_status']); ?> text-sm"><?php echo $booking['booking_status']; ?></span>
    </div>

    <?php if (isset($messages['success'])): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-4 mb-6 rounded-lg"><?php echo $messages['success']; ?></div>
    <?php endif; ?>
    <?php if (isset($messages['error'])): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-4 mb-6 rounded-lg"><?php echo $messages['error']; ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-user mr-2 text-blue-600"></i>Guest Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider">Name</label>
                        <p class="text-gray-800 font-medium"><?php echo sanitize($booking['first_name'] . ' ' . $booking['last_name']); ?></p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider">Email</label>
                        <p class="text-gray-800"><?php echo sanitize($booking['email']); ?></p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider">Phone</label>
                        <p class="text-gray-800"><?php echo sanitize($booking['phone'] ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider">Gender</label>
                        <p class="text-gray-800"><?php echo sanitize($booking['gender'] ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider">Nationality</label>
                        <p class="text-gray-800"><?php echo sanitize($booking['nationality'] ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider">Address</label>
                        <p class="text-gray-800"><?php echo sanitize($booking['address'] ?? 'N/A'); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-bed mr-2 text-blue-600"></i>Room Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider">Room</label>
                        <p class="text-gray-800 font-medium"><?php echo sanitize($booking['room_name'] ?: $booking['room_number']); ?></p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider">Room Type</label>
                        <p class="text-gray-800"><?php echo sanitize($booking['type_name']); ?></p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider">Bed Type</label>
                        <p class="text-gray-800"><?php echo sanitize($booking['bed_type']); ?></p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider">Room Size</label>
                        <p class="text-gray-800"><?php echo sanitize($booking['room_size']); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-calendar mr-2 text-blue-600"></i>Booking Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider">Check In</label>
                        <p class="text-gray-800 font-medium"><?php echo formatDate($booking['check_in_date']); ?></p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider">Check Out</label>
                        <p class="text-gray-800 font-medium"><?php echo formatDate($booking['check_out_date']); ?></p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider">Total Nights</label>
                        <p class="text-gray-800 font-medium"><?php echo $booking['total_nights']; ?></p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider">Adults</label>
                        <p class="text-gray-800"><?php echo $booking['adults']; ?></p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider">Children</label>
                        <p class="text-gray-800"><?php echo $booking['children'] ?: 0; ?></p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider">Total Guests</label>
                        <p class="text-gray-800"><?php echo $booking['total_guests']; ?></p>
                    </div>
                </div>
                <?php if ($booking['special_notes']): ?>
                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <label class="text-xs text-gray-400 uppercase tracking-wider">Special Notes</label>
                    <p class="text-gray-700 mt-1"><?php echo nl2br(sanitize($booking['special_notes'])); ?></p>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($specialRequests)): ?>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-clipboard-list mr-2 text-blue-600"></i>Special Requests</h2>
                <ul class="space-y-3">
                    <?php foreach ($specialRequests as $sr): ?>
                    <li class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                        <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                        <div>
                            <p class="font-medium text-gray-800"><?php echo sanitize($sr['request_name']); ?></p>
                            <?php if ($sr['remarks']): ?>
                            <p class="text-sm text-gray-500"><?php echo sanitize($sr['remarks']); ?></p>
                            <?php endif; ?>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if ($booking['early_check_in_time']): ?>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-sun mr-2 text-yellow-500"></i>Early Check-In</h2>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-800">Requested Time: <strong><?php echo $booking['early_check_in_time'] ? date('h:i A', strtotime($booking['early_check_in_time'])) : 'N/A'; ?></strong></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($booking['late_check_out_time']): ?>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-moon mr-2 text-indigo-500"></i>Late Check-Out</h2>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-800">Requested Time: <strong><?php echo $booking['late_check_out_time'] ? date('h:i A', strtotime($booking['late_check_out_time'])) : 'N/A'; ?></strong></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-credit-card mr-2 text-blue-600"></i>Price Breakdown</h2>
                <div class="space-y-3">
                    <div class="flex justify-between text-gray-600">
                        <span>Room Price (<?php echo formatCurrency($booking['room_price']); ?> / night)</span>
                        <span><?php echo formatCurrency($booking['room_price'] * $booking['total_nights']); ?></span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Nights</span>
                        <span><?php echo $booking['total_nights']; ?> night(s)</span>
                    </div>
                    <hr class="border-gray-200">
                    <div class="flex justify-between text-lg font-bold text-gray-800">
                        <span>Total Price</span>
                        <span><?php echo formatCurrency($booking['total_price']); ?></span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Payment Status</span>
                        <span class="badge-status <?php
                            echo $booking['payment_status'] === 'Paid' ? 'badge-approved' : ($booking['payment_status'] === 'Refunded' ? 'badge-cancelled' : 'badge-pending');
                        ?>"><?php echo $booking['payment_status']; ?></span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-tasks mr-2 text-blue-600"></i>Actions</h2>
                <div class="space-y-3">
                    <?php if ($booking['booking_status'] === 'Pending'): ?>
                        <form method="POST" action="action.php" class="space-y-2">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="id" value="<?php echo $booking['reservation_id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <button type="submit" class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold" onclick="var _f=this.form;event.preventDefault();showSystemModal('Approve Booking','Approve this booking?','info',function(){_f.submit();})"><i class="fas fa-check mr-2"></i>Approve Booking</button>
                        </form>
                        <form method="POST" action="action.php" class="space-y-2">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="id" value="<?php echo $booking['reservation_id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <button type="submit" class="w-full px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold" onclick="var _f=this.form;event.preventDefault();showSystemModal('Reject Booking','Reject this booking?','error',function(){_f.submit();})"><i class="fas fa-times mr-2"></i>Reject Booking</button>
                        </form>
                    <?php elseif ($booking['booking_status'] === 'Approved'): ?>
                        <form method="POST" action="action.php" class="space-y-2">
                            <input type="hidden" name="action" value="checkin">
                            <input type="hidden" name="id" value="<?php echo $booking['reservation_id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <button type="submit" class="w-full px-4 py-3 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition font-semibold" onclick="var _f=this.form;event.preventDefault();showSystemModal('Check In','Mark as checked in?','info',function(){_f.submit();})"><i class="fas fa-sign-in-alt mr-2"></i>Check In</button>
                        </form>
                        <form method="POST" action="action.php" class="space-y-2">
                            <input type="hidden" name="action" value="cancel">
                            <input type="hidden" name="id" value="<?php echo $booking['reservation_id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <button type="submit" class="w-full px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold" onclick="var _f=this.form;event.preventDefault();showSystemModal('Cancel Booking','Cancel this booking?','error',function(){_f.submit();})"><i class="fas fa-ban mr-2"></i>Cancel Booking</button>
                        </form>
                    <?php elseif ($booking['booking_status'] === 'Checked In'): ?>
                        <form method="POST" action="action.php" class="space-y-2">
                            <input type="hidden" name="action" value="checkout">
                            <input type="hidden" name="id" value="<?php echo $booking['reservation_id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <button type="submit" class="w-full px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-semibold" onclick="var _f=this.form;event.preventDefault();showSystemModal('Check Out','Mark as checked out?','info',function(){_f.submit();})"><i class="fas fa-sign-out-alt mr-2"></i>Check Out</button>
                        </form>
                    <?php endif; ?>
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-400">Created: <?php echo formatDate($booking['created_at']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/admin-footer.php'; ?>
