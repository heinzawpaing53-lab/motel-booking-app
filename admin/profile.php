<?php
require_once '../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}
define('PAGE_TITLE', 'Admin Profile');
include 'header.php';

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $firstName = sanitize($_POST['first_name'] ?? '');
        $lastName = sanitize($_POST['last_name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');

        if (empty($firstName) || empty($lastName)) {
            $error = 'First name and last name are required.';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, phone=? WHERE user_id=?");
            $stmt->execute([$firstName, $lastName, $phone, $userId]);
            $success = 'Profile updated successfully.';
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
        }
    }
}
?>

<div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Admin Profile</h1>
                <p class="text-slate-500 text-sm">Manage your personal information</p>
            </div>
        </div>

        <?php if ($success): ?>
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Premium Profile Header -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="h-28 bg-gradient-to-r from-stone-900 via-stone-800 to-amber-950"></div>
            <div class="px-6 pb-6">
                <div class="flex items-end gap-5 -mt-10">
                    <div class="relative flex-shrink-0">
                        <?php
                        $imgPath = '';
                        $hasImage = false;
                        if (!empty($user['profile_image'])) {
                            $absPath = dirname(__DIR__) . '/uploads/' . $user['profile_image'];
                            if (file_exists($absPath)) {
                                $imgPath = SITE_URL . 'uploads/' . $user['profile_image'];
                                $hasImage = true;
                            }
                        }
                        ?>
                        <?php if ($hasImage): ?>
                            <img src="<?php echo $imgPath; ?>" alt="Profile" class="w-24 h-24 rounded-full border-4 border-white shadow-lg object-cover">
                        <?php else: ?>
                            <div class="w-24 h-24 rounded-full border-4 border-white shadow-lg bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center">
                                <span class="text-3xl font-bold text-white"><?php echo strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="pb-1">
                        <h2 class="text-xl font-bold text-white"><?php echo htmlspecialchars($user['first_name'].' '.$user['last_name']); ?></h2>
                        <p class="text-stone-300 text-sm"><?php echo htmlspecialchars($user['email']); ?></p>
                        <span class="inline-block mt-1.5 px-2.5 py-0.5 bg-amber-500/20 text-amber-300 border border-amber-400/30 rounded-full text-xs font-medium">Administrator</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="flex items-center justify-between p-5 border-b border-slate-100">
                <h3 class="text-base font-semibold text-slate-800">Personal Information</h3>
            </div>
            <form method="POST" class="p-5">
                <input type="hidden" name="action" value="update_profile">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">First Name *</label>
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none transition bg-slate-50 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Last Name *</label>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none transition bg-slate-50 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Email</label>
                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled class="w-full px-4 py-2.5 border border-slate-100 rounded-lg bg-slate-50 text-slate-400 text-sm cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Phone</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none transition bg-slate-50 text-sm">
                    </div>
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors duration-200 text-sm">
                        <i class="fas fa-save mr-1"></i>Save Changes
                    </button>
                    <a href="settings/index.php" class="border border-slate-200 text-slate-600 hover:bg-slate-50 font-medium px-6 py-2.5 rounded-lg transition-colors duration-200 text-sm">
                        <i class="fas fa-cog mr-1"></i>System Settings
                    </a>
                </div>
            </form>
        </div>
    </div>

<?php include '../includes/admin-footer.php'; ?>
