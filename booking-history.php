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

$stmt = $pdo->prepare("SELECT r.*, rm.room_number, rm.room_name, rt.type_name, rt.price_per_night, (SELECT COUNT(*) FROM payments p WHERE p.reservation_id = r.reservation_id AND p.payment_status = 'Pending') as pending_payment_count FROM reservations r JOIN rooms rm ON r.room_id = rm.room_id JOIN room_types rt ON rm.type_id = rt.type_id WHERE r.user_id = ? AND r.customer_hidden = 0 ORDER BY r.created_at DESC");
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();

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
</style>

<!-- Custom Confirm Modal (reusable) -->
<div id="confirmModal" class="fixed inset-0 bg-black/70 backdrop-blur-md z-50 hidden items-center justify-center p-4">
    <div class="bg-[#1C120C] border border-amber-500/20 shadow-2xl rounded-3xl p-8 max-w-md w-full text-center relative overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="confirmModalCard">
        <div id="confirmModalIcon" class="w-14 h-14 rounded-full bg-amber-500/10 border border-amber-500/20 flex items-center justify-center mx-auto mb-5 shadow-inner">
            <i class="fas fa-exclamation-triangle text-amber-400 text-xl"></i>
        </div>
        <h3 id="confirmModalTitle" class="text-2xl font-serif text-amber-50 tracking-wide mb-2">Are you sure?</h3>
        <p id="confirmModalMessage" class="text-sm text-stone-300 leading-relaxed max-w-xs mx-auto mb-8">This action cannot be undone.</p>
        <div class="grid grid-cols-2 gap-3 w-full">
            <button id="confirmModalCancel" onclick="closeConfirmModal()" class="w-full py-3 px-4 rounded-xl text-sm font-medium text-stone-300 bg-stone-800/80 hover:bg-stone-700/80 border border-stone-700/50 transition-all duration-200">Cancel</button>
            <button id="confirmModalAction" class="w-full py-3 px-4 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-red-800 to-rose-700 hover:from-red-700 hover:to-rose-600 shadow-lg shadow-red-950/50 border border-red-500/30 transition-all duration-200">Confirm</button>
        </div>
    </div>
</div>

<!-- Success / Alert Modal (reusable) -->
<div id="alertModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-[#2A1810] border border-amber-500/20 rounded-2xl p-6 max-w-sm w-full shadow-2xl text-center transform transition-all duration-300 scale-95 opacity-0" id="alertModalCard">
        <div id="alertModalIcon" class="inline-flex items-center justify-center p-3 rounded-full mb-4 bg-emerald-500/20">
            <i id="alertModalIconEl" class="fas fa-check text-emerald-400 text-xl"></i>
        </div>
        <h3 id="alertModalTitle" class="text-lg font-bold text-white mb-2">Success</h3>
        <p id="alertModalMessage" class="text-stone-300 text-sm mb-6">Action completed.</p>
        <button onclick="closeAlertModal()" class="px-6 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-medium transition-colors text-sm">Got It</button>
    </div>
</div>

<section class="bg-stone-50 min-h-screen pb-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h3 class="text-2xl font-bold text-stone-900">My Bookings</h3>
                <p class="text-gray-500 text-sm">Manage your reservations</p>
            </div>
            <a href="rooms.php" class="btn-primary"><i class="fas fa-plus mr-2"></i>Book New Room</a>
        </div>

        <?php
        $modalAlertMsg = '';
        $modalAlertTitle = '';

        if (isset($_SESSION['success'])) {
            $modalAlertMsg = $_SESSION['success'];
            $modalAlertTitle = 'Success';
            unset($_SESSION['success']);
        } elseif (isset($_SESSION['booking_success'])) {
            $modalAlertMsg = $_SESSION['booking_success'];
            $modalAlertTitle = 'Success';
            unset($_SESSION['booking_success']);
        }
        ?>

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
                        <p class="text-sm text-gray-500"><i class="fas fa-calendar-check text-amber-500 mr-1"></i><?php echo formatDate($b['check_in_date']); ?></p>
                        <p class="text-sm text-gray-500"><i class="fas fa-calendar-times text-amber-500 mr-1"></i><?php echo formatDate($b['check_out_date']); ?></p>
                        <p class="text-sm text-gray-500"><i class="fas fa-moon text-amber-500 mr-1"></i><?php echo $b['total_nights']; ?> Night<?php echo $b['total_nights'] > 1 ? 's' : ''; ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500"><i class="fas fa-user text-amber-500 mr-1"></i><?php echo $b['total_guests']; ?> Guest<?php echo $b['total_guests']>1?'s':''; ?></p>
                        <p class="text-lg font-bold text-amber-500 flex items-center flex-wrap"><i class="fas fa-credit-card text-amber-500 mr-1"></i><?php echo formatCurrency($b['total_price']); ?>
                            <?php
                            $ps = $b['payment_status'];
                            $hasPending = isset($b['pending_payment_count']) && $b['pending_payment_count'] > 0;
                            if ($ps === 'Paid') { $bc = 'bg-emerald-50 text-emerald-700 border border-emerald-200'; $label = 'Paid'; }
                            elseif ($ps === 'Refunded') { $bc = 'bg-slate-100 text-slate-700 border border-slate-300'; $label = 'Refunded'; }
                            elseif ($ps === 'Unpaid' && !$hasPending) { $bc = 'bg-rose-50 text-rose-700 border border-rose-200'; $label = 'Unpaid'; }
                            else { $bc = 'bg-amber-50 text-amber-700 border border-amber-200'; $label = 'Awaiting Verification'; }
                            ?>
                            <span class="<?php echo $bc; ?> text-xs font-medium px-2.5 py-0.5 rounded-full inline-flex items-center ml-2"><?php echo $label; ?></span>
                        </p>
                    </div>
                    <div class="text-right relative z-10">
                        <a href="booking-details.php?id=<?php echo $b['reservation_id']; ?>" class="text-amber-500 hover:text-amber-600 hover:underline text-sm block mb-1 relative z-10"><i class="fas fa-eye"></i> View</a>
                        <?php if ($b['booking_status'] == 'Pending'): ?>
                        <a href="?cancel=<?php echo $b['reservation_id']; ?>" data-cancel="1" class="text-red-600 hover:text-red-700 hover:underline text-sm relative z-10 js-confirm-action" onclick="event.preventDefault(); window._confirmUrl=this.href; showConfirmModal({title:'Cancel Booking',message:'Are you sure you want to cancel this booking? This action cannot be undone.',icon:'fa-times',iconBg:'bg-amber-500/10',iconBorder:'border border-amber-500/20',iconColor:'text-amber-400',btnText:'Yes, Cancel',action:function(){window.location.href=window._confirmUrl;}});"><i class="fas fa-times"></i> Cancel</a>
                        <?php endif; ?>
                        <?php if (in_array($b['booking_status'], ['Checked Out', 'Rejected'])): ?>
                        <form method="post" action="process_customer_action.php" class="inline-block relative z-10">
                            <input type="hidden" name="action" value="delete_history">
                            <input type="hidden" name="reservation_id" value="<?php echo $b['reservation_id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <button type="button" class="text-red-600 hover:text-red-700 hover:underline text-sm relative z-10 js-confirm-action" onclick="event.preventDefault(); window._confirmForm=this.closest('form'); showConfirmModal({title:'Delete History',message:'Are you sure you want to delete this booking history? It will remain in our records but be hidden from your view.',icon:'fa-trash-alt',iconBg:'bg-rose-500/10',iconBorder:'border border-rose-500/20',iconColor:'text-rose-400',btnText:'Yes, Delete',action:function(){window._confirmForm.submit();}});"><i class="fas fa-trash-alt"></i> Delete History</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
