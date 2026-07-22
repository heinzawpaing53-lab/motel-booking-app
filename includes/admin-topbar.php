<!-- Top bar with user menu -->
<div class="shrink-0 bg-white border-b border-slate-200 px-6 py-3 flex items-center justify-between z-40">
    <div>
        <span class="text-sm text-slate-500"><?php echo date('l, F d, Y'); ?></span>
    </div>
    <div class="relative group">
        <button class="flex items-center space-x-3 text-slate-700 hover:text-amber-600 transition">
            <?php
            $imgPath = '';
            $hasImage = false;
            if (!empty($_SESSION['profile_image'])) {
                $absPath = dirname(__DIR__) . '/uploads/' . $_SESSION['profile_image'];
                if (file_exists($absPath)) {
                    $imgPath = SITE_URL . 'uploads/' . $_SESSION['profile_image'];
                    $hasImage = true;
                }
            }
            ?>
            <?php if ($hasImage): ?>
                <img src="<?php echo $imgPath; ?>" alt="Profile" class="w-8 h-8 rounded-full object-cover border-2 border-amber-200">
            <?php else: ?>
                <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center border-2 border-amber-200">
                    <i class="fa-solid fa-user-shield text-amber-600 text-sm"></i>
                </div>
            <?php endif; ?>
            <div class="text-left">
                <span class="text-sm font-medium block leading-tight text-slate-700"><?php echo htmlspecialchars($_SESSION['first_name'] ?? 'Admin'); ?></span>
                <span class="text-xs text-stone-400 font-normal"><?php echo htmlspecialchars($_SESSION['role_name'] ?? 'Administrator'); ?></span>
            </div>
            <i class="fas fa-chevron-down text-xs"></i>
        </button>
        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
            <a href="<?php echo SITE_URL; ?>admin/profile.php" class="flex items-center space-x-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-amber-50 hover:text-amber-600"><i class="fas fa-user w-4"></i><span>Profile</span></a>
            <a href="<?php echo SITE_URL; ?>admin/settings/index.php" class="flex items-center space-x-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-amber-50 hover:text-amber-600"><i class="fas fa-cog w-4"></i><span>Settings</span></a>
            <hr class="my-1 border-slate-100">
            <a href="<?php echo SITE_URL; ?>logout.php" class="flex items-center space-x-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50"><i class="fas fa-sign-out-alt w-4"></i><span>Logout</span></a>
        </div>
    </div>
</div>
