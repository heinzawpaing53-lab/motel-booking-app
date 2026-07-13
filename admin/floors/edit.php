<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}
define('PAGE_TITLE', 'Edit Floor');
include '../header.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM floors WHERE floor_id = ?");
$stmt->execute([$id]);
$floor = $stmt->fetch();

if (!$floor) {
    redirect('admin/rooms/index.php?tab=floors');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $floor_name = sanitize($_POST['floor_name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $sort_order = (int)($_POST['sort_order'] ?? 0);

        if (empty($floor_name)) {
            $error = 'Floor name is required.';
        } else {
            $stmt = $pdo->prepare("UPDATE floors SET floor_name = ?, description = ?, sort_order = ? WHERE floor_id = ?");
            $stmt->execute([$floor_name, $description, $sort_order, $id]);
            logActivity($pdo, $_SESSION['user_id'], 'Update Floor', "Updated floor {$floor_name}");
            $success = 'Floor updated successfully.';
            $floor['floor_name'] = $floor_name;
            $floor['description'] = $description;
            $floor['sort_order'] = $sort_order;
        }
    }
}
?>


<div class="ml-64 min-h-screen">
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Floor</h1>
                <p class="text-gray-500 text-sm">Editing <?php echo sanitize($floor['floor_name']); ?></p>
            </div>
            <a href="../rooms/index.php?tab=floors" class="text-gray-600 hover:text-gray-800 bg-white px-4 py-2 rounded-lg border text-sm"><i class="fas fa-arrow-left mr-2"></i>Back</a>
        </div>

        <?php if ($error): ?>
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-sm p-6 max-w-2xl">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Floor Name <span class="text-red-500">*</span></label>
                        <input type="text" name="floor_name" value="<?php echo sanitize($floor['floor_name']); ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                        <input type="number" name="sort_order" value="<?php echo $floor['sort_order']; ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm">
                    </div>
                </div>
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="4" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm"><?php echo sanitize($floor['description']); ?></textarea>
                </div>
                <div class="mt-6">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 transition text-sm font-medium"><i class="fas fa-save mr-2"></i>Update Floor</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
