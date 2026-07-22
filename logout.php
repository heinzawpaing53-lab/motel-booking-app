<?php
require_once 'config/db.php';

if (isLoggedIn()) {
    logActivity($pdo, $_SESSION['user_id'], 'Logout', 'User logged out');
}

$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}
session_destroy();
header("Location: " . SITE_URL . "index.php");
exit();
