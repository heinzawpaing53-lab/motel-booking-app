<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}
define('PAGE_TITLE', 'Add Special Request');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestName = sanitize($_POST['request_name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $active = isset($_POST['active']) ? 1 : 0;

    if (empty($requestName)) {
        $error = 'Request name is required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO special_requests (request_name, description, active) VALUES (?, ?, ?)");
        $stmt->execute([$requestName, $description, $active]);
        logActivity($pdo, $_SESSION['user_id'], 'Special Request Created', "Special request '$requestName' created.");
        $_SESSION['success'] = 'Special request created successfully.';
        redirect('admin/requests/index.php');
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

<div class="ml-64 p-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="index.php" class="text-blue-600 hover:text-blue-800 text-sm"><i class="fas fa-arrow-left mr-1"></i>Back to Special Requests</a>
            <h1 class="text-2xl font-bold text-gray-800 mt-1">Add Special Request</h1>
        </div>
    </div>

    <?php if ($error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm p-6 max-w-2xl">
        <form method="POST">
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-600 mb-1">Request Name <span class="text-red-500">*</span></label>
                <input type="text" name="request_name" value="<?php echo sanitize($_POST['request_name'] ?? ''); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-600 mb-1">Description</label>
                <textarea name="description" rows="4" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"><?php echo sanitize($_POST['description'] ?? ''); ?></textarea>
            </div>
            <div class="mb-6">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="active" value="1" <?php echo ($_POST['active'] ?? 1) ? 'checked' : ''; ?> class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="ml-2 text-sm font-semibold text-gray-600">Active</span>
                </label>
            </div>
            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"><i class="fas fa-save mr-2"></i>Create Request</button>
        </form>
    </div>
</div>

</body>
</html>
