<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}
define('PAGE_TITLE', 'Edit Customer');

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ? AND role_id = 2");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    redirect('admin/users/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');
    $gender = sanitize($_POST['gender'] ?? '');
    $nationality = sanitize($_POST['nationality'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $status = sanitize($_POST['status'] ?? 'Active');

    if (empty($firstName) || empty($lastName) || empty($email)) {
        $error = 'First name, last name, and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND user_id != ?");
        $checkStmt->execute([$email, $id]);
        if ($checkStmt->fetchColumn() > 0) {
            $error = 'A user with this email already exists.';
        } else {
            if (!empty($password)) {
                $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, password = ?, phone = ?, gender = ?, nationality = ?, address = ?, status = ? WHERE user_id = ?");
                $stmt->execute([$firstName, $lastName, $email, password_hash($password, PASSWORD_DEFAULT), $phone, $gender, $nationality, $address, $status, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, gender = ?, nationality = ?, address = ?, status = ? WHERE user_id = ?");
                $stmt->execute([$firstName, $lastName, $email, $phone, $gender, $nationality, $address, $status, $id]);
            }
            logActivity($pdo, $_SESSION['user_id'], 'Customer Updated', "Customer $firstName $lastName ($email) updated.");
            $_SESSION['success'] = 'Customer updated successfully.';
            redirect('admin/users/index.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo PAGE_TITLE . ' - ' . SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
</head>
<body class="font-[Inter] bg-gray-50">
<?php include '../../includes/sidebar.php'; ?>
<?php include '../../includes/admin-topbar.php'; ?>

<div class="ml-64 p-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="index.php" class="text-blue-600 hover:text-blue-800 text-sm"><i class="fas fa-arrow-left mr-1"></i>Back to Customers</a>
            <h1 class="text-2xl font-bold text-gray-800 mt-1">Edit Customer</h1>
        </div>
    </div>

    <?php if ($error): ?>
    <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm p-6 max-w-2xl">
        <form method="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" value="<?php echo sanitize($user['first_name']); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" value="<?php echo sanitize($user['last_name']); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="<?php echo sanitize($user['email']); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">New Password</label>
                    <input type="password" name="password" placeholder="Leave blank to keep current" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Phone</label>
                    <input type="text" name="phone" value="<?php echo sanitize($user['phone'] ?? ''); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Gender</label>
                    <select name="gender" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">Select Gender</option>
                        <option value="Male" <?php echo $user['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $user['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo $user['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Nationality</label>
                    <input type="text" name="nationality" value="<?php echo sanitize($user['nationality'] ?? ''); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Status</label>
                    <select name="status" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="Active" <?php echo $user['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo $user['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-600 mb-1">Address</label>
                <textarea name="address" rows="3" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"><?php echo sanitize($user['address'] ?? ''); ?></textarea>
            </div>
            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"><i class="fas fa-save mr-2"></i>Update Customer</button>
        </form>
    </div>
</div>

</body>
</html>
