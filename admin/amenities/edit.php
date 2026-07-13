<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}
define('PAGE_TITLE', 'Edit Amenity');
include '../header.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM amenities WHERE amenity_id = ?");
$stmt->execute([$id]);
$amenity = $stmt->fetch();

if (!$amenity) {
    redirect('admin/amenities/index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $amenity_name = sanitize($_POST['amenity_name'] ?? '');
        $icon = sanitize($_POST['icon'] ?? '');

        if (empty($amenity_name)) {
            $error = 'Amenity name is required.';
        } else {
            $stmt = $pdo->prepare("UPDATE amenities SET amenity_name = ?, icon = ? WHERE amenity_id = ?");
            $stmt->execute([$amenity_name, $icon, $id]);
            logActivity($pdo, $_SESSION['user_id'], 'Update Amenity', "Updated amenity {$amenity_name}");
            $success = 'Amenity updated successfully.';
            $amenity['amenity_name'] = $amenity_name;
            $amenity['icon'] = $icon;
        }
    }
}
?>


<div class="ml-64 min-h-screen">
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Amenity</h1>
                <p class="text-gray-500 text-sm">Editing <?php echo sanitize($amenity['amenity_name']); ?></p>
            </div>
            <a href="index.php" class="text-gray-600 hover:text-gray-800 bg-white px-4 py-2 rounded-lg border text-sm"><i class="fas fa-arrow-left mr-2"></i>Back</a>
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
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Amenity Name <span class="text-red-500">*</span></label>
                    <input type="text" name="amenity_name" value="<?php echo sanitize($amenity['amenity_name']); ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm" required>
                </div>
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Font Awesome Icon Class</label>
                    <input type="text" name="icon" value="<?php echo sanitize($amenity['icon']); ?>" placeholder="fa-wifi" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm">
                    <p class="text-xs text-gray-400 mt-1">Enter the Font Awesome icon class name without the "fas" prefix.</p>
                </div>
                <div class="mt-6">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 transition text-sm font-medium"><i class="fas fa-save mr-2"></i>Update Amenity</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
