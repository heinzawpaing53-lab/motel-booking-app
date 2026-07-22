<?php
require_once '../../config/db.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}
define('PAGE_TITLE', 'Customers');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo PAGE_TITLE . ' - ' . SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
</head>
<body class="admin-layout font-[Inter] flex h-screen w-screen overflow-hidden bg-slate-100">
<?php include '../../includes/sidebar.php'; ?>
<div class="flex-1 flex flex-col h-full overflow-hidden">
<?php include '../../includes/admin-topbar.php'; ?>
<main class="flex-1 overflow-y-auto bg-slate-50">
<div class="p-6">
    <?php if (isset($messages['success'])): ?>
    <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $messages['success']; ?></div>
    <?php endif; ?>
    <?php if (isset($messages['error'])): ?>
    <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $messages['error']; ?></div>
    <?php endif; ?>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Customers</h1>
            <p class="text-gray-500 text-sm">Manage registered customers</p>
        </div>
        <a href="create.php" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"><i class="fas fa-plus mr-2"></i>Add Customer</a>
    </div>

    <div class="w-full overflow-x-auto rounded-2xl border border-stone-200/80 bg-white shadow-sm">
        <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-[#2A1810] text-amber-100 border-b-2 border-amber-500/30">
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-center">#</th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left min-w-[160px]">Name</th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left min-w-[200px]">Email</th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left">Phone</th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-center min-w-[120px]">Status</th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-left">Registered</th>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider text-amber-50/90 whitespace-nowrap text-right pr-6 min-w-[240px]">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE role_id = 2 ORDER BY created_at DESC");
                    $stmt->execute();
                    $users = $stmt->fetchAll();

                    $messages = [];
                    if (isset($_SESSION['success'])) { $messages['success'] = $_SESSION['success']; unset($_SESSION['success']); }
                    if (isset($_SESSION['error'])) { $messages['error'] = $_SESSION['error']; unset($_SESSION['error']); }

                    foreach ($users as $i => $u):
                    ?>
                    <tr class="hover:bg-amber-50/30 transition-colors border-b border-stone-100 last:border-none">
                        <td class="px-4 py-4 text-center text-gray-500 whitespace-nowrap"><?php echo $i + 1; ?></td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="font-semibold text-gray-800"><?php echo sanitize($u['first_name'] . ' ' . $u['last_name']); ?></div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap"><?php echo sanitize($u['email']); ?></td>
                        <td class="px-4 py-4 whitespace-nowrap"><?php echo sanitize($u['phone'] ?? 'N/A'); ?></td>
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            <span class="whitespace-nowrap inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide <?php
                                echo match($u['status']) {
                                    'Active' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                                    'Inactive' => 'bg-slate-100 text-slate-700 border border-slate-200',
                                    'Suspended' => 'bg-rose-100 text-rose-800 border border-rose-200',
                                    'Banned' => 'bg-rose-100 text-rose-800 border border-rose-200',
                                    default => 'bg-amber-100 text-amber-800 border border-amber-200',
                                };
                            ?>"><?php echo $u['status']; ?></span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap"><?php echo formatDate($u['created_at']); ?></td>
                        <td class="px-4 py-4 text-right pr-6 whitespace-nowrap">
                            <div class="inline-flex items-center justify-end gap-2">
                                <a href="view.php?id=<?php echo $u['user_id']; ?>" class="w-[72px] h-8 inline-flex items-center justify-center gap-1.5 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300/60 transition-all shadow-sm shrink-0"><i class="fas fa-eye"></i>View</a>
                                <a href="edit.php?id=<?php echo $u['user_id']; ?>" class="w-[68px] h-8 inline-flex items-center justify-center gap-1.5 text-xs font-semibold rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 transition-all shadow-sm shrink-0"><i class="fas fa-edit"></i>Edit</a>
                                <a href="delete.php?id=<?php echo $u['user_id']; ?>" class="w-[80px] h-8 inline-flex items-center justify-center gap-1.5 text-xs font-semibold rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-all shadow-sm shrink-0" onclick="var _t=this;event.preventDefault();showSystemModal('Delete Customer','Delete this customer? This cannot be undone.','error',function(){location.href=_t.href;})"><i class="fas fa-trash"></i>Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                    <tr><td colspan="7" class="p-8 text-center text-gray-400">No customers found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
    </div>
</div>

<?php include '../../includes/admin-footer.php'; ?>
