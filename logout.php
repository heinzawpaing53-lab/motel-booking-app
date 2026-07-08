<?php
require_once 'config/db.php';

if (isLoggedIn()) {
    logActivity($pdo, $_SESSION['user_id'], 'Logout', 'User logged out');
}

session_destroy();
redirect('index.php');
