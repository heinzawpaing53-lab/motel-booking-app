<?php
require_once 'config/db.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $reservationId = filter_input(INPUT_POST, 'reservation_id', FILTER_VALIDATE_INT);
    $token = $_POST['csrf_token'] ?? '';

    if (!$reservationId) {
        $_SESSION['error'] = 'Invalid reservation reference.';
        redirect('booking-history.php');
    }

    if (!verifyCsrfToken($token)) {
        $_SESSION['error'] = 'Security validation failed. Please try again.';
        redirect('booking-history.php');
    }

    try {
        switch ($action) {
            case 'delete_history':
                $stmt = $pdo->prepare("UPDATE reservations SET customer_hidden = 1 WHERE reservation_id = :id AND user_id = :user_id");
                $stmt->execute(['id' => $reservationId, 'user_id' => $_SESSION['user_id']]);

                if ($stmt->rowCount() === 0) {
                    throw new Exception('Booking not found or already hidden.');
                }

                logActivity($pdo, $_SESSION['user_id'], 'Booking Hidden', "Reservation #$reservationId hidden from customer view.");
                $_SESSION['success'] = 'Booking removed from your history.';
                break;

            default:
                $_SESSION['error'] = 'Unknown action.';
                break;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'System Error: ' . $e->getMessage();
    }

    redirect('booking-history.php');
}

redirect('booking-history.php');
