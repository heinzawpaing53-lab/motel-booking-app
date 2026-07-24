<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}
define('PAGE_TITLE', 'Payments');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_paid') {
    $reservationId = (int)($_POST['reservation_id'] ?? 0);
    $paymentMethod = sanitize($_POST['payment_method'] ?? 'Cash');
    $customAmount = $_POST['custom_amount'] ?? '';

    if ($reservationId <= 0) {
        $_SESSION['error'] = 'Invalid reservation.';
        redirect('admin/bookings/payments.php');
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT * FROM reservations WHERE reservation_id = ? FOR UPDATE");
        $stmt->execute([$reservationId]);
        $reservation = $stmt->fetch();

        if (!$reservation) {
            throw new Exception('Reservation not found.');
        }

        if ($reservation['payment_status'] === 'Paid') {
            throw new Exception('Payment already recorded for this reservation.');
        }

        $amount = $reservation['total_price'];
        if ($customAmount !== '' && is_numeric($customAmount) && $customAmount > 0) {
            $amount = (float)$customAmount;
        }

        $year = date('Y');
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM payments WHERE invoice_number LIKE ?");
        $stmt->execute(["INV-$year-%"]);
        $count = (int)$stmt->fetchColumn() + 1;
        $invoiceNumber = "INV-$year-" . str_pad($count, 4, '0', STR_PAD_LEFT);

        $stmt = $pdo->prepare("INSERT INTO payments (reservation_id, invoice_number, amount_paid, payment_method, payment_status) VALUES (?, ?, ?, ?, 'Completed')");
        $stmt->execute([$reservationId, $invoiceNumber, $amount, $paymentMethod]);

        $stmt = $pdo->prepare("UPDATE reservations SET payment_status = 'Paid' WHERE reservation_id = ?");
        $stmt->execute([$reservationId]);

        logActivity($pdo, $_SESSION['user_id'], 'Payment Received', "Invoice $invoiceNumber for Reservation #$reservationId - $" . number_format($amount, 2));

        $pdo->commit();
        $_SESSION['success'] = "Payment recorded. Invoice <strong>$invoiceNumber</strong> generated for $" . number_format($amount, 2) . ".";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = $e->getMessage();
    }

    redirect('admin/bookings/payments.php');
}

$tab = $_GET['tab'] ?? 'ledger';

