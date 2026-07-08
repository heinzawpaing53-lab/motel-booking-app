<?php
define('PAGE_TITLE', 'My Bookings');
require_once 'config/db.php';

if (!isLoggedIn()) { redirect('login.php'); }

$userId = $_SESSION['user_id'];

// Cancel booking
if (isset($_GET['cancel'])) {
    $resId = (int)$_GET['cancel'];
    $stmt = $pdo->prepare("UPDATE reservations SET booking_status = 'Cancelled' WHERE reservation_id = ? AND user_id = ? AND booking_status = 'Pending'");
    $stmt->execute([$resId, $userId]);
    $_SESSION['success'] = 'Booking cancelled successfully.';
    redirect('booking-history.php');
}

$stmt = $pdo->prepare("SELECT r.*, rm.room_number, rm.room_name, rt.type_name, rt.price_per_night FROM reservations r JOIN rooms rm ON r.room_id = rm.room_id JOIN room_types rt ON rm.type_id = rt.type_id WHERE r.user_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();

include 'includes/header.php';
?>

<section class="py-16 bg-gray-50 min-h-screen">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="font-[Playfair_Display] text-4xl font-bold">My Bookings</h1>
                <p class="text-gray-500">Manage your reservations</p>
            </div>
            <a href="rooms.php" class="btn-primary"><i class="fas fa-plus mr-2"></i>Book New Room</a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <?php if (empty($bookings)): ?>
        <div class="text-center py-16 bg-white rounded-xl">
            <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-2xl font-bold text-gray-400 mb-2">No Bookings Yet</h3>
            <p class="text-gray-500 mb-6">Start by booking a room</p>
            <a href="rooms.php" class="btn-primary">Browse Rooms</a>
        </div>
        <?php else: ?>
        <div class="space-y-6">
            <?php foreach($bookings as $b): ?>
            <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <div>
                        <span class="badge-status badge-<?php echo badgeClass($b['booking_status']); ?>"><?php echo $b['booking_status']; ?></span>
                        <h3 class="font-semibold mt-2"><?php echo $b['room_name'] ?: 'Room '.$b['room_number']; ?></h3>
                        <p class="text-sm text-gray-500"><?php echo $b['type_name']; ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500"><i class="fas fa-calendar-check text-blue-600 mr-1"></i><?php echo formatDate($b['check_in_date']); ?></p>
                        <p class="text-sm text-gray-500"><i class="fas fa-calendar-times text-blue-600 mr-1"></i><?php echo formatDate($b['check_out_date']); ?></p>
                        <p class="text-sm text-gray-500"><i class="fas fa-moon text-blue-600 mr-1"></i><?php echo $b['total_nights']; ?> Night<?php echo $b['total_nights'] > 1 ? 's' : ''; ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500"><i class="fas fa-user text-blue-600 mr-1"></i><?php echo $b['total_guests']; ?> Guest<?php echo $b['total_guests']>1?'s':''; ?></p>
                        <p class="text-sm text-gray-500"><i class="fas fa-credit-card text-blue-600 mr-1"></i><?php echo $b['payment_status']; ?></p>
                        <p class="text-lg font-bold text-blue-600"><?php echo formatCurrency($b['total_price']); ?></p>
                    </div>
                    <div class="text-right">
                        <a href="booking-details.php?id=<?php echo $b['reservation_id']; ?>" class="text-blue-600 hover:underline text-sm block mb-1"><i class="fas fa-eye"></i> View</a>
                        <?php if ($b['booking_status'] == 'Pending'): ?>
                        <a href="?cancel=<?php echo $b['reservation_id']; ?>" class="text-red-600 hover:underline text-sm" onclick="return confirm('Cancel this booking?')"><i class="fas fa-times"></i> Cancel</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
