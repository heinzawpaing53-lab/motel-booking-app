<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ? AND role_id = 2");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error'] = 'Customer not found.';
    redirect('admin/users/index.php');
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role_id = 2");
    $stmt->execute([$id]);

    logActivity($pdo, $_SESSION['user_id'], 'Customer Deleted', "Customer {$user['first_name']} {$user['last_name']} ({$user['email']}) deleted.");

    $pdo->commit();
    $_SESSION['success'] = 'Customer deleted successfully.';
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Cannot delete customer. They may have existing reservations.';
}

redirect('admin/users/index.php');
