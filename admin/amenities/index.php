<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}
define('PAGE_TITLE', 'Amenities');
include '../header.php';

$activeTab = $_GET['tab'] ?? 'amenities';
if (!in_array($activeTab, ['amenities', 'requests'])) {
    $activeTab = 'amenities';
}

$messages = [];
if (isset($_SESSION['success'])) { $messages['success'] = $_SESSION['success']; unset($_SESSION['success']); }
if (isset($_SESSION['error'])) { $messages['error'] = $_SESSION['error']; unset($_SESSION['error']); }

if ($activeTab === 'amenities') {
    $amenities = $pdo->query("SELECT * FROM amenities ORDER BY amenity_name")->fetchAll();
} elseif ($activeTab === 'requests') {
    $requests = $pdo->query("SELECT * FROM special_requests ORDER BY request_name")->fetchAll();
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
            <h1 class="text-2xl font-bold text-gray-800">Amenities Management</h1>
        </div>

        <div class="flex items-center gap-2 mb-6">
            <a href="?tab=amenities" class="px-5 py-2.5 rounded-lg text-sm font-semibold transition <?php echo $activeTab === 'amenities' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'; ?>"><i class="fas fa-concierge-bell mr-2"></i>Manage Amenities</a>
            <a href="?tab=requests" class="px-5 py-2.5 rounded-lg text-sm font-semibold transition <?php echo $activeTab === 'requests' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'; ?>"><i class="fas fa-clipboard-list mr-2"></i>Special Requests</a>
        </div>

        <?php if ($activeTab === 'amenities'): ?>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold text-gray-800">Manage Amenities</h2>
            <a href="create.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm"><i class="fas fa-plus mr-2"></i>Add Amenity</a>
        </div>

        <div class="bg-white border border-stone-200 rounded-2xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#2A1810] text-amber-100 border-b-2 border-amber-500/30 shadow-sm">
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 text-left">Icon</th>
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 text-left">Amenity Name</th>
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 text-right pr-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($amenities as $amenity): ?>
                        <tr class="hover:bg-amber-50/30 transition-colors border-b border-stone-100 last:border-none">
                            <td class="p-4"><i class="fas <?php echo sanitize($amenity['icon'] ?? 'fa-tag'); ?> text-blue-600 text-lg w-8"></i></td>
                            <td class="p-4 font-medium"><?php echo sanitize($amenity['amenity_name']); ?></td>
                            <td class="p-4">
                                <div class="flex items-center space-x-2">
                                    <a href="edit.php?id=<?php echo $amenity['amenity_id']; ?>" class="text-blue-600 hover:text-blue-800 bg-blue-50 px-3 py-1.5 rounded-lg text-xs font-medium"><i class="fas fa-edit mr-1"></i>Edit</a>
                                    <a href="delete.php?id=<?php echo $amenity['amenity_id']; ?>" class="text-red-600 hover:text-red-800 bg-red-50 px-3 py-1.5 rounded-lg text-xs font-medium" onclick="var _t=this;event.preventDefault();showSystemModal('Delete Amenity','Are you sure you want to delete this amenity?','error',function(){location.href=_t.href;})"><i class="fas fa-trash mr-1"></i>Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($amenities)): ?>
                        <tr><td colspan="3" class="p-6 text-center text-gray-400">No amenities found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php elseif ($activeTab === 'requests'): ?>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold text-gray-800">Special Requests</h2>
            <a href="../requests/create.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm"><i class="fas fa-plus mr-2"></i>Add Request</a>
        </div>

        <div class="bg-white border border-stone-200 rounded-2xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#2A1810] text-amber-100 border-b-2 border-amber-500/30 shadow-sm">
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 text-left">#</th>
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 text-left">Request Name</th>
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 text-left">Description</th>
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 text-left">Status</th>
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 text-right pr-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $i => $r): ?>
                        <tr class="hover:bg-amber-50/30 transition-colors border-b border-stone-100 last:border-none">
                            <td class="p-4 text-gray-500"><?php echo $i + 1; ?></td>
                            <td class="p-4 font-semibold text-gray-800"><?php echo sanitize($r['request_name']); ?></td>
                            <td class="p-4 text-gray-600 max-w-xs truncate"><?php echo sanitize($r['description'] ?? 'N/A'); ?></td>
                            <td class="p-4">
                                <span class="badge-status <?php echo $r['active'] ? 'badge-approved' : 'badge-cancelled'; ?>"><?php echo $r['active'] ? 'Active' : 'Inactive'; ?></span>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center space-x-2">
                                    <a href="../requests/edit.php?id=<?php echo $r['request_id']; ?>" class="text-blue-600 hover:text-blue-800 bg-blue-50 px-3 py-1.5 rounded-lg text-xs font-medium"><i class="fas fa-edit mr-1"></i>Edit</a>
                                    <a href="../requests/delete.php?id=<?php echo $r['request_id']; ?>" class="text-red-600 hover:text-red-800 bg-red-50 px-3 py-1.5 rounded-lg text-xs font-medium" onclick="var _t=this;event.preventDefault();showSystemModal('Delete Request','Delete this special request?','error',function(){location.href=_t.href;})"><i class="fas fa-trash mr-1"></i>Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($requests)): ?>
                        <tr><td colspan="5" class="p-6 text-center text-gray-400">No special requests found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

    </div>
<?php include '../../includes/admin-footer.php'; ?>
