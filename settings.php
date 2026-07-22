<?php
define('PAGE_TITLE', 'Settings');
require_once 'config/db.php';

if (!isLoggedIn()) { redirect('login.php'); }

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmNewPassword = $_POST['confirm_new_password'] ?? '';

    if (empty($currentPassword) || empty($newPassword) || empty($confirmNewPassword)) {
        $error = 'All password fields are required.';
    } elseif (!password_verify($currentPassword, $user['password'])) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($newPassword !== $confirmNewPassword) {
        $error = 'New passwords do not match.';
    } else {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password=? WHERE user_id=?");
        $stmt->execute([$hashed, $userId]);
        $success = 'Password updated successfully.';
    }
}

include 'includes/header.php';
?>

<section class="bg-stone-50 min-h-screen pb-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
        <div class="mb-6">
            <div class="flex items-center gap-4">
                <?php
                $imgPath = '';
                $hasImage = false;
                if (!empty($user['profile_image'])) {
                    $absPath = 'uploads/' . $user['profile_image'];
                    if (file_exists($absPath)) {
                        $imgPath = SITE_URL . 'uploads/' . $user['profile_image'];
                        $hasImage = true;
                    }
                }
                ?>
                <?php if ($hasImage): ?>
                    <img src="<?php echo $imgPath; ?>" alt="Profile" class="w-24 h-24 rounded-full border-4 border-white shadow-lg object-cover">
                <?php else: ?>
                    <div class="w-24 h-24 rounded-full border-4 border-white shadow-lg bg-luxury-200 flex items-center justify-center">
                        <span class="text-3xl font-bold text-luxury-600"><?php echo strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)); ?></span>
                    </div>
                <?php endif; ?>
                <div>
                    <h1 class="text-xl font-bold text-stone-900">Account Settings</h1>
                    <p class="text-gray-500 text-sm"><?php echo htmlspecialchars($user['first_name'].' '.$user['last_name']); ?></p>
                    <span class="inline-block mt-1.5 px-2.5 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-xs font-medium">Guest</span>
                </div>
            </div>
        </div>

        <?php if ($success): ?>
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="p-5 border-b border-gray-100">
                <h2 class="text-base font-semibold text-stone-900 flex items-center gap-2">
                    <i class="fas fa-lock text-amber-500"></i> Change Password
                </h2>
                <p class="text-sm text-gray-500 mt-1">Update your password to keep your account secure</p>
            </div>
            <form method="POST" class="p-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                    <input type="password" name="current_password" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none transition bg-gray-50 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input type="password" name="new_password" required minlength="6" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none transition bg-gray-50 text-sm">
                    <p class="text-xs text-gray-400 mt-1">Must be at least 6 characters</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <input type="password" name="confirm_new_password" required minlength="6" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none transition bg-gray-50 text-sm">
                </div>
                <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold py-2.5 rounded-xl transition-colors duration-200 text-sm">
                    <i class="fas fa-save mr-2"></i>Update Password
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-5">
                <h2 class="text-base font-semibold text-stone-900 flex items-center gap-2 mb-2">
                    <i class="fas fa-sign-out-alt text-red-400"></i> Account
                </h2>
                <p class="text-sm text-gray-500 mb-4">Sign out of your account on this device</p>
                <a href="<?php echo SITE_URL; ?>logout.php" class="block w-full text-center border-2 border-red-200 text-red-600 hover:bg-red-50 hover:border-red-300 font-semibold py-2.5 rounded-xl transition-colors duration-200 text-sm">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </div>
        </div>
    </div>
</section>

<?php
// Footer removed for cleaner dashboard layout
?>
<script src="<?php echo SITE_URL; ?>assets/js/main.js"></script>
</body>
</html>
