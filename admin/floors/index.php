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

        <div class="bg-white border border-stone-200 rounded-2xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#2A1810] text-amber-100 border-b-2 border-amber-500/30 shadow-sm">
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 text-left">Floor Name</th>
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 text-left">Description</th>
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 text-right">Sort Order</th>
                            <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 text-center min-w-[200px]">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($floors as $floor): ?>
                        <tr class="hover:bg-amber-50/30 transition-colors border-b border-stone-100 last:border-none">
                            <td class="p-4 font-medium"><?php echo sanitize($floor['floor_name']); ?></td>
                            <td class="p-4 text-gray-500"><?php echo sanitize($floor['description']); ?></td>
                            <td class="p-4"><?php echo $floor['sort_order']; ?></td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center justify-center gap-2">
                                    <a href="edit.php?id=<?php echo $floor['floor_id']; ?>" class="w-[68px] h-8 inline-flex items-center justify-center gap-1.5 text-xs font-semibold rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 transition-all shadow-sm shrink-0"><i class="fas fa-edit"></i>Edit</a>
                                    <a href="delete.php?id=<?php echo $floor['floor_id']; ?>" class="w-[80px] h-8 inline-flex items-center justify-center gap-1.5 text-xs font-semibold rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-all shadow-sm shrink-0" onclick="var _t=this;event.preventDefault();showSystemModal('Delete Floor','Are you sure you want to delete this floor?','error',function(){location.href=_t.href;})"><i class="fas fa-trash"></i>Delete</a>
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
<?php include '../../includes/admin-footer.php'; ?>
