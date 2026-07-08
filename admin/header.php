<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo defined('PAGE_TITLE') ? PAGE_TITLE . ' - ' . SITE_NAME : SITE_NAME . ' Admin'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
</head>
<body class="font-[Inter] bg-gray-50">
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<!-- Top bar with user menu -->
<div class="ml-64 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between sticky top-0 z-30">
    <div>
        <span class="text-sm text-gray-500"><?php echo date('l, F d, Y'); ?></span>
    </div>
    <div class="relative group">
        <button class="flex items-center space-x-2 text-gray-700 hover:text-blue-600 transition">
            <i class="fas fa-user-circle text-xl"></i>
            <span class="text-sm font-medium"><?php echo htmlspecialchars($_SESSION['first_name'] ?? 'Admin'); ?></span>
            <i class="fas fa-chevron-down text-xs"></i>
        </button>
        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
            <a href="<?php echo SITE_URL; ?>profile.php" class="flex items-center space-x-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600"><i class="fas fa-user w-4"></i><span>Profile</span></a>
            <a href="<?php echo SITE_URL; ?>admin/settings/index.php" class="flex items-center space-x-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600"><i class="fas fa-cog w-4"></i><span>Settings</span></a>
            <hr class="my-1">
            <a href="<?php echo SITE_URL; ?>logout.php" class="flex items-center space-x-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50"><i class="fas fa-sign-out-alt w-4"></i><span>Logout</span></a>
        </div>
    </div>
</div>

