<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}
define('PAGE_TITLE', 'Floors');
include '../header.php';

$floors = $pdo->query("SELECT * FROM floors ORDER BY sort_order, floor_name")->fetchAll();

$messages = [];
if (isset($_SESSION['success'])) { $messages['success'] = $_SESSION['success']; unset($_SESSION['success']); }
if (isset($_SESSION['error'])) { $messages['error'] = $_SESSION['error']; unset($_SESSION['error']); }
?>

<div class="ml-64 min-h-screen">
    <div class="p-6">
        <?php if (isset($messages['success'])): ?>
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $messages['success']; ?></div>
        <?php endif; ?>
        <?php if (isset($messages['error'])): ?>
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $messages['error']; ?></div>
        <?php endif; ?>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Floors</h1>
            <a href="create.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm"><i class="fas fa-plus mr-2"></i>Add Floor</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-gray-500 border-b">
                            <th class="p-4 font-semibold">Floor Name</th>
                            <th class="p-4 font-semibold">Description</th>
                            <th class="p-4 font-semibold">Sort Order</th>
                            <th class="p-4 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($floors as $floor): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-4 font-medium"><?php echo sanitize($floor['floor_name']); ?></td>
                            <td class="p-4 text-gray-500"><?php echo sanitize($floor['description']); ?></td>
                            <td class="p-4"><?php echo $floor['sort_order']; ?></td>
                            <td class="p-4">
                                <div class="flex items-center space-x-2">
                                    <a href="edit.php?id=<?php echo $floor['floor_id']; ?>" class="text-blue-600 hover:text-blue-800 bg-blue-50 px-3 py-1.5 rounded-lg text-xs font-medium"><i class="fas fa-edit mr-1"></i>Edit</a>
                                    <a href="delete.php?id=<?php echo $floor['floor_id']; ?>" class="text-red-600 hover:text-red-800 bg-red-50 px-3 py-1.5 rounded-lg text-xs font-medium" onclick="var _t=this;event.preventDefault();showSystemModal('Delete Floor','Are you sure you want to delete this floor?','error',function(){location.href=_t.href;})"><i class="fas fa-trash mr-1"></i>Delete</a>
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
        </div>
    </div>
</div>
<?php include '../../includes/admin-footer.php'; ?>
