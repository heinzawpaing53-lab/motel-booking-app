<?php
define('PAGE_TITLE', 'Booking Details');
require_once 'config/db.php';

if (!isLoggedIn()) { redirect('login.php'); }

$resId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT r.*, rm.room_number, rm.room_name, rt.type_name, rt.bed_type, rt.room_size, f.floor_name,
    u.first_name, u.last_name, u.email, u.phone
    FROM reservations r
    JOIN rooms rm ON r.room_id = rm.room_id
    JOIN room_types rt ON rm.type_id = rt.type_id
    JOIN floors f ON rm.floor_id = f.floor_id
    JOIN users u ON r.user_id = u.user_id
    WHERE r.reservation_id = ? AND r.user_id = ?");
$stmt->execute([$resId, $_SESSION['user_id']]);
$booking = $stmt->fetch();
if (!$booking) { redirect('booking-history.php'); }

$requests = $pdo->prepare("SELECT sr.request_name, rr.remarks FROM reservation_requests rr JOIN special_requests sr ON rr.request_id = sr.request_id WHERE rr.reservation_id = ?");
$requests->execute([$resId]);
$bookingRequests = $requests->fetchAll();

include 'includes/header.php';
?>

<section class="py-16 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="font-[Playfair_Display] text-3xl font-bold">Booking #<?php echo $booking['reservation_id']; ?></h1>
            <span class="badge-status badge-<?php echo badgeClass($booking['booking_status']); ?> text-sm px-4 py-2"><?php echo $booking['booking_status']; ?></span>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-8 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h3 class="font-semibold text-lg mb-4"><i class="fas fa-hotel text-amber-500 mr-2"></i>Room Information</h3>
                    <table class="w-full text-sm">
                        <tr class="border-b"><td class="py-2 text-gray-500">Room</td><td class="py-2 font-semibold"><?php echo $booking['room_name'] ?: 'Room '.$booking['room_number']; ?></td></tr>
                        <tr class="border-b"><td class="py-2 text-gray-500">Type</td><td class="py-2 font-semibold"><?php echo $booking['type_name']; ?></td></tr>
                        <tr class="border-b"><td class="py-2 text-gray-500">Floor</td><td class="py-2 font-semibold"><?php echo $booking['floor_name']; ?></td></tr>
                        <tr class="border-b"><td class="py-2 text-gray-500">Bed</td><td class="py-2 font-semibold"><?php echo $booking['bed_type']; ?></td></tr>
                        <tr><td class="py-2 text-gray-500">Room Size</td><td class="py-2 font-semibold"><?php echo $booking['room_size']; ?></td></tr>
                    </table>
                </div>
                <div>
                    <h3 class="font-semibold text-lg mb-4"><i class="fas fa-calendar text-amber-500 mr-2"></i>Booking Details</h3>
                    <table class="w-full text-sm">
                        <tr class="border-b"><td class="py-2 text-gray-500">Check In</td><td class="py-2 font-semibold"><?php echo formatDate($booking['check_in_date']); ?></td></tr>
                        <tr class="border-b"><td class="py-2 text-gray-500">Check Out</td><td class="py-2 font-semibold"><?php echo formatDate($booking['check_out_date']); ?></td></tr>
                        <tr class="border-b"><td class="py-2 text-gray-500">Nights</td><td class="py-2 font-semibold"><?php echo $booking['total_nights']; ?></td></tr>
                        <tr class="border-b"><td class="py-2 text-gray-500">Guests</td><td class="py-2 font-semibold"><?php echo $booking['total_guests']; ?> (<?php echo $booking['adults']; ?> Adults, <?php echo $booking['children']; ?> Children)</td></tr>
                        <tr><td class="py-2 text-gray-500">Payment</td><td class="py-2 font-semibold"><?php echo $booking['payment_status']; ?></td></tr>
                    </table>
                </div>
            </div>
        </div>

        <?php if ($bookingRequests): ?>
        <div class="bg-white rounded-xl shadow-sm p-8 mb-6">
            <h3 class="font-semibold text-lg mb-4"><i class="fas fa-clipboard-list text-amber-500 mr-2"></i>Special Requests</h3>
            <div class="flex flex-wrap gap-2">
                <?php foreach($bookingRequests as $req): ?>
                <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-sm"><?php echo $req['request_name']; ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($booking['special_notes']): ?>
        <div class="bg-white rounded-xl shadow-sm p-8 mb-6">
            <h3 class="font-semibold text-lg mb-4"><i class="fas fa-sticky-note text-amber-500 mr-2"></i>Special Notes</h3>
            <p class="text-gray-600"><?php echo $booking['special_notes']; ?></p>
        </div>
        <?php endif; ?>

        <?php if ($booking['early_check_in_time'] || $booking['late_check_out_time']): ?>
        <div class="bg-white rounded-xl shadow-sm p-8 mb-6">
            <h3 class="font-semibold text-lg mb-4"><i class="fas fa-clock text-amber-500 mr-2"></i>Check-in/out Requests</h3>
            <?php if ($booking['early_check_in_time']): ?>
            <p>Early Check-in: Requested at <?php echo $booking['early_check_in_time']; ?></p>
            <?php endif; ?>
            <?php if ($booking['late_check_out_time']): ?>
            <p>Late Check-out: Requested at <?php echo $booking['late_check_out_time']; ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-sm p-8 mb-6">
            <h3 class="font-semibold text-lg mb-4"><i class="fas fa-receipt text-amber-500 mr-2"></i>Price Summary</h3>
            <table class="w-full text-sm">
                <tr class="border-b"><td class="py-2 text-gray-500">Room Price per Night</td><td class="py-2 font-semibold text-right"><?php echo formatCurrency($booking['room_price']); ?></td></tr>
                <tr class="border-b"><td class="py-2 text-gray-500">Total Nights</td><td class="py-2 font-semibold text-right"><?php echo $booking['total_nights']; ?></td></tr>
                <tr><td class="py-3 text-lg font-bold">Total Price</td><td class="py-3 text-lg font-bold text-amber-500 text-right"><?php echo formatCurrency($booking['total_price']); ?></td></tr>
            </table>
        </div>

        <div class="flex space-x-4">
            <button onclick="window.print()" class="btn-primary"><i class="fas fa-print mr-2"></i>Print</button>
            <a href="booking-history.php" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition"><i class="fas fa-arrow-left mr-2"></i>Back to Bookings</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
