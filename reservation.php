<?php
define('PAGE_TITLE', 'Reservation');
require_once 'config/db.php';

if (isset($_GET['ajax']) && $_GET['ajax'] === 'save_pending') {
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $_SESSION['pending_reservation'] = $_POST;
        $_SESSION['redirect_after_login'] = 'reservation.php';
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

$pendingData = isset($_SESSION['pending_reservation']) ? $_SESSION['pending_reservation'] : [];

$roomId = isset($_GET['room_id']) ? (int)$_GET['room_id'] : (isset($_POST['room_id']) ? (int)$_POST['room_id'] : (isset($pendingData['room_id']) ? (int)$pendingData['room_id'] : 0));
$room = null;
if ($roomId) {
    $stmt = $pdo->prepare("SELECT r.*, rt.type_name, rt.price_per_night, rt.bed_type, rt.room_size, rt.max_capacity, f.floor_name
        FROM rooms r JOIN room_types rt ON r.type_id = rt.type_id JOIN floors f ON r.floor_id = f.floor_id WHERE r.room_id = ?");
    $stmt->execute([$roomId]);
    $room = $stmt->fetch();
}

$specialRequests = $pdo->query("SELECT * FROM special_requests WHERE active = 1 ORDER BY request_name")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['ajax'])) {
    $checkIn = sanitize($_POST['check_in'] ?? '');
    $checkOut = sanitize($_POST['check_out'] ?? '');
    $adults = (int)($_POST['adults'] ?? 1);
    $children = (int)($_POST['children'] ?? 0);
    $totalGuests = $adults + $children;
    $specialNotes = sanitize($_POST['special_notes'] ?? '');
    $earlyCheckInTime = !empty($_POST['early_check_in_time']) ? sanitize($_POST['early_check_in_time']) : null;
    $lateCheckOutTime = !empty($_POST['late_check_out_time']) ? sanitize($_POST['late_check_out_time']) : null;
    $selectedRequests = $_POST['requests'] ?? [];

    if (empty($checkIn) || empty($checkOut)) { $error = 'Please select check-in and check-out dates.'; }
    elseif (strtotime($checkIn) < strtotime(date('Y-m-d'))) { $error = 'Check-in date cannot be in the past.'; }
    elseif (strtotime($checkOut) <= strtotime($checkIn)) { $error = 'Check-out must be after check-in.'; }
    elseif (!$room) { $error = 'Invalid room selected.'; }
    elseif ($room && $totalGuests > $room['max_capacity']) { $error = 'Total guests exceed room capacity of '.$room['max_capacity'].'.'; }
    else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE room_id = ? AND booking_status IN ('Pending','Approved','Checked In') AND ? < check_out_date AND ? > check_in_date");
        $stmt->execute([$roomId, $checkIn, $checkOut]);
        if ($stmt->fetchColumn() > 0) { $error = 'This room is already reserved or awaiting approval for the selected dates. Please choose a different date range or room.'; }
        else {
            $totalNights = ceil((strtotime($checkOut) - strtotime($checkIn)) / (60 * 60 * 24));
            $totalPrice = $room['price_per_night'] * $totalNights;

            $stmt = $pdo->prepare("INSERT INTO reservations (user_id, room_id, check_in_date, check_out_date, adults, children, total_guests, total_nights, room_price, total_price, booking_status, payment_status, early_check_in_time, late_check_out_time, special_notes) VALUES (?,?,?,?,?,?,?,?,?,?,'Pending','Pending',?,?,?)");
            $stmt->execute([$_SESSION['user_id'], $roomId, $checkIn, $checkOut, $adults, $children, $totalGuests, $totalNights, $room['price_per_night'], $totalPrice, $earlyCheckInTime, $lateCheckOutTime, $specialNotes]);
            $reservationId = $pdo->lastInsertId();

            foreach ($selectedRequests as $reqId) {
                $stmt = $pdo->prepare("INSERT INTO reservation_requests (reservation_id, request_id) VALUES (?,?)");
                $stmt->execute([$reservationId, $reqId]);
            }

            logActivity($pdo, $_SESSION['user_id'], 'New Reservation', "Reservation #$reservationId created");
            unset($_SESSION['pending_reservation']);
            $_SESSION['success'] = 'Your reservation has been submitted successfully! We will notify you once it is confirmed.';
            redirect('booking-history.php');
        }
    }
}

