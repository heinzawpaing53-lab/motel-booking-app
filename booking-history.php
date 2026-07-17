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

    /* --- Compact Booking Card Grid --- */
    .booking-card-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    @media (max-width: 768px) {
        .booking-card-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="py-16 bg-luxury-50 min-h-screen">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h3 class="font-[Playfair_Display] text-4xl">My Bookings</h3>
                <p class="text-gray-500">Manage your reservations</p>
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
                    <div class="text-right">
                        <a href="booking-details.php?id=<?php echo $b['reservation_id']; ?>" class="text-amber-500 hover:underline text-sm block mb-1"><i class="fas fa-eye"></i> View</a>
                        <?php if ($b['booking_status'] == 'Pending'): ?>
                        <a href="?cancel=<?php echo $b['reservation_id']; ?>" class="text-red-600 hover:underline text-sm" onclick="var _t=this;event.preventDefault();showSystemModal('Cancel Booking','Cancel this booking?','info',function(){location.href=_t.href;})"><i class="fas fa-times"></i> Cancel</a>
                        <?php endif; ?>
                        <?php if (in_array($b['booking_status'], ['Checked Out', 'Rejected'])): ?>
                        <form method="post" action="process_customer_action.php" class="inline-block">
                            <input type="hidden" name="action" value="delete_history">
                            <input type="hidden" name="reservation_id" value="<?php echo $b['reservation_id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <button type="submit" class="text-red-600 hover:underline text-sm" onclick="var _f=this.form;event.preventDefault();showSystemModal('Remove from View','Hide this booking from your history? It will remain in our records.','warning',function(){_f.submit();})"><i class="fas fa-trash-alt"></i> Delete History</button>
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

<?php include 'includes/footer.php'; ?>
