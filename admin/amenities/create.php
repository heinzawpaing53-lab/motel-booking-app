<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}
define('PAGE_TITLE', 'Add Amenity');
include '../header.php';

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
            $stmt = $pdo->prepare("INSERT INTO amenities (amenity_name, icon) VALUES (?, ?)");
            $stmt->execute([$amenity_name, $icon]);
            logActivity($pdo, $_SESSION['user_id'], 'Create Amenity', "Created amenity {$amenity_name}");
            $success = 'Amenity created successfully.';
            echo '<script>setTimeout(function(){ window.location.href = "index.php"; }, 1500);</script>';
        }
    }
}
?>


<div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Add Amenity</h1>
                <p class="text-gray-500 text-sm">Create a new amenity</p>
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
                    <input type="text" name="amenity_name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm" required>
                </div>
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Font Awesome Icon Class <span class="text-gray-400 text-xs">(e.g., fa-wifi, fa-parking, fa-swimming-pool)</span></label>
                    <input type="text" name="icon" placeholder="fa-wifi" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm">
                    <p class="text-xs text-gray-400 mt-1">Enter the Font Awesome icon class name without the "fas" prefix.</p>
                </div>
                <div class="mt-6">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 transition text-sm font-medium"><i class="fas fa-save mr-2"></i>Create Amenity</button>
                </div>
            </form>
        </div>
    </div>
<?php include '../../includes/admin-footer.php'; ?>
