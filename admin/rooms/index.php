<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}
define('PAGE_TITLE', 'Manage Rooms');
include '../header.php';

$activeTab = $_GET['tab'] ?? 'rooms';
if (!in_array($activeTab, ['rooms', 'types', 'floors'])) {
    $activeTab = 'rooms';
}

$messages = [];
if (isset($_SESSION['success'])) { $messages['success'] = $_SESSION['success']; unset($_SESSION['success']); }
if (isset($_SESSION['error'])) { $messages['error'] = $_SESSION['error']; unset($_SESSION['error']); }

if ($activeTab === 'rooms') {
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
} elseif ($activeTab === 'types') {
    $roomTypes = $pdo->query("SELECT * FROM room_types ORDER BY type_name")->fetchAll();
} elseif ($activeTab === 'floors') {
    $floors = $pdo->query("SELECT * FROM floors ORDER BY sort_order, floor_name")->fetchAll();
}
?>

<div class="p-6">
        <?php if (isset($messages['success'])): ?>
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $messages['success']; ?></div>
        <?php endif; ?>
        <?php if (isset($messages['error'])): ?>
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $messages['error']; ?></div>
        <?php endif; ?>

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Rooms Management</h1>
        </div>

        <div class="flex items-center gap-2 mb-6">
            <a href="?tab=rooms" class="px-5 py-2.5 rounded-lg text-sm font-semibold transition <?php echo $activeTab === 'rooms' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'; ?>"><i class="fas fa-bed mr-2"></i>Manage Rooms</a>
            <a href="?tab=types" class="px-5 py-2.5 rounded-lg text-sm font-semibold transition <?php echo $activeTab === 'types' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'; ?>"><i class="fas fa-tags mr-2"></i>Room Types</a>
            <a href="?tab=floors" class="px-5 py-2.5 rounded-lg text-sm font-semibold transition <?php echo $activeTab === 'floors' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'; ?>"><i class="fas fa-layer-group mr-2"></i>Floors</a>
        </div>

        <?php if ($activeTab === 'rooms'): ?>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold text-gray-800">Manage Rooms</h2>
            <a href="create.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm"><i class="fas fa-plus mr-2"></i>Add Room</a>
        </div>

        <div class="w-full overflow-x-auto rounded-2xl border border-stone-200/80 bg-white shadow-sm">
                <table class="w-full text-left border-collapse min-w-[900px]">
                    <thead>
                        <tr class="bg-[#2A1810] text-amber-100 border-b-2 border-amber-500/30">
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-center">Room </th>
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left min-w-[120px]">Room Name</th>
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left">Type</th>
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left">Floor</th>
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-center min-w-[120px]">Status</th>
                            <th class="px-14 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-right pr-6">Price/Night</th>
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-center min-w-[200px]">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rooms as $room): ?>
                        <tr class="hover:bg-amber-50/30 transition-colors border-b border-stone-100 last:border-none">
                            <td class="px-4 py-4 text-center font-medium whitespace-nowrap"><?php echo sanitize($room['room_number']); ?></td>
                            <td class="px-4 py-4 whitespace-nowrap"><?php echo sanitize($room['room_name']); ?></td>
                            <td class="px-4 py-4 whitespace-nowrap"><?php echo sanitize($room['type_name']); ?></td>
                            <td class="px-4 py-4 whitespace-nowrap"><?php echo sanitize($room['floor_name']); ?></td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <span class="whitespace-nowrap inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide <?php
                                    echo match($room['status']) {
                                        'Available' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                                        'Occupied' => 'bg-blue-100 text-blue-800 border border-blue-200',
                                        'Reserved' => 'bg-indigo-100 text-indigo-800 border border-indigo-200',
                                        'Maintenance' => 'bg-amber-100 text-amber-800 border border-amber-200',
                                        default => 'bg-slate-100 text-slate-700 border border-slate-200',
                                    };
                                ?>"><?php echo $room['status']; ?></span>
                            </td>
                            <td class="px-4 py-4 text-sm font-semibold text-stone-900 text-right pr-6 whitespace-nowrap"><?php echo formatCurrency($room['price_per_night']); ?></td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center justify-center gap-2">
                                    <a href="edit.php?id=<?php echo $room['room_id']; ?>" class="w-[68px] h-8 inline-flex items-center justify-center gap-1.5 text-xs font-semibold rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 transition-all shadow-sm shrink-0"><i class="fas fa-edit"></i>Edit</a>
                                    <a href="delete.php?id=<?php echo $room['room_id']; ?>" class="w-[80px] h-8 inline-flex items-center justify-center gap-1.5 text-xs font-semibold rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-all shadow-sm shrink-0" onclick="var _t=this;event.preventDefault();showSystemModal('Delete Room','Are you sure you want to delete this room?','error',function(){location.href=_t.href;})"><i class="fas fa-trash"></i>Delete</a>
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

        <?php elseif ($activeTab === 'types'): ?>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold text-gray-800">Room Types</h2>
            <a href="../room-types/create.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm"><i class="fas fa-plus mr-2"></i>Add Room Type</a>
        </div>

        <div class="w-full overflow-x-auto rounded-2xl border border-stone-200/80 bg-white shadow-sm">
                <table class="w-full text-left border-collapse min-w-[800px]">
                    <thead>
                        <tr class="bg-[#2A1810] text-amber-100 border-b-2 border-amber-500/30">
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left min-w-[140px]">Name</th>
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-right pr-6">Price/Night</th>
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-center">Max Capacity</th>
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left">Bed Type</th>
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left">Room Size</th>
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-center min-w-[200px]">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roomTypes as $type): ?>
                        <tr class="hover:bg-amber-50/30 transition-colors border-b border-stone-100 last:border-none">
                            <td class="px-4 py-4 font-medium whitespace-nowrap"><?php echo sanitize($type['type_name']); ?></td>
                            <td class="px-4 py-4 text-sm font-semibold text-stone-900 text-right pr-6 whitespace-nowrap"><?php echo formatCurrency($type['price_per_night']); ?></td>
                            <td class="px-4 py-4 text-center whitespace-nowrap"><?php echo $type['max_capacity']; ?></td>
                            <td class="px-4 py-4 whitespace-nowrap"><?php echo sanitize($type['bed_type']); ?></td>
                            <td class="px-4 py-4 whitespace-nowrap"><?php echo sanitize($type['room_size']); ?></td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center justify-center gap-2">
                                    <a href="../room-types/edit.php?id=<?php echo $type['type_id']; ?>" class="w-[68px] h-8 inline-flex items-center justify-center gap-1.5 text-xs font-semibold rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 transition-all shadow-sm shrink-0"><i class="fas fa-edit"></i>Edit</a>
                                    <a href="../room-types/delete.php?id=<?php echo $type['type_id']; ?>" class="w-[80px] h-8 inline-flex items-center justify-center gap-1.5 text-xs font-semibold rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-all shadow-sm shrink-0" onclick="var _t=this;event.preventDefault();showSystemModal('Delete Room Type','Are you sure you want to delete this room type?','error',function(){location.href=_t.href;})"><i class="fas fa-trash"></i>Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($roomTypes)): ?>
                        <tr><td colspan="6" class="p-6 text-center text-gray-400">No room types found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
        </div>

        <?php elseif ($activeTab === 'floors'): ?>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold text-gray-800">Floors</h2>
            <a href="../floors/create.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm"><i class="fas fa-plus mr-2"></i>Add Floor</a>
        </div>

        <div class="w-full overflow-x-auto rounded-2xl border border-stone-200/80 bg-white shadow-sm">
                <table class="w-full text-left border-collapse min-w-[600px]">
                    <thead>
                        <tr class="bg-[#2A1810] text-amber-100 border-b-2 border-amber-500/30">
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left min-w-[120px]">Floor Name</th>
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left">Description</th>
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-right pr-6">Sort Order</th>
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-center min-w-[200px]">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($floors as $floor): ?>
                        <tr class="hover:bg-amber-50/30 transition-colors border-b border-stone-100 last:border-none">
                            <td class="px-4 py-4 font-medium whitespace-nowrap"><?php echo sanitize($floor['floor_name']); ?></td>
                            <td class="px-4 py-4 text-gray-500 whitespace-nowrap"><?php echo sanitize($floor['description']); ?></td>
                            <td class="px-4 py-4 text-right pr-6 whitespace-nowrap"><?php echo $floor['sort_order']; ?></td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center justify-center gap-2">
                                    <a href="../floors/edit.php?id=<?php echo $floor['floor_id']; ?>" class="w-[68px] h-8 inline-flex items-center justify-center gap-1.5 text-xs font-semibold rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 transition-all shadow-sm shrink-0"><i class="fas fa-edit"></i>Edit</a>
                                    <a href="../floors/delete.php?id=<?php echo $floor['floor_id']; ?>" class="w-[80px] h-8 inline-flex items-center justify-center gap-1.5 text-xs font-semibold rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-all shadow-sm shrink-0" onclick="var _t=this;event.preventDefault();showSystemModal('Delete Floor','Are you sure you want to delete this floor?','error',function(){location.href=_t.href;})"><i class="fas fa-trash"></i>Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($floors)): ?>
                        <tr><td colspan="4" class="p-6 text-center text-gray-400">No floors found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
        </div>
        <?php endif; ?>

    </div>
<?php include '../../includes/admin-footer.php'; ?>