$pendingRequests = isset($pendingData['requests']) && is_array($pendingData['requests']) ? $pendingData['requests'] : [];

include 'includes/header.php';
?>

<section class="py-16 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <span class="text-blue-600 font-semibold tracking-wider uppercase text-sm">Booking</span>
            <h1 class="font-[Playfair_Display] text-4xl font-bold mt-2">Complete Your Reservation</h1>
        </div>

        <?php if ($error): ?>
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['booking_error'])): ?>
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $_SESSION['booking_error']; unset($_SESSION['booking_error']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['pending_reservation']) && !$_POST): ?>
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-r shadow-sm font-medium mb-6">
            You are now signed in! Your booking details have been restored. Please review and confirm your reservation.
        </div>
        <?php endif; ?>

        <?php if ($room): ?>
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <div class="flex items-center space-x-4">
                <div class="flex-1">
                    <h3 class="font-semibold text-lg"><?php echo $room['room_name'] ?: 'Room '.$room['room_number']; ?></h3>
                    <p class="text-gray-500 text-sm"><?php echo $room['type_name']; ?> - <?php echo $room['floor_name']; ?></p>
                    <p class="text-blue-600 font-bold text-2xl mt-2"><?php echo formatCurrency($room['price_per_night']); ?> <span class="text-sm text-gray-500 font-normal">/night</span></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <form method="POST" class="bg-white rounded-xl shadow-sm p-8" id="reservationForm">
            <input type="hidden" name="room_id" value="<?php echo $roomId; ?>">

            <h3 class="font-semibold text-lg mb-4"><i class="fas fa-calendar text-blue-600 mr-2"></i>Booking Dates</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Check-in Date</label>
                    <input type="date" name="check_in" id="check_in" value="<?php echo sanitize($pendingData['check_in'] ?? $_POST['check_in'] ?? date('Y-m-d', strtotime('+1 day'))); ?>" min="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Check-out Date</label>
                    <input type="date" name="check_out" id="check_out" value="<?php echo sanitize($pendingData['check_out'] ?? $_POST['check_out'] ?? date('Y-m-d', strtotime('+2 days'))); ?>" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>
            </div>

            <h3 class="font-semibold text-lg mb-4"><i class="fas fa-users text-blue-600 mr-2"></i>Guests</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Adults</label>
                    <select name="adults" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        <?php for($i=1;$i<=5;$i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo (int)($pendingData['adults'] ?? $_POST['adults'] ?? 1)==$i?'selected':''; ?>><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Children</label>
                    <select name="children" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        <?php for($i=0;$i<=3;$i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo (int)($pendingData['children'] ?? $_POST['children'] ?? 0)==$i?'selected':''; ?>><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <input type="hidden" name="room_price" id="room_price" value="<?php echo $room['price_per_night'] ?? 0; ?>">

            <h3 class="font-semibold text-lg mb-4"><i class="fas fa-clipboard-list text-blue-600 mr-2"></i>Special Requests</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 mb-6">
                <?php foreach($specialRequests as $req): ?>
                <label class="flex items-center space-x-2 p-3 border border-gray-200 rounded-lg hover:bg-blue-50 cursor-pointer">
                    <input type="checkbox" name="requests[]" value="<?php echo $req['request_id']; ?>" class="rounded text-blue-600" <?php echo in_array($req['request_id'], $pendingRequests) ? 'checked' : ''; ?>>
                    <span class="text-sm"><?php echo $req['request_name']; ?></span>
                </label>
                <?php endforeach; ?>
            </div>

            <h3 class="font-semibold text-lg mb-4"><i class="fas fa-clock text-blue-600 mr-2"></i>Check-in / Check-out Options</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="p-4 border border-gray-200 rounded-lg">
                    <label class="block font-medium mb-2">Request Early Check-in Time</label>
                    <select name="early_check_in_time" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                        <option value="">No Early Check-in</option>
                        <?php for($h=6;$h<=13;$h++): $val = sprintf('%02d:00', $h); ?>
                        <option value="<?php echo $val; ?>" <?php echo ($pendingData['early_check_in_time'] ?? $_POST['early_check_in_time'] ?? '')==$val?'selected':''; ?>><?php echo $val; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="p-4 border border-gray-200 rounded-lg">
                    <label class="block font-medium mb-2">Request Late Check-out Time</label>
                    <select name="late_check_out_time" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                        <option value="">No Late Check-out</option>
                        <?php for($h=12;$h<=18;$h++): $val = sprintf('%02d:00', $h); ?>
                        <option value="<?php echo $val; ?>" <?php echo ($pendingData['late_check_out_time'] ?? $_POST['late_check_out_time'] ?? '')==$val?'selected':''; ?>><?php echo $val; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="mb-8">
                <label class="block text-sm font-semibold text-gray-600 mb-1">Special Notes</label>
                <textarea name="special_notes" rows="3" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Any additional requests or notes..."><?php echo sanitize($pendingData['special_notes'] ?? $_POST['special_notes'] ?? ''); ?></textarea>
            </div>

            <div class="bg-gray-50 rounded-lg p-6 mb-8">
                <h3 class="font-semibold text-lg mb-4">Price Summary</h3>
                <div class="space-y-2">
                    <div class="flex justify-between"><span>Price per Night</span><span class="font-semibold" id="display_price"><?php echo formatCurrency($room['price_per_night'] ?? 0); ?></span></div>
                    <div class="flex justify-between"><span>Total Nights</span><span class="font-semibold" id="display_nights">-</span></div>
                    <hr>
                    <div class="flex justify-between text-lg"><span class="font-bold">Total Price</span><span class="font-bold text-blue-600 text-xl" id="estimated_total">-</span></div>
                </div>
            </div>

            <button type="submit" class="btn-primary w-full text-lg py-4" id="confirmBtn"><i class="fas fa-check-circle mr-2"></i>Confirm Reservation</button>
        </form>
    </div>
</section>

<?php if (isset($_SESSION['pending_reservation'])): unset($_SESSION['pending_reservation']); endif; ?>

<div id="authModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 text-center relative animate-modal">
        <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-shield-halved text-blue-600 text-3xl"></i>
        </div>
        <h3 class="text-2xl font-bold text-slate-900 mb-3">Authentication Required</h3>
        <p class="text-slate-600 mb-2">Please sign in or register an account to secure your booking.</p>
        <p class="text-slate-400 text-sm mb-8">Your reservation details have been saved.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="login.php" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition"><i class="fas fa-sign-in-alt mr-2"></i>Log In</a>
            <a href="register.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg transition"><i class="fas fa-user-plus mr-2"></i>Register</a>
        </div>
        <button onclick="closeModal()" class="mt-6 text-sm text-slate-400 hover:text-slate-600 transition">Continue Browsing</button>
    </div>
</div>

<style>
.animate-modal {
    animation: modalIn 0.3s ease-out;
}
@keyframes modalIn {
    from { opacity: 0; transform: scale(0.9) translateY(20px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
</style>

<script>
const loggedIn = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
const form = document.getElementById('reservationForm');
const modal = document.getElementById('authModal');

function closeModal() {
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

modal.addEventListener('click', function(e) {
    if (e.target === modal) closeModal();
});

form.addEventListener('submit', function(e) {
    if (loggedIn) return;

    e.preventDefault();

    const formData = new FormData(form);

    fetch('reservation.php?ajax=save_pending', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    })
    .catch(function() {
        window.location.href = 'login.php';
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const checkIn = document.getElementById('check_in');
    const checkOut = document.getElementById('check_out');
    const price = <?php echo $room['price_per_night'] ?? 0; ?>;
    const displayPrice = document.getElementById('display_price');
    const displayNights = document.getElementById('display_nights');
    const estimatedTotal = document.getElementById('estimated_total');
    function calc() {
        if (checkIn.value && checkOut.value) {
            const d1 = new Date(checkIn.value);
            const d2 = new Date(checkOut.value);
            if (d2 > d1) {
                const nights = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24));
                displayNights.textContent = nights;
                estimatedTotal.textContent = '$' + (price * nights).toFixed(2);
                return;
            }
        }
        displayNights.textContent = '-';
        estimatedTotal.textContent = '-';
    }
    checkIn.addEventListener('change', calc);
    checkOut.addEventListener('change', calc);
    calc();
});
</script>

<?php include 'includes/footer.php'; ?>
