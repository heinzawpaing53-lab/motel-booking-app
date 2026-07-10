<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}
define('PAGE_TITLE', 'Manage Rooms');
include '../header.php';

$statusFilter = $_GET['status'] ?? '';
$sql = "SELECT r.*, rt.type_name, rt.price_per_night, f.floor_name
        FROM rooms r
        JOIN room_types rt ON r.type_id = rt.type_id
        JOIN floors f ON r.floor_id = f.floor_id";
$params = [];
if (in_array($statusFilter, ['Available', 'Occupied', 'Reserved', 'Maintenance'])) {
    $sql .= " WHERE r.status = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY r.room_number";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rooms = $stmt->fetchAll();

$messages = [];
if (isset($_SESSION['success'])) { $messages['success'] = $_SESSION['success']; unset($_SESSION['success']); }
if (isset($_SESSION['error'])) { $messages['error'] = $_SESSION['error']; unset($_SESSION['error']); }
?>

<div class="ml-64 min-h-screen">
    <div class="p-6">
        <?php if (isset($messages['success'])): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg text-sm"><?php echo $messages['success']; ?></div>
        <?php endif; ?>
        <?php if (isset($messages['error'])): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg text-sm"><?php echo $messages['error']; ?></div>
        <?php endif; ?>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Manage Rooms</h1>
            <a href="create.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm"><i class="fas fa-plus mr-2"></i>Add Room</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-gray-500 border-b">
                            <th class="p-4 font-semibold">Room #</th>
                            <th class="p-4 font-semibold">Room Name</th>
                            <th class="p-4 font-semibold">Type</th>
                            <th class="p-4 font-semibold">Floor</th>
                            <th class="p-4 font-semibold">Status</th>
                            <th class="p-4 font-semibold">Price/Night</th>
                            <th class="p-4 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rooms as $room): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-4 font-medium"><?php echo sanitize($room['room_number']); ?></td>
                            <td class="p-4"><?php echo sanitize($room['room_name']); ?></td>
                            <td class="p-4"><?php echo sanitize($room['type_name']); ?></td>
                            <td class="p-4"><?php echo sanitize($room['floor_name']); ?></td>
                            <td class="p-4"><span class="badge-status badge-<?php echo badgeClass($room['status']); ?>"><?php echo $room['status']; ?></span></td>
                            <td class="p-4"><?php echo formatCurrency($room['price_per_night']); ?></td>
                            <td class="p-4">
                                <div class="flex items-center space-x-2">
                                    <a href="edit.php?id=<?php echo $room['room_id']; ?>" class="text-blue-600 hover:text-blue-800 bg-blue-50 px-3 py-1.5 rounded-lg text-xs font-medium"><i class="fas fa-edit mr-1"></i>Edit</a>
                                    <a href="delete.php?id=<?php echo $room['room_id']; ?>" class="text-red-600 hover:text-red-800 bg-red-50 px-3 py-1.5 rounded-lg text-xs font-medium" onclick="var _t=this;event.preventDefault();showSystemModal('Delete Room','Are you sure you want to delete this room?','error',function(){location.href=_t.href;})"><i class="fas fa-trash mr-1"></i>Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($rooms)): ?>
                        <tr><td colspan="7" class="p-6 text-center text-gray-400">No rooms found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include '../../includes/admin-footer.php'; ?>
