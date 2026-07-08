<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM special_requests WHERE request_id = ?");
$stmt->execute([$id]);
$request = $stmt->fetch();

if (!$request) {
    $_SESSION['error'] = 'Special request not found.';
    redirect('admin/requests/index.php');
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("DELETE FROM special_requests WHERE request_id = ?");
    $stmt->execute([$id]);

    logActivity($pdo, $_SESSION['user_id'], 'Special Request Deleted', "Special request '{$request['request_name']}' deleted.");

    $pdo->commit();
    $_SESSION['success'] = 'Special request deleted successfully.';
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Cannot delete this special request. It may be referenced by existing reservations.';
}

redirect('admin/requests/index.php');
