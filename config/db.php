<?php
ini_set('session.cookie_lifetime', 0);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', 0);
session_start();

$host = 'localhost';
$dbname = 'motel_booking';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

define('SITE_URL', '/motel-app/');
define('SITE_NAME', 'Luxury Motel');

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1;
}

function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit();
}

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function sanitize($input) {
    if ($input === null) { return ''; }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function logActivity($pdo, $userId, $action, $description = '') {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $action, $description, $ip]);
    } catch (PDOException $e) {
        // Silently fail if activity_logs table doesn't exist yet
    }
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

function getSetting($pdo, $key) {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['setting_value'] : null;
}

function badgeClass($status) {
    return strtolower(str_replace(' ', '-', $status));
}

function oldInput($key, $default = '') {
    return isset($_POST[$key]) ? sanitize($_POST[$key]) : $default;
}

if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT status FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch();
    if (!$row || $row['status'] !== 'Active') {
        session_unset();
        session_destroy();
        header("Location: " . SITE_URL . "login.php");
        exit();
    }
}
