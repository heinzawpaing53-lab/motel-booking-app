<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$action = $_REQUEST['action'] ?? '';
$id = (int)($_REQUEST['id'] ?? 0);

if (!in_array($action, ['approve', 'reject', 'checkin', 'checkout', 'cancel'])) {
    $_SESSION['error'] = 'Invalid action.';
    redirect('admin/bookings/view.php?id=' . $id);
}

$stmt = $pdo->prepare("
    SELECT r.*, rm.room_id, rm.room_number, rm.room_name, u.first_name, u.last_name
    FROM reservations r
    JOIN rooms rm ON r.room_id = rm.room_id
    JOIN users u ON r.user_id = u.user_id
    WHERE r.reservation_id = ?
");
$stmt->execute([$id]);
$booking = $stmt->fetch();

if (!$booking) {
    $_SESSION['error'] = 'Booking not found.';
    redirect('admin/bookings/index.php');
}

$currentStatus = $booking['booking_status'];
$allowedActions = [
    'Pending' => ['approve', 'reject'],
    'Approved' => ['checkin', 'cancel'],
    'Checked In' => ['checkout'],
];

try {
    $pdo->beginTransaction();

    if ($action === 'approve') {
        if ($currentStatus !== 'Pending') {
            throw new Exception('Booking cannot be approved in its current status.');
        }

        $overlapStmt = $pdo->prepare("
            SELECT COUNT(*) FROM reservations
            WHERE room_id = ?
            AND reservation_id != ?
            AND booking_status IN ('Approved', 'Checked In')
            AND (
                (check_in_date < ? AND check_out_date > ?)
                OR (check_in_date < ? AND check_out_date > ?)
                OR (check_in_date >= ? AND check_out_date <= ?)
            )
        ");
        $overlapStmt->execute([
            $booking['room_id'],
            $id,
            $booking['check_out_date'], $booking['check_in_date'],
            $booking['check_in_date'], $booking['check_out_date'],
            $booking['check_in_date'], $booking['check_out_date']
        ]);
        $overlapCount = $overlapStmt->fetchColumn();

        if ($overlapCount > 0) {
            throw new Exception('Room is not available for the selected dates. There are overlapping bookings.');
        }

        $updateStmt = $pdo->prepare("UPDATE reservations SET booking_status = 'Approved', payment_status = 'Unpaid' WHERE reservation_id = ?");
        $updateStmt->execute([$id]);
        logActivity($pdo, $_SESSION['user_id'], 'Booking Approved', "Booking #$id for {$booking['first_name']} {$booking['last_name']} approved.");
        $_SESSION['success'] = 'Booking has been approved successfully.';

    } elseif ($action === 'reject') {
        if ($currentStatus !== 'Pending') {
            throw new Exception('Booking cannot be rejected in its current status.');
        }
        $updateStmt = $pdo->prepare("UPDATE reservations SET booking_status = 'Rejected' WHERE reservation_id = ?");
        $updateStmt->execute([$id]);
        logActivity($pdo, $_SESSION['user_id'], 'Booking Rejected', "Booking #$id for {$booking['first_name']} {$booking['last_name']} rejected.");
        $_SESSION['success'] = 'Booking has been rejected.';

    } elseif ($action === 'checkin') {
        if ($currentStatus !== 'Approved') {
            throw new Exception('Booking must be approved before check-in.');
        }
        $updateStmt = $pdo->prepare("UPDATE reservations SET booking_status = 'Checked In' WHERE reservation_id = ?");
        $updateStmt->execute([$id]);
        $roomStmt = $pdo->prepare("UPDATE rooms SET status = 'Occupied' WHERE room_id = ?");
        $roomStmt->execute([$booking['room_id']]);
        logActivity($pdo, $_SESSION['user_id'], 'Guest Checked In', "Guest {$booking['first_name']} {$booking['last_name']} checked into {$booking['room_name']}.");
        $_SESSION['success'] = 'Guest has been checked in successfully.';

    } elseif ($action === 'checkout') {
        if ($currentStatus !== 'Checked In') {
            throw new Exception('Booking must be checked in before check-out.');
        }
        $updateStmt = $pdo->prepare("UPDATE reservations SET booking_status = 'Checked Out' WHERE reservation_id = ?");
        $updateStmt->execute([$id]);
        $roomStmt = $pdo->prepare("UPDATE rooms SET status = 'Available' WHERE room_id = ?");
        $roomStmt->execute([$booking['room_id']]);
        logActivity($pdo, $_SESSION['user_id'], 'Guest Checked Out', "Guest {$booking['first_name']} {$booking['last_name']} checked out from {$booking['room_name']}.");
        $_SESSION['success'] = 'Guest has been checked out successfully.';

    } elseif ($action === 'cancel') {
        if (!in_array($currentStatus, ['Pending', 'Approved'])) {
            throw new Exception('Booking cannot be cancelled in its current status.');
        }
        $updateStmt = $pdo->prepare("UPDATE reservations SET booking_status = 'Cancelled' WHERE reservation_id = ?");
        $updateStmt->execute([$id]);
        logActivity($pdo, $_SESSION['user_id'], 'Booking Cancelled', "Booking #$id for {$booking['first_name']} {$booking['last_name']} cancelled.");
        $_SESSION['success'] = 'Booking has been cancelled.';
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = $e->getMessage();
}

redirect('admin/bookings/view.php?id=' . $id);
