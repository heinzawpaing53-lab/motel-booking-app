<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT type_name FROM room_types WHERE type_id = ?");
    $stmt->execute([$id]);
    $type = $stmt->fetch();

    if ($type) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE type_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            $_SESSION['error'] = 'Cannot delete room type with associated rooms.';
            redirect('admin/rooms/index.php?tab=types');
        }
        $stmt = $pdo->prepare("DELETE FROM room_types WHERE type_id = ?");
        $stmt->execute([$id]);
        logActivity($pdo, $_SESSION['user_id'], 'Delete Room Type', "Deleted room type {$type['type_name']}");
    }
}

redirect('admin/rooms/index.php?tab=types');
