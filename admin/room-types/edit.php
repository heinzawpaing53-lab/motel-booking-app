<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}
define('PAGE_TITLE', 'Edit Room Type');
include '../header.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM room_types WHERE type_id = ?");
$stmt->execute([$id]);
$type = $stmt->fetch();

if (!$type) {
    redirect('admin/rooms/index.php?tab=types');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $type_name = sanitize($_POST['type_name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $price_per_night = (float)($_POST['price_per_night'] ?? 0);
        $max_capacity = (int)($_POST['max_capacity'] ?? 1);
        $bed_type = sanitize($_POST['bed_type'] ?? '');
        $room_size = sanitize($_POST['room_size'] ?? '');

        if (empty($type_name)) {
            $error = 'Type name is required.';
        } elseif ($price_per_night <= 0) {
            $error = 'Price per night must be greater than 0.';
        } elseif ($max_capacity <= 0) {
            $error = 'Max capacity must be at least 1.';
        } else {
            $stmt = $pdo->prepare("UPDATE room_types SET type_name = ?, description = ?, price_per_night = ?, max_capacity = ?, bed_type = ?, room_size = ? WHERE type_id = ?");
            $stmt->execute([$type_name, $description, $price_per_night, $max_capacity, $bed_type, $room_size, $id]);
            logActivity($pdo, $_SESSION['user_id'], 'Update Room Type', "Updated room type {$type_name}");
            $success = 'Room type updated successfully.';
            $type['type_name'] = $type_name;
            $type['description'] = $description;
            $type['price_per_night'] = $price_per_night;
            $type['max_capacity'] = $max_capacity;
            $type['bed_type'] = $bed_type;
            $type['room_size'] = $room_size;
        }
    }
}
?>


<div class="ml-64 min-h-screen">
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Room Type</h1>
                <p class="text-gray-500 text-sm">Editing <?php echo sanitize($type['type_name']); ?></p>
            </div>
            <a href="../rooms/index.php?tab=types" class="text-gray-600 hover:text-gray-800 bg-white px-4 py-2 rounded-lg border text-sm"><i class="fas fa-arrow-left mr-2"></i>Back</a>
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type Name <span class="text-red-500">*</span></label>
                        <input type="text" name="type_name" value="<?php echo sanitize($type['type_name']); ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Price Per Night <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" name="price_per_night" value="<?php echo $type['price_per_night']; ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Max Capacity <span class="text-red-500">*</span></label>
                        <input type="number" min="1" name="max_capacity" value="<?php echo $type['max_capacity']; ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bed Type</label>
                        <input type="text" name="bed_type" value="<?php echo sanitize($type['bed_type']); ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Room Size</label>
                        <input type="text" name="room_size" value="<?php echo sanitize($type['room_size']); ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm">
                    </div>
                </div>
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="4" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm"><?php echo sanitize($type['description']); ?></textarea>
                </div>
                <div class="mt-6">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 transition text-sm font-medium"><i class="fas fa-save mr-2"></i>Update Room Type</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
