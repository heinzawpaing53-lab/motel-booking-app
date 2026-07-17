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
<style>
    .btn-primary, a.btn-primary, button.btn-primary {
        background: linear-gradient(135deg, #C8A96A, #A68B5B) !important;
        color: #2C1810 !important;
        border: none !important;
        font-weight: 600;
    }
    .btn-primary:hover, a.btn-primary:hover, button.btn-primary:hover {
        background: linear-gradient(135deg, #A68B5B, #8B7548) !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(200,169,106,0.3) !important;
    }

    /* --- Premium Modal Overlay Styles --- */
    .luxury-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(6px);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .luxury-modal {
        background-color: #2b1f1d;
        border: 1px solid #d4af37;
        border-radius: 12px;
        padding: 30px;
        max-width: 500px;
        width: 90%;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        transform: scale(0.9);
        transition: transform 0.3s ease;
    }

    .luxury-modal.show {
        opacity: 1;
        transform: scale(1);
    }

    .modal-title {
        font-size: 1.5rem;
        font-family: serif;
        color: #d4af37;
        margin-bottom: 15px;
    }

    .modal-message {
        color: #f5ece1;
        font-size: 1.05rem;
        line-height: 1.6;
        margin-bottom: 25px;
    }

    .btn-modal-close {
        background-color: #d4af37;
        color: #1a110f;
        border: none;
        padding: 10px 30px;
        font-weight: bold;
        border-radius: 6px;
        cursor: pointer;
        text-transform: uppercase;
        transition: background-color 0.2s ease;
    }

    .btn-modal-close:hover {
        background-color: #bfa032;
    }

    /* --- Compact Form Layout --- */
    .booking-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    @media (max-width: 768px) {
        .booking-form-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Make checkboxes inline and neat */
    .special-requests-group {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-top: 10px;
    }

    .special-requests-group label {
        display: flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.05);
        padding: 8px 16px;
        border-radius: 6px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        cursor: pointer;
    }

    /* --- Room Summary Horizontal Row --- */
    .room-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 25px;
        background-color: #311d18;
        border-radius: 8px;
        margin-bottom: 25px;
    }

    .room-meta-left {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .room-meta-left h3 {
        margin: 0;
        font-size: 1.3rem;
        color: #f5ece1;
    }

    .room-meta-left p {
        margin: 0;
        font-size: 0.95rem;
        color: #bfa594;
    }

    .room-price-right {
        font-size: 1.4rem;
        font-weight: bold;
        color: #d4af37;
        white-space: nowrap;
    }

    .room-price-right span {
        font-size: 0.9rem;
        font-weight: normal;
        color: #bfa594;
    }

    @media (max-width: 576px) {
        .room-summary-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
            padding: 15px;
        }
    }
</style>

<section class="py-10 bg-luxury-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <span class="text-luxury-400 font-semibold tracking-wider uppercase text-sm">Booking</span>
            <h1 class="font-[Playfair_Display] text-4xl mt-2 text-luxury-900">Complete Your Reservation</h1>
        </div>

        <?php
        // Build a single consolidated alert message for the modal
        $modalAlertMsg = '';
        $modalAlertTitle = '';

        if ($error) {
            $modalAlertMsg = $error;
            $modalAlertTitle = 'System Notice';
        } elseif (isset($_SESSION['booking_error'])) {
            $modalAlertMsg = $_SESSION['booking_error'];
            $modalAlertTitle = 'System Notice';
            unset($_SESSION['booking_error']);
        } elseif (isset($_SESSION['pending_reservation']) && !$_POST && isset($_SESSION['user_id'])) {
            $modalAlertMsg = 'Your booking details have been restored. Please review and confirm your reservation.';
            $modalAlertTitle = 'Welcome Back';
        } elseif (isset($_SESSION['pending_reservation']) && !isset($_SESSION['user_id'])) {
            unset($_SESSION['pending_reservation']);
        }
        ?>

        <?php if ($modalAlertMsg): ?>
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            var modalHtml = '<div class="luxury-modal-overlay" id="luxuryModal">'
                + '<div class="luxury-modal">'
                + '<h3 class="modal-title"><?php echo addslashes($modalAlertTitle); ?></h3>'
                + '<p class="modal-message"><?php echo addslashes($modalAlertMsg); ?></p>'
                + '<button class="btn-modal-close" onclick="closeLuxuryModal()">Got It</button>'
                + '</div></div>';
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            setTimeout(function() {
                var overlay = document.getElementById('luxuryModal');
                if (overlay) {
                    overlay.style.opacity = '1';
                    overlay.querySelector('.luxury-modal').classList.add('show');
                }
            }, 100);
        });

        function closeLuxuryModal() {
            var overlay = document.getElementById('luxuryModal');
            if (overlay) {
                overlay.style.opacity = '0';
                overlay.querySelector('.luxury-modal').classList.remove('show');
                setTimeout(function() { overlay.remove(); }, 300);
            }
        }
        </script>
        <?php endif; ?>

        <?php if ($room): ?>
        <div class="room-summary-row">
            <div class="room-meta-left">
                <h3><?php echo $room['room_name'] ?: 'Room '.$room['room_number']; ?></h3>
                <p><?php echo $room['type_name']; ?> - <?php echo $room['floor_name']; ?></p>
            </div>
            <div class="room-price-right">
                <?php echo formatCurrency($room['price_per_night']); ?> <span>/night</span>
            </div>
        </div>
        <?php endif; ?>

        <form method="POST" class="bg-luxury-800 rounded-xl shadow-sm p-8" id="reservationForm">
            <input type="hidden" name="room_id" value="<?php echo $roomId; ?>">

            <h3 class="font-semibold text-lg mb-4 text-luxury-100"><i class="fas fa-calendar text-luxury-400 mr-2"></i>Booking Details</h3>
            <div class="booking-form-grid mb-6">
                <div>
                    <label class="block text-sm font-semibold text-luxury-300 mb-1">Check-in Date</label>
                    <input type="date" name="check_in" id="check_in" value="<?php echo sanitize($pendingData['check_in'] ?? $_POST['check_in'] ?? date('Y-m-d', strtotime('+1 day'))); ?>" min="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-3 border border-luxury-600 rounded-lg focus:ring-2 focus:ring-luxury-400 outline-none bg-luxury-700 text-luxury-100" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-luxury-300 mb-1">Check-out Date</label>
                    <input type="date" name="check_out" id="check_out" value="<?php echo sanitize($pendingData['check_out'] ?? $_POST['check_out'] ?? date('Y-m-d', strtotime('+2 days'))); ?>" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" class="w-full px-4 py-3 border border-luxury-600 rounded-lg focus:ring-2 focus:ring-luxury-400 outline-none bg-luxury-700 text-luxury-100" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-luxury-300 mb-1"><i class="fas fa-users text-luxury-400 mr-1"></i>Adults</label>
                    <select name="adults" class="w-full px-4 py-3 border border-luxury-600 rounded-lg focus:ring-2 focus:ring-luxury-400 outline-none bg-luxury-700 text-luxury-100">
                        <?php for($i=1;$i<=5;$i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo (int)($pendingData['adults'] ?? $_POST['adults'] ?? 1)==$i?'selected':''; ?>><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-luxury-300 mb-1"><i class="fas fa-child text-luxury-400 mr-1"></i>Children</label>
                    <select name="children" class="w-full px-4 py-3 border border-luxury-600 rounded-lg focus:ring-2 focus:ring-luxury-400 outline-none bg-luxury-700 text-luxury-100">
                        <?php for($i=0;$i<=3;$i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo (int)($pendingData['children'] ?? $_POST['children'] ?? 0)==$i?'selected':''; ?>><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <input type="hidden" name="room_price" id="room_price" value="<?php echo $room['price_per_night'] ?? 0; ?>">

            <h3 class="font-semibold text-lg mb-2 text-luxury-100"><i class="fas fa-clipboard-list text-luxury-400 mr-2"></i>Special Requests</h3>
            <div class="special-requests-group mb-6">
                <?php foreach($specialRequests as $req): ?>
                <label class="flex items-center space-x-2 p-2 border border-luxury-600 rounded-lg hover:bg-luxury-700 cursor-pointer">
                    <input type="checkbox" name="requests[]" value="<?php echo $req['request_id']; ?>" class="rounded text-luxury-400" <?php echo in_array($req['request_id'], $pendingRequests) ? 'checked' : ''; ?>>
                    <span class="text-sm text-luxury-200"><?php echo $req['request_name']; ?></span>
                </label>
                <?php endforeach; ?>
            </div>

            <h3 class="font-semibold text-lg mb-3 text-luxury-100"><i class="fas fa-clock text-luxury-400 mr-2"></i>Check-in / Check-out Options</h3>
            <div class="booking-form-grid mb-6">
                <div class="p-4 border border-luxury-600 rounded-lg">
                    <label class="block font-medium mb-2 text-luxury-200">Early Check-in</label>
                    <select name="early_check_in_time" class="w-full px-3 py-2 border border-luxury-600 rounded-lg text-sm bg-luxury-700 text-luxury-100">
                        <option value="">No Early Check-in</option>
                        <?php for($h=6;$h<=13;$h++): $val = sprintf('%02d:00', $h); ?>
                        <option value="<?php echo $val; ?>" <?php echo ($pendingData['early_check_in_time'] ?? $_POST['early_check_in_time'] ?? '')==$val?'selected':''; ?>><?php echo $val; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="p-4 border border-luxury-600 rounded-lg">
                    <label class="block font-medium mb-2 text-luxury-200">Late Check-out</label>
                    <select name="late_check_out_time" class="w-full px-3 py-2 border border-luxury-600 rounded-lg text-sm bg-luxury-700 text-luxury-100">
                        <option value="">No Late Check-out</option>
                        <?php for($h=12;$h<=18;$h++): $val = sprintf('%02d:00', $h); ?>
                        <option value="<?php echo $val; ?>" <?php echo ($pendingData['late_check_out_time'] ?? $_POST['late_check_out_time'] ?? '')==$val?'selected':''; ?>><?php echo $val; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-luxury-300 mb-1">Special Notes</label>
                <textarea name="special_notes" rows="2" class="w-full px-4 py-3 border border-luxury-600 rounded-lg focus:ring-2 focus:ring-luxury-400 outline-none bg-luxury-700 text-luxury-100 placeholder-luxury-300" placeholder="Any additional requests or notes..."><?php echo sanitize($pendingData['special_notes'] ?? $_POST['special_notes'] ?? ''); ?></textarea>
            </div>

            <div class="bg-luxury-700 rounded-lg p-5 mb-6">
                <h3 class="font-semibold text-lg mb-3 text-luxury-100">Price Summary</h3>
                <div class="space-y-2">
                    <div class="flex justify-between text-luxury-200"><span>Price per Night</span><span class="font-semibold" id="display_price"><?php echo formatCurrency($room['price_per_night'] ?? 0); ?></span></div>
                    <div class="flex justify-between text-luxury-200"><span>Total Nights</span><span class="font-semibold" id="display_nights">-</span></div>
                    <hr class="border-luxury-600">
                    <div class="flex justify-between text-lg"><span class="font-bold text-luxury-100">Total Price</span><span class="font-bold text-luxury-400 text-xl" id="estimated_total">-</span></div>
                </div>
            </div>

            <button type="submit" class="btn-primary w-full text-lg py-4" id="confirmBtn"><i class="fas fa-check-circle mr-2"></i>Confirm Reservation</button>
        </form>
    </div>
</section>

<?php if (isset($_SESSION['pending_reservation'])): unset($_SESSION['pending_reservation']); endif; ?>

<?php if (!isset($_SESSION['user_id'])): ?>
<div id="authModal" class="auth-modal-overlay" style="display:none;">
    <div class="auth-modal-card animate-modal">
        <div class="w-20 h-20 bg-luxury-700 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-shield-halved text-luxury-400 text-3xl"></i>
        </div>
        <h3 class="text-2xl font-bold text-luxury-100 mb-3">Authentication Required</h3>
        <p class="text-luxury-300 mb-2">Please sign in or register an account to secure your booking.</p>
        <p class="text-luxury-300 text-sm mb-8">Your reservation details have been saved.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="login.php" class="px-5 py-2.5 bg-luxury-400 hover:bg-luxury-500 text-luxury-900 font-medium rounded-lg transition"><i class="fas fa-sign-in-alt mr-2"></i>Log In</a>
            <a href="register.php" class="px-5 py-2.5 bg-luxury-700 hover:bg-luxury-600 text-luxury-200 font-medium rounded-lg transition border border-luxury-600"><i class="fas fa-user-plus mr-2"></i>Register</a>
        </div>
        <a href="rooms.php" class="mt-6 inline-block text-sm text-luxury-300 hover:text-luxury-100 transition px-4 py-2 rounded cursor-pointer">Continue Browsing</a>
    </div>
</div>
<?php endif; ?>

<style>
    /* --- Auth Modal Full Centering --- */
    .auth-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(26, 17, 15, 0.75);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        z-index: 999999;
    }

    .auth-modal-card {
        margin: 0 auto;
        max-width: 480px;
        width: 90%;
        background-color: #2b1f1d;
        border: 1px solid #d4af37;
        border-radius: 16px;
        padding: 32px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        transform: scale(1);
        transition: transform 0.2s ease-out;
    }

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

if (modal) {
    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });
}

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
        if (data.success && modal) {
            modal.style.display = 'flex';
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