$messages = [];
if (isset($_SESSION['success'])) { $messages['success'] = $_SESSION['success']; unset($_SESSION['success']); }
if (isset($_SESSION['error'])) { $messages['error'] = $_SESSION['error']; unset($_SESSION['error']); }
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
            <h1 class="text-2xl font-bold text-gray-800"><i class="fas fa-credit-card text-blue-600 mr-2"></i>Payments</h1>
            <p class="text-gray-500 text-sm">Manage billing and payment records</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="?tab=ledger" class="px-4 py-2 rounded-lg text-sm font-semibold transition <?php echo $tab === 'ledger' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>"><i class="fas fa-book mr-1"></i>Ledger</a>
            <a href="?tab=unpaid" class="px-4 py-2 rounded-lg text-sm font-semibold transition <?php echo $tab === 'unpaid' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>"><i class="fas fa-clock mr-1"></i>Pending Payments</a>
        </div>
    </div>

    <?php if (isset($messages['success'])): ?>
    <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $messages['success']; ?></div>
    <?php endif; ?>
    <?php if (isset($messages['error'])): ?>
    <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $messages['error']; ?></div>
    <?php endif; ?>

    <?php if ($tab === 'ledger'): ?>
    <?php
    $stmt = $pdo->query("
        SELECT p.*, r.reservation_id, r.total_price, r.payment_status, r.check_in_date, r.check_out_date,
               u.first_name, u.last_name, u.email,
               rm.room_number, rm.room_name
        FROM payments p
        JOIN reservations r ON p.reservation_id = r.reservation_id
        JOIN users u ON r.user_id = u.user_id
        JOIN rooms rm ON r.room_id = rm.room_id
        ORDER BY p.paid_at DESC
    ");
    $payments = $stmt->fetchAll();

    $totalCollected = 0;
    foreach ($payments as $p) { $totalCollected += $p['amount_paid']; }
    ?>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
            <p class="text-sm text-gray-500 font-medium">Total Transactions</p>
            <p class="text-3xl font-bold text-gray-800"><?php echo count($payments); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
            <p class="text-sm text-gray-500 font-medium">Total Collected</p>
            <p class="text-3xl font-bold text-green-600"><?php echo formatCurrency($totalCollected); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-purple-500">
            <p class="text-sm text-gray-500 font-medium">Average Payment</p>
            <p class="text-3xl font-bold text-purple-600"><?php echo count($payments) > 0 ? formatCurrency($totalCollected / count($payments)) : formatCurrency(0); ?></p>
        </div>
    </div>

    <div class="w-full overflow-x-auto rounded-2xl border border-stone-200/80 bg-white shadow-sm">
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead>
                    <tr class="bg-[#2A1810] text-amber-100 border-b-2 border-amber-500/30">
                        <th class="px-14 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left min-w-[140px]">Invoice </th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left min-w-[160px]">Customer</th>
                        <th class="px-12 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left min-w-[120px]">Room</th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-right pr-6">Amount</th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left">Method</th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-center min-w-[120px]">Status</th>
                        <th class="px-10 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $p): ?>
                    <tr class="hover:bg-amber-50/30 transition-colors border-b border-stone-100 last:border-none">
                        <td class="px-4 py-4 font-mono font-semibold text-blue-700 whitespace-nowrap"><?php echo sanitize($p['invoice_number']); ?></td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="font-semibold text-gray-800"><?php echo sanitize($p['first_name'] . ' ' . $p['last_name']); ?></div>
                            <div class="text-gray-400 text-xs"><?php echo sanitize($p['email']); ?></div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap"><?php echo sanitize($p['room_name'] ?: $p['room_number']); ?></td>
                        <td class="px-4 py-4 text-sm font-semibold text-stone-900 text-right pr-6 whitespace-nowrap"><?php echo formatCurrency($p['amount_paid']); ?></td>
                        <td class="px-4 py-4 whitespace-nowrap"><?php echo sanitize($p['payment_method']); ?></td>
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            <span class="whitespace-nowrap inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide bg-emerald-100 text-emerald-800 border border-emerald-200"><?php echo $p['payment_status']; ?></span>
                        </td>
                        <td class="px-4 py-4 text-gray-500 whitespace-nowrap"><?php echo formatDate($p['paid_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($payments)): ?>
                    <tr><td colspan="7" class="p-8 text-center text-gray-400">No payment records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
    </div>
    <?php endif; ?>

    <?php if ($tab === 'unpaid'): ?>
    <?php
    $countStmt = $pdo->query("
        SELECT COUNT(*) AS total_pending
        FROM reservations
        WHERE booking_status IN ('Approved', 'Checked In') AND payment_status = 'Unpaid'
    ");
    $pending_count = (int)($countStmt->fetch()['total_pending'] ?? 0);

    $sumStmt = $pdo->query("
        SELECT SUM(total_price) AS total_outstanding
        FROM reservations
        WHERE booking_status IN ('Approved', 'Checked In') AND payment_status = 'Unpaid'
    ");
    $total_outstanding = (float)($sumStmt->fetch()['total_outstanding'] ?? 0);

    $ledgerStmt = $pdo->query("
        SELECT r.reservation_id, r.total_price, r.check_in_date, r.check_out_date,
               u.first_name, u.last_name, u.email, rm.room_number, rm.room_name
        FROM reservations r
        JOIN users u ON r.user_id = u.user_id
        JOIN rooms rm ON r.room_id = rm.room_id
        WHERE r.booking_status IN ('Approved', 'Checked In') AND r.payment_status = 'Unpaid'
        ORDER BY r.check_in_date ASC
    ");
    $pending_payments_list = $ledgerStmt->fetchAll();
    ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-yellow-500">
            <p class="text-sm text-gray-500 font-medium">Pending Payments</p>
            <p class="text-3xl font-bold text-gray-800"><?php echo $pending_count; ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-red-500">
            <p class="text-sm text-gray-500 font-medium">Total Outstanding</p>
            <p class="text-3xl font-bold text-red-600"><?php echo formatCurrency($total_outstanding); ?></p>
        </div>
    </div>

    <?php if (!empty($pending_payments_list)): ?>
    <div class="w-full overflow-x-auto rounded-2xl border border-stone-200/80 bg-white shadow-sm">
            <table class="w-full text-left border-collapse min-w-[1100px]">
                <thead>
                    <tr class="bg-[#2A1810] text-amber-100 border-b-2 border-amber-500/30">
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-center min-w-[100px]">Reservation</th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left min-w-[160px]">Customer</th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left min-w-[120px]">Room</th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left">Check In</th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left">Check Out</th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-right pr-6">Amount</th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-center min-w-[140px]">Status</th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-center min-w-[280px]">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_payments_list as $u): ?>
                    <tr class="hover:bg-amber-50/30 transition-colors border-b border-stone-100 last:border-none">
                        <td class="px-4 py-4 text-center font-semibold whitespace-nowrap">#<?php echo $u['reservation_id']; ?></td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="font-semibold text-gray-800"><?php echo sanitize($u['first_name'] . ' ' . $u['last_name']); ?></div>
                            <div class="text-gray-400 text-xs"><?php echo sanitize($u['email']); ?></div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap"><?php echo sanitize($u['room_name'] ?: $u['room_number']); ?></td>
                        <td class="px-4 py-4 whitespace-nowrap"><?php echo formatDate($u['check_in_date']); ?></td>
                        <td class="px-4 py-4 whitespace-nowrap"><?php echo formatDate($u['check_out_date']); ?></td>
                        <td class="px-4 py-4 text-sm font-semibold text-stone-900 text-right pr-6 whitespace-nowrap"><?php echo formatCurrency($u['total_price']); ?></td>
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            <span class="whitespace-nowrap inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide bg-amber-100 text-amber-800 border border-amber-200">Pending Payment</span>
                        </td>
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            <form action="../process_action.php" method="POST" class="inline-flex items-center justify-center gap-2">
                                <input type="hidden" name="reservation_id" value="<?php echo $u['reservation_id']; ?>">
                                <select name="payment_method" class="h-8 text-xs rounded-lg border border-stone-200 px-2 bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                                    <option value="Cash">Cash</option>
                                    <option value="Credit Card">Credit Card</option>
                                    <option value="Debit Card">Debit Card</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="PayPal">PayPal</option>
                                </select>
                                <button type="submit" name="action" value="mark_paid" class="h-8 w-[120px] inline-flex items-center justify-center gap-1.5 text-xs font-semibold rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 transition-all shadow-sm shrink-0"><i class="fas fa-check"></i>Mark as Paid</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <i class="fas fa-check-circle text-green-400 text-6xl mb-4"></i>
        <p class="text-lg font-semibold text-gray-700">All payments are settled!</p>
        <p class="text-gray-400 text-sm mt-1">There are no outstanding payments.</p>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<div id="paymentModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 relative">
        <button onclick="closePaymentModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 text-xl">&times;</button>
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check-circle text-emerald-600 text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900">Record Payment</h3>
            <p class="text-slate-500 text-sm mt-1">Mark this reservation as paid</p>
        </div>
        <div id="modalBookingInfo" class="bg-slate-50 rounded-lg p-4 mb-6 text-sm space-y-1">
            <div class="flex justify-between"><span class="text-slate-500">Customer:</span><span class="font-semibold text-slate-800" id="modalCustomer">-</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Room:</span><span class="font-semibold text-slate-800" id="modalRoom">-</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Amount Due:</span><span class="font-semibold text-slate-800" id="modalAmount">-</span></div>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="mark_paid">
            <input type="hidden" name="reservation_id" id="modalReservationId" value="0">
            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-600 mb-1">Payment Method</label>
                <select name="payment_method" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="Cash">Cash</option>
                    <option value="Credit Card">Credit Card</option>
                    <option value="Debit Card">Debit Card</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="GCash">GCash</option>
                    <option value="PayPal">PayPal</option>
                </select>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-semibold text-slate-600 mb-1">Amount <span class="text-slate-400 font-normal">(leave blank for full amount)</span></label>
                <input type="number" step="0.01" min="0" name="custom_amount" id="modalCustomAmount" placeholder="Full amount" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <button type="submit" class="w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition"><i class="fas fa-check mr-2"></i>Confirm Payment</button>
        </form>
    </div>
</div>

<button id="modalCloseBtn" class="hidden" onclick="closePaymentModal()"></button>

<script>
function openPaymentModal(id, customer, room, amount) {
    document.getElementById('modalReservationId').value = id;
    document.getElementById('modalCustomer').textContent = customer;
    document.getElementById('modalRoom').textContent = room;
    document.getElementById('modalAmount').textContent = '$' + amount.toFixed(2);
    document.getElementById('modalCustomAmount').value = '';
    document.getElementById('paymentModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('click', function(e) {
    const modal = document.getElementById('paymentModal');
    if (e.target === modal) closePaymentModal();
});
</script>

</body>
</html>
