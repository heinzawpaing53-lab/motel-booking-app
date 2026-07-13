<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}
define('PAGE_TITLE', 'Customers');
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
    <?php if (isset($messages['success'])): ?>
    <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $messages['success']; ?></div>
    <?php endif; ?>
    <?php if (isset($messages['error'])): ?>
    <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $messages['error']; ?></div>
    <?php endif; ?>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Customers</h1>
            <p class="text-gray-500 text-sm">Manage registered customers</p>
        </div>
        <a href="create.php" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"><i class="fas fa-plus mr-2"></i>Add Customer</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b bg-gray-50">
                        <th class="p-4 font-semibold">#</th>
                        <th class="p-4 font-semibold">Name</th>
                        <th class="p-4 font-semibold">Email</th>
                        <th class="p-4 font-semibold">Phone</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold">Registered</th>
                        <th class="p-4 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE role_id = 2 ORDER BY created_at DESC");
                    $stmt->execute();
                    $users = $stmt->fetchAll();

                    $messages = [];
                    if (isset($_SESSION['success'])) { $messages['success'] = $_SESSION['success']; unset($_SESSION['success']); }
                    if (isset($_SESSION['error'])) { $messages['error'] = $_SESSION['error']; unset($_SESSION['error']); }

                    foreach ($users as $i => $u):
                    ?>
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="p-4 text-gray-500"><?php echo $i + 1; ?></td>
                        <td class="p-4">
                            <div class="font-semibold text-gray-800"><?php echo sanitize($u['first_name'] . ' ' . $u['last_name']); ?></div>
                        </td>
                        <td class="p-4"><?php echo sanitize($u['email']); ?></td>
                        <td class="p-4"><?php echo sanitize($u['phone'] ?? 'N/A'); ?></td>
                        <td class="p-4">
                            <span class="badge-status badge-<?php echo badgeClass($u['status']); ?>"><?php echo $u['status']; ?></span>
                        </td>
                        <td class="p-4"><?php echo formatDate($u['created_at']); ?></td>
                        <td class="p-4">
                            <div class="flex items-center space-x-2">
                                <a href="view.php?id=<?php echo $u['user_id']; ?>" class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition text-xs font-semibold"><i class="fas fa-eye mr-1"></i>View</a>
                                <a href="edit.php?id=<?php echo $u['user_id']; ?>" class="px-3 py-1.5 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition text-xs font-semibold"><i class="fas fa-edit mr-1"></i>Edit</a>
                                <a href="delete.php?id=<?php echo $u['user_id']; ?>" class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition text-xs font-semibold" onclick="var _t=this;event.preventDefault();showSystemModal('Delete Customer','Delete this customer? This cannot be undone.','error',function(){location.href=_t.href;})"><i class="fas fa-trash mr-1"></i>Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                    <tr><td colspan="7" class="p-8 text-center text-gray-400">No customers found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/admin-footer.php'; ?>
