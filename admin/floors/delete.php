<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT floor_name FROM floors WHERE floor_id = ?");
    $stmt->execute([$id]);
    $floor = $stmt->fetch();

    if ($floor) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE floor_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            $_SESSION['error'] = 'Cannot delete floor with associated rooms.';
            redirect('admin/rooms/index.php?tab=floors');
        }
        $stmt = $pdo->prepare("DELETE FROM floors WHERE floor_id = ?");
        $stmt->execute([$id]);
        logActivity($pdo, $_SESSION['user_id'], 'Delete Floor', "Deleted floor {$floor['floor_name']}");
    }
}

redirect('admin/rooms/index.php?tab=floors');