// --- Custom Confirm Modal ---
var confirmCallback = null;

function showConfirmModal(opts) {
    var modal = document.getElementById('confirmModal');
    var card = document.getElementById('confirmModalCard');
    var iconEl = document.getElementById('confirmModalIcon');
    var titleEl = document.getElementById('confirmModalTitle');
    var msgEl = document.getElementById('confirmModalMessage');
    var actionBtn = document.getElementById('confirmModalAction');

    iconEl.className = 'w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner ' + (opts.iconBg || 'bg-amber-500/10') + ' ' + (opts.iconBorder || 'border border-amber-500/20');
    iconEl.innerHTML = '<i class="fas ' + (opts.icon || 'fa-exclamation-triangle') + ' ' + (opts.iconColor || 'text-amber-400') + ' text-xl"></i>';
    titleEl.textContent = opts.title || 'Are you sure?';
    msgEl.textContent = opts.message || 'This action cannot be undone.';
    actionBtn.textContent = opts.btnText || 'Confirm';
    actionBtn.className = 'w-full py-3 px-4 rounded-xl text-sm font-semibold text-white transition-all duration-200 ' + (opts.btnClass || 'bg-gradient-to-r from-red-800 to-rose-700 hover:from-red-700 hover:to-rose-600 shadow-lg shadow-red-950/50 border border-red-500/30');
    confirmCallback = opts.action || null;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(function() {
        card.style.transform = 'scale(1)';
        card.style.opacity = '1';
    }, 10);
}

function closeConfirmModal() {
    var modal = document.getElementById('confirmModal');
    var card = document.getElementById('confirmModalCard');
    card.style.transform = 'scale(0.95)';
    card.style.opacity = '0';
    setTimeout(function() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        confirmCallback = null;
    }, 200);
}

document.getElementById('confirmModalAction').addEventListener('click', function() {
    if (confirmCallback) confirmCallback();
    closeConfirmModal();
});

document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) closeConfirmModal();
});

// --- Custom Alert Modal ---
function showAlertModal(opts) {
    var modal = document.getElementById('alertModal');
    var card = document.getElementById('alertModalCard');
    var iconBg = document.getElementById('alertModalIcon');
    var iconEl = document.getElementById('alertModalIconEl');
    var titleEl = document.getElementById('alertModalTitle');
    var msgEl = document.getElementById('alertModalMessage');

    var isSuccess = opts.type === 'success';
    iconBg.className = 'inline-flex items-center justify-center p-3 rounded-full mb-4 ' + (isSuccess ? 'bg-emerald-500/20' : 'bg-red-500/20');
    iconEl.className = 'fas ' + (isSuccess ? 'fa-check text-emerald-400' : 'fa-exclamation-triangle text-red-400') + ' text-xl';
    titleEl.textContent = opts.title || 'Notice';
    msgEl.textContent = opts.message || '';

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(function() {
        card.style.transform = 'scale(1)';
        card.style.opacity = '1';
    }, 10);
}

function closeAlertModal() {
    var modal = document.getElementById('alertModal');
    var card = document.getElementById('alertModalCard');
    card.style.transform = 'scale(0.95)';
    card.style.opacity = '0';
    setTimeout(function() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 200);
}

document.getElementById('alertModal').addEventListener('click', function(e) {
    if (e.target === this) closeAlertModal();
});

// Show success modal on page load if PHP session had a message
<?php if ($modalAlertMsg): ?>
document.addEventListener("DOMContentLoaded", function() {
    showAlertModal({type:'success',title:'<?php echo addslashes($modalAlertTitle); ?>',message:'<?php echo addslashes($modalAlertMsg); ?>'});
});
<?php endif; ?>
</script>

<script src="<?php echo SITE_URL; ?>assets/js/main.js"></script>
</body>
</html>
