<?php
require_once '../config/db.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $reservationId = filter_input(INPUT_POST, 'reservation_id', FILTER_VALIDATE_INT);

    if (!$reservationId) {
        $_SESSION['error'] = 'Invalid reservation reference.';
        redirect('admin/bookings/index.php');
    }

    try {
        switch ($action) {
            case 'approve':
                $stmt = $pdo->prepare("SELECT booking_status FROM reservations WHERE reservation_id = ?");
                $stmt->execute([$reservationId]);
                $res = $stmt->fetch();
                if (!$res || $res['booking_status'] !== 'Pending') {
                    throw new Exception('Booking cannot be approved in its current status.');
                }
                $stmt = $pdo->prepare("UPDATE reservations SET booking_status = 'Approved', payment_status = 'Unpaid' WHERE reservation_id = ?");
                $stmt->execute([$reservationId]);
                logActivity($pdo, $_SESSION['user_id'], 'Booking Approved', "Booking #$reservationId approved.");
                $_SESSION['success'] = "Reservation #$reservationId approved. Balance forwarded to Pending Payments.";
                break;

            case 'reject':
                $stmt = $pdo->prepare("SELECT booking_status FROM reservations WHERE reservation_id = ?");
                $stmt->execute([$reservationId]);
                $res = $stmt->fetch();
                if (!$res || $res['booking_status'] !== 'Pending') {
                    throw new Exception('Booking cannot be rejected in its current status.');
                }
                $stmt = $pdo->prepare("UPDATE reservations SET booking_status = 'Rejected' WHERE reservation_id = ?");
                $stmt->execute([$reservationId]);
                logActivity($pdo, $_SESSION['user_id'], 'Booking Rejected', "Booking #$reservationId rejected.");
                $_SESSION['success'] = "Reservation #$reservationId has been rejected.";
                break;

            case 'checkout':
                $stmt = $pdo->prepare("SELECT booking_status FROM reservations WHERE reservation_id = ?");
                $stmt->execute([$reservationId]);
                $res = $stmt->fetch();
                if (!$res || $res['booking_status'] !== 'Checked In') {
                    throw new Exception('Booking must be checked in before checkout.');
                }
                $stmt = $pdo->prepare("UPDATE reservations SET booking_status = 'Checked Out' WHERE reservation_id = ?");
                $stmt->execute([$reservationId]);
                $roomStmt = $pdo->prepare("UPDATE rooms SET status = 'Available' WHERE room_id = (SELECT room_id FROM reservations WHERE reservation_id = ?)");
                $roomStmt->execute([$reservationId]);
                logActivity($pdo, $_SESSION['user_id'], 'Guest Checked Out', "Reservation #$reservationId checked out.");
                $_SESSION['success'] = "Guest checked out successfully.";
                break;

            case 'mark_paid':
                $paymentMethod = filter_input(INPUT_POST, 'payment_method', FILTER_DEFAULT) ?? 'Cash';

                $stmt = $pdo->prepare("SELECT total_price, payment_status FROM reservations WHERE reservation_id = ?");
                $stmt->execute([$reservationId]);
                $reservation = $stmt->fetch();

                if (!$reservation) {
                    throw new Exception('Reservation not found.');
                }
                if ($reservation['payment_status'] === 'Paid') {
                    throw new Exception('This invoice is already settled.');
                }

                $pdo->beginTransaction();

                $updateRes = $pdo->prepare("UPDATE reservations SET payment_status = 'Paid' WHERE reservation_id = ?");
                $updateRes->execute([$reservationId]);

                $invoiceNum = 'INV-' . date('Ymd') . '-' . str_pad($reservationId, 4, '0', STR_PAD_LEFT);
                $txRef = 'TXN-' . strtoupper(bin2hex(random_bytes(4)));

                $insertPay = $pdo->prepare("
                    INSERT INTO payments (reservation_id, invoice_number, amount_paid, payment_method, transaction_reference, payment_status)
                    VALUES (?, ?, ?, ?, ?, 'Completed')
                ");
                $insertPay->execute([
                    $reservationId,
                    $invoiceNum,
                    $reservation['total_price'],
                    $paymentMethod,
                    $txRef
                ]);

                $pdo->commit();
                logActivity($pdo, $_SESSION['user_id'], 'Payment Received', "Invoice $invoiceNum for Reservation #$reservationId");
                $_SESSION['success'] = "Payment completed! Generated Invoice: $invoiceNum";
                break;

            default:
                $_SESSION['error'] = 'Unknown action.';
                break;
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error'] = 'System Error: ' . $e->getMessage();
    }

    $referer = $_SERVER['HTTP_REFERER'] ?? null;
    if ($referer) {
        $parsed = parse_url($referer);
        $path = $parsed['path'] ?? '';
        $siteUrlPath = parse_url(SITE_URL, PHP_URL_PATH);
        if ($siteUrlPath && strpos($path, $siteUrlPath) === 0) {
            $page = substr($path, strlen($siteUrlPath));
        } else {
            $page = ltrim($path, '/');
        }
        $page = ltrim($page, '/');
        if (empty($page) || $page === 'process_action.php') {
            $page = 'admin/bookings/index.php';
        } elseif (isset($parsed['query'])) {
            $page .= '?' . $parsed['query'];
        }
        redirect($page);
    }
    redirect('admin/bookings/index.php');
    exit();
}

redirect('admin/bookings/index.php');