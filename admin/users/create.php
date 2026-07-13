<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}
define('PAGE_TITLE', 'Add Customer');

$error = '';
$success = '';

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

    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        $error = 'First name, last name, email, and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $checkStmt->execute([$email]);
        if ($checkStmt->fetchColumn() > 0) {
            $error = 'A user with this email already exists.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (role_id, first_name, last_name, email, password, phone, gender, nationality, address, status) VALUES (2, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$firstName, $lastName, $email, password_hash($password, PASSWORD_DEFAULT), $phone, $gender, $nationality, $address, $status]);
            logActivity($pdo, $_SESSION['user_id'], 'Customer Created', "Customer $firstName $lastName ($email) created.");
            $_SESSION['success'] = 'Customer created successfully.';
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
            <h1 class="text-2xl font-bold text-gray-800 mt-1">Add Customer</h1>
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
                    <input type="text" name="first_name" value="<?php echo sanitize($_POST['first_name'] ?? ''); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" value="<?php echo sanitize($_POST['last_name'] ?? ''); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="<?php echo sanitize($_POST['email'] ?? ''); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Phone</label>
                    <input type="text" name="phone" value="<?php echo sanitize($_POST['phone'] ?? ''); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Gender</label>
                    <select name="gender" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">Select Gender</option>
                        <option value="Male" <?php echo ($_POST['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($_POST['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo ($_POST['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Nationality</label>
                    <input type="text" name="nationality" value="<?php echo sanitize($_POST['nationality'] ?? ''); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Status</label>
                    <select name="status" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="Active" <?php echo ($_POST['status'] ?? 'Active') === 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo ($_POST['status'] ?? '') === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-600 mb-1">Address</label>
                <textarea name="address" rows="3" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"><?php echo sanitize($_POST['address'] ?? ''); ?></textarea>
            </div>
            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"><i class="fas fa-save mr-2"></i>Create Customer</button>
        </form>
    </div>
</div>

</body>
</html>
