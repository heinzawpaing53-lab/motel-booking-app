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
<body class="admin-layout font-[Inter] flex h-screen w-screen overflow-hidden bg-slate-100">
<?php include '../../includes/sidebar.php'; ?>
<div class="flex-1 flex flex-col h-full overflow-hidden">
<?php include '../../includes/admin-topbar.php'; ?>
<main class="flex-1 overflow-y-auto bg-slate-50">
<div class="p-6">
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

    <div class="w-full overflow-x-auto rounded-2xl border border-stone-200/80 bg-white shadow-sm">
        <table class="w-full text-left border-collapse min-w-[1100px]">
                <thead>
                    <tr class="bg-[#2A1810] text-amber-100 border-b-2 border-amber-500/30">
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left min-w-[160px]">Guest</th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left min-w-[120px]">Room</th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left">Check In</th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left">Check Out</th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-center">Nights</th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-right pr-6">Total</th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-center min-w-[140px]">Payment</th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-center min-w-[140px]">Status</th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-center min-w-[280px]">Actions</th>
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

                    $filter = $_GET['filter'] ?? '';
                    if ($filter === 'checkins_today') {
                        $conditions[] = "DATE(r.check_in_date) = CURDATE()";
                    } elseif ($filter === 'checkouts_today') {
                        $conditions[] = "DATE(r.check_out_date) = CURDATE()";
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
                    <tr class="hover:bg-amber-50/30 transition-colors border-b border-stone-100 last:border-none">
                        <td class="px-4 py-4">
                            <div class="font-semibold text-gray-800 whitespace-nowrap"><?php echo sanitize($b['first_name'] . ' ' . $b['last_name']); ?></div>
                            <div class="text-gray-400 text-xs"><?php echo sanitize($b['email']); ?></div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap"><?php echo sanitize($b['room_name'] ?: $b['room_number']); ?></td>
                        <td class="px-4 py-4 whitespace-nowrap"><?php echo formatDate($b['check_in_date']); ?></td>
                        <td class="px-4 py-4 whitespace-nowrap"><?php echo formatDate($b['check_out_date']); ?></td>
                        <td class="px-4 py-4 text-center whitespace-nowrap"><?php echo $b['total_nights']; ?></td>
                        <td class="px-4 py-4 text-sm font-semibold text-stone-900 text-right pr-6 whitespace-nowrap"><?php echo formatCurrency($b['total_price']); ?></td>
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            <?php if ($b['payment_status'] === 'Paid'): ?>
                                <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide bg-emerald-100 text-emerald-800 border border-emerald-200 whitespace-nowrap">Paid</span>
                            <?php elseif ($b['payment_status'] === 'Refunded'): ?>
                                <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide bg-slate-100 text-slate-700 border border-slate-200 whitespace-nowrap">Refunded</span>
                            <?php else: ?>
                                <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide bg-amber-100 text-amber-800 border border-amber-200 whitespace-nowrap">Pending Payment</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            <span class="whitespace-nowrap inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide <?php
                                echo match($b['booking_status']) {
                                    'Pending' => 'bg-amber-100 text-amber-800 border border-amber-200',
                                    'Approved' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                                    'Checked In' => 'bg-indigo-100 text-indigo-800 border border-indigo-200',
                                    'Checked Out' => 'bg-indigo-100 text-indigo-800 border border-indigo-200',
                                    'Completed' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                                    'Cancelled' => 'bg-rose-100 text-rose-800 border border-rose-200',
                                    'Rejected' => 'bg-rose-100 text-rose-800 border border-rose-200',
                                    default => 'bg-slate-100 text-slate-700 border border-slate-200',
                                };
                            ?>"><?php echo $b['booking_status']; ?></span>
                        </td>
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            <div class="inline-flex items-center justify-center gap-2 min-w-[280px]">
                                <!-- Slot 1: View (always present) -->
                                <a href="view.php?id=<?php echo $b['reservation_id']; ?>" class="w-[80px] h-8 inline-flex items-center justify-center gap-1.5 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300/60 transition-all shadow-sm shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    View
                                </a>
                                <!-- Slot 2: Primary Action OR spacer -->
                                <?php if ($b['booking_status'] === 'Pending'): ?>
                                    <form method="POST" action="../process_action.php" class="inline" id="approveForm_<?php echo $b['reservation_id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="reservation_id" value="<?php echo $b['reservation_id']; ?>">
                                        <button type="button" onclick="showSystemModal('Approve','Approve this booking?','info',function(){document.getElementById('approveForm_<?php echo $b['reservation_id']; ?>').submit();})" class="w-[95px] h-8 inline-flex items-center justify-center gap-1.5 text-xs font-semibold rounded-lg transition-all shadow-sm shrink-0 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            Approve
                                        </button>
                                    </form>
                                <?php elseif ($b['booking_status'] === 'Approved'): ?>
                                    <a href="action.php?action=checkin&id=<?php echo $b['reservation_id']; ?>" class="w-[95px] h-8 inline-flex items-center justify-center gap-1.5 text-xs font-semibold rounded-lg transition-all shadow-sm shrink-0 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200" onclick="var _t=this;event.preventDefault();showSystemModal('Check In','Mark as checked in?','info',function(){location.href=_t.href;})">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M13.8 12H3"/></svg>
                                        Check In
                                    </a>
                                <?php elseif ($b['booking_status'] === 'Checked In'): ?>
                                    <form method="POST" action="../process_action.php" class="inline" id="checkoutForm_<?php echo $b['reservation_id']; ?>">
                                        <input type="hidden" name="action" value="checkout">
                                        <input type="hidden" name="reservation_id" value="<?php echo $b['reservation_id']; ?>">
                                        <button type="button" onclick="showSystemModal('Check Out','Mark as checked out?','info',function(){document.getElementById('checkoutForm_<?php echo $b['reservation_id']; ?>').submit();})" class="w-[95px] h-8 inline-flex items-center justify-center gap-1.5 text-xs font-semibold rounded-lg transition-all shadow-sm shrink-0 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                                            Check Out
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="w-[95px] h-8 shrink-0"></div>
                                <?php endif; ?>
                                <!-- Slot 3: Destructive Action OR spacer -->
                                <?php if ($b['booking_status'] === 'Pending'): ?>
                                    <form method="POST" action="../process_action.php" class="inline" id="rejectForm_<?php echo $b['reservation_id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="reservation_id" value="<?php echo $b['reservation_id']; ?>">
                                        <button type="button" onclick="showSystemModal('Reject','Reject this booking?','error',function(){document.getElementById('rejectForm_<?php echo $b['reservation_id']; ?>').submit();})" class="w-[80px] h-8 inline-flex items-center justify-center gap-1.5 text-xs font-semibold rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-all shadow-sm shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Reject
                                        </button>
                                    </form>
                                <?php elseif ($b['booking_status'] === 'Approved'): ?>
                                    <a href="action.php?action=cancel&id=<?php echo $b['reservation_id']; ?>" class="w-[80px] h-8 inline-flex items-center justify-center gap-1.5 text-xs font-semibold rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-all shadow-sm shrink-0" onclick="var _t=this;event.preventDefault();showSystemModal('Cancel','Cancel this booking?','error',function(){location.href=_t.href;})">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Cancel
                                    </a>
                                <?php else: ?>
                                    <div class="w-[80px] h-8 shrink-0"></div>
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

<?php include '../../includes/admin-footer.php'; ?>
