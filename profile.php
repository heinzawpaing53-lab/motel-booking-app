<?php
define('PAGE_TITLE', 'My Profile');
require_once 'config/db.php';

if (!isLoggedIn()) { redirect('login.php'); }

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $gender = sanitize($_POST['gender'] ?? '');
    $nationality = sanitize($_POST['nationality'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmNewPassword = $_POST['confirm_new_password'] ?? '';

    if (empty($firstName) || empty($lastName)) {
        $error = 'First name and last name are required.';
    } elseif (!empty($newPassword)) {
        if (!password_verify($currentPassword, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($newPassword) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($newPassword !== $confirmNewPassword) {
            $error = 'New passwords do not match.';
        } else {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, phone=?, gender=?, nationality=?, address=?, password=? WHERE user_id=?");
            $stmt->execute([$firstName, $lastName, $phone, $gender, $nationality, $address, $hashed, $userId]);
            $success = 'Profile and password updated successfully.';
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
        }
    } else {
        $stmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, phone=?, gender=?, nationality=?, address=? WHERE user_id=?");
        $stmt->execute([$firstName, $lastName, $phone, $gender, $nationality, $address, $userId]);
        $success = 'Profile updated successfully.';
        $_SESSION['first_name'] = $firstName;
        $_SESSION['last_name'] = $lastName;
    }
}

include 'includes/header.php';
?>

<section class="py-16 bg-gray-50 min-h-screen">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h1 class="font-[Playfair_Display] text-4xl font-bold">My Profile</h1>
        </div>

        <?php if ($success): ?>
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-sm p-8">
            <form method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">First Name *</label>
                        <input type="text" name="first_name" value="<?php echo $user['first_name']; ?>" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Last Name *</label>
                        <input type="text" name="last_name" value="<?php echo $user['last_name']; ?>" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Email</label>
                    <input type="email" value="<?php echo $user['email']; ?>" class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-100" disabled>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Phone</label>
                        <input type="text" name="phone" value="<?php echo $user['phone'] ?? ''; ?>" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Gender</label>
                        <select name="gender" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none">
                            <option value="">Select</option>
                            <option value="Male" <?php echo ($user['gender']??'')=='Male'?'selected':''; ?>>Male</option>
                            <option value="Female" <?php echo ($user['gender']??'')=='Female'?'selected':''; ?>>Female</option>
                            <option value="Other" <?php echo ($user['gender']??'')=='Other'?'selected':''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Nationality</label>
                    <input type="text" name="nationality" value="<?php echo $user['nationality'] ?? ''; ?>" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none">
                </div>
                <div class="mb-8">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Address</label>
                    <textarea name="address" rows="2" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none"><?php echo $user['address'] ?? ''; ?></textarea>
                </div>

                <h3 class="font-semibold text-lg mb-4 border-t pt-6">Change Password (optional)</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Current Password</label>
                        <input type="password" name="current_password" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">New Password</label>
                        <input type="password" name="new_password" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Confirm New</label>
                        <input type="password" name="confirm_new_password" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full text-lg py-3"><i class="fas fa-save mr-2"></i>Update Profile</button>
            </form>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
