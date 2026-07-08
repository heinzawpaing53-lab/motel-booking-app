<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT room_number FROM rooms WHERE room_id = ?");
    $stmt->execute([$id]);
    $room = $stmt->fetch();

    if ($room) {
        $stmt = $pdo->prepare("DELETE FROM room_amenities WHERE room_id = ?");
        $stmt->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE room_id = ?");
        $stmt->execute([$id]);
        logActivity($pdo, $_SESSION['user_id'], 'Delete Room', "Deleted room {$room['room_number']}");
    }
}

redirect('admin/rooms/index.php');
