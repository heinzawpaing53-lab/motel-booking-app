<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) { redirect('login.php'); }

define('PAGE_TITLE', 'System Settings');

$userId = $_SESSION['user_id'];
$csrfToken = generateCsrfToken();
$message = '';
$messageType = '';

// Fetch user for password change
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$adminUser = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token. Please try again.';
        $messageType = 'error';
    } elseif (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmNewPassword = $_POST['confirm_new_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmNewPassword)) {
            $message = 'All password fields are required.';
            $messageType = 'error';
        } elseif (!password_verify($currentPassword, $adminUser['password'])) {
            $message = 'Current password is incorrect.';
            $messageType = 'error';
        } elseif (strlen($newPassword) < 6) {
            $message = 'New password must be at least 6 characters.';
            $messageType = 'error';
        } elseif ($newPassword !== $confirmNewPassword) {
            $message = 'New passwords do not match.';
            $messageType = 'error';
        } else {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password=? WHERE user_id=?");
            $stmt->execute([$hashed, $userId]);
            $message = 'Password updated successfully.';
            $messageType = 'success';
            logActivity($pdo, $userId, 'Password Change', 'Admin changed password');
        }
    } else {
        $settings = $_POST['setting'] ?? [];
        $updated = 0;
        foreach ($settings as $key => $value) {
            $sanitizedKey = sanitize($key);
            $sanitizedValue = sanitize($value);
            if (!empty($sanitizedKey)) {
                $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                $stmt->execute([$sanitizedValue, $sanitizedKey]);
                if ($stmt->rowCount() > 0) {
                    $updated++;
                }
            }
        }
        $message = "Settings updated successfully. ($updated setting(s) changed.)";
        $messageType = 'success';
        logActivity($pdo, $userId, 'Updated Settings', "Updated $updated setting(s)");
    }
}

$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings ORDER BY setting_key");
$settings = $stmt->fetchAll();

$settingLabels = [
    'site_name' => 'Site Name',
    'site_email' => 'Site Email',
    'site_phone' => 'Site Phone',
    'site_address' => 'Site Address',
    'check_in_time' => 'Check-in Time',
    'check_out_time' => 'Check-out Time',
    'tax_rate' => 'Tax Rate (%)',
    'currency' => 'Currency',
    'max_guests_per_room' => 'Max Guests Per Room',
];

$settingIcons = [
    'site_name' => 'fa-globe',
    'site_email' => 'fa-envelope',
    'site_phone' => 'fa-phone',
    'site_address' => 'fa-map-marker-alt',
    'check_in_time' => 'fa-clock',
    'check_out_time' => 'fa-clock',
    'tax_rate' => 'fa-percent',
    'currency' => 'fa-dollar-sign',
    'max_guests_per_room' => 'fa-user-friends',
];

$settingTypes = [
    'site_name' => 'text',
    'site_email' => 'email',
    'site_phone' => 'text',
    'site_address' => 'text',
    'check_in_time' => 'time',
    'check_out_time' => 'time',
    'tax_rate' => 'number',
    'currency' => 'text',
    'max_guests_per_room' => 'number',
];
include '../../admin/header.php';
?>

<div class="p-6">
    <div class="max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800"><i class="fas fa-cog text-amber-600 mr-2"></i>System Settings</h1>
                    <p class="text-slate-500 text-sm">Manage your motel configuration</p>
                </div>
            </div>

            <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-r shadow-sm border-l-4 text-sm font-medium <?php echo $messageType === 'success' ? 'bg-emerald-50 border-emerald-500 text-emerald-700' : 'bg-rose-50 border-rose-500 text-rose-700'; ?>">
                <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <!-- System Configuration -->
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <?php foreach ($settings as $setting): 
                        $key = $setting['setting_key'];
                        $label = $settingLabels[$key] ?? ucwords(str_replace('_', ' ', $key));
                        $icon = $settingIcons[$key] ?? 'fa-cog';
                        $type = $settingTypes[$key] ?? 'text';
                        $isTextarea = in_array($key, ['site_address']);
                    ?>
                    <div class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition">
                        <label for="setting_<?php echo $key; ?>" class="flex items-center space-x-2 text-sm font-semibold text-slate-700 mb-3">
                            <i class="fas <?php echo $icon; ?> text-amber-500"></i>
                            <span><?php echo $label; ?></span>
                        </label>
                        <?php if ($isTextarea): ?>
                        <textarea name="setting[<?php echo $key; ?>]" id="setting_<?php echo $key; ?>" rows="2" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none text-sm bg-slate-50"><?php echo sanitize($setting['setting_value']); ?></textarea>
                        <?php else: ?>
                        <input type="<?php echo $type; ?>" name="setting[<?php echo $key; ?>]" id="setting_<?php echo $key; ?>" value="<?php echo sanitize($setting['setting_value']); ?>" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none text-sm bg-slate-50" <?php echo $type === 'number' ? 'step="any"' : ''; ?>>
                        <?php endif; ?>
                        <p class="text-xs text-slate-400 mt-1">Key: <?php echo $key; ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-6 flex items-center justify-end space-x-4">
                    <button type="reset" class="px-6 py-2.5 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition font-semibold text-sm"><i class="fas fa-undo mr-2"></i>Reset</button>
                    <button type="submit" class="px-6 py-2.5 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition font-semibold text-sm"><i class="fas fa-save mr-2"></i>Save Settings</button>
                </div>
            </form>
        </div>
    </div>

<?php include '../../includes/admin-footer.php'; ?>
