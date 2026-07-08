<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT amenity_name FROM amenities WHERE amenity_id = ?");
    $stmt->execute([$id]);
    $amenity = $stmt->fetch();

    if ($amenity) {
        $stmt = $pdo->prepare("DELETE FROM room_amenities WHERE amenity_id = ?");
        $stmt->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM amenities WHERE amenity_id = ?");
        $stmt->execute([$id]);
        logActivity($pdo, $_SESSION['user_id'], 'Delete Amenity', "Deleted amenity {$amenity['amenity_name']}");
    }
}

redirect('admin/amenities/index.php');
