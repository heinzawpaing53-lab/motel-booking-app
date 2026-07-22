<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}
define('PAGE_TITLE', 'Edit Room');
include '../header.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE room_id = ?");
$stmt->execute([$id]);
$room = $stmt->fetch();

if (!$room) {
    redirect('admin/rooms/index.php');
}

$roomTypes = $pdo->query("SELECT * FROM room_types ORDER BY type_name")->fetchAll();
$floors = $pdo->query("SELECT * FROM floors ORDER BY sort_order, floor_name")->fetchAll();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $room_number = sanitize($_POST['room_number'] ?? '');
        $room_name = sanitize($_POST['room_name'] ?? '');
        $type_id = (int)($_POST['type_id'] ?? 0);
        $floor_id = (int)($_POST['floor_id'] ?? 0);
        $status = sanitize($_POST['status'] ?? 'Available');
        $description = sanitize($_POST['description'] ?? '');

        if (empty($room_number)) {
            $error = 'Room number is required.';
        } elseif ($type_id <= 0) {
            $error = 'Please select a room type.';
        } elseif ($floor_id <= 0) {
            $error = 'Please select a floor.';
        } else {
            $check = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE room_number = ? AND room_id != ?");
            $check->execute([$room_number, $id]);
            if ($check->fetchColumn() > 0) {
                $error = 'Room number already exists.';
            } else {
                $stmt = $pdo->prepare("UPDATE rooms SET room_number = ?, room_name = ?, type_id = ?, floor_id = ?, status = ?, description = ? WHERE room_id = ?");
                $stmt->execute([$room_number, $room_name, $type_id, $floor_id, $status, $description, $id]);
                logActivity($pdo, $_SESSION['user_id'], 'Update Room', "Updated room {$room_number}");
                $success = 'Room updated successfully.';
                $room['room_number'] = $room_number;
                $room['room_name'] = $room_name;
                $room['type_id'] = $type_id;
                $room['floor_id'] = $floor_id;
                $room['status'] = $status;
                $room['description'] = $description;
            }
        }
    }
}
?>


<div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Room</h1>
                <p class="text-gray-500 text-sm">Editing room <?php echo sanitize($room['room_number']); ?></p>
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Room Number <span class="text-red-500">*</span></label>
                        <input type="text" name="room_number" value="<?php echo sanitize($room['room_number']); ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Room Name</label>
                        <input type="text" name="room_name" value="<?php echo sanitize($room['room_name']); ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Room Type <span class="text-red-500">*</span></label>
                        <select name="type_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm" required>
                            <option value="">Select Type</option>
                            <?php foreach ($roomTypes as $type): ?>
                            <option value="<?php echo $type['type_id']; ?>" <?php echo $type['type_id'] == $room['type_id'] ? 'selected' : ''; ?>><?php echo sanitize($type['type_name']); ?> (<?php echo formatCurrency($type['price_per_night']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Floor <span class="text-red-500">*</span></label>
                        <select name="floor_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm" required>
                            <option value="">Select Floor</option>
                            <?php foreach ($floors as $floor): ?>
                            <option value="<?php echo $floor['floor_id']; ?>" <?php echo $floor['floor_id'] == $room['floor_id'] ? 'selected' : ''; ?>><?php echo sanitize($floor['floor_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm">
                            <option value="Available" <?php echo $room['status'] == 'Available' ? 'selected' : ''; ?>>Available</option>
                            <option value="Occupied" <?php echo $room['status'] == 'Occupied' ? 'selected' : ''; ?>>Occupied</option>
                            <option value="Reserved" <?php echo $room['status'] == 'Reserved' ? 'selected' : ''; ?>>Reserved</option>
                            <option value="Maintenance" <?php echo $room['status'] == 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                            <option value="Cleaning" <?php echo $room['status'] == 'Cleaning' ? 'selected' : ''; ?>>Cleaning</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="4" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm"><?php echo sanitize($room['description']); ?></textarea>
                </div>
                <div class="mt-6">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 transition text-sm font-medium"><i class="fas fa-save mr-2"></i>Update Room</button>
                </div>
            </form>
        </div>
    </div>
<?php include '../../includes/admin-footer.php'; ?>
