<aside class="w-64 h-full bg-[#2A1810] flex flex-col shrink-0" id="adminSidebar">
    <div class="p-4 border-b border-[#1a0f0a] flex-shrink-0 bg-[#1a0f0a]">
        <div class="flex items-center justify-between">
            <a href="<?php echo SITE_URL; ?>admin/dashboard.php" class="flex items-center space-x-2">
                <i class="fas fa-hotel text-amber-500 text-xl"></i>
                <span class="font-bold text-lg text-white">Luxury Motel</span>
            </a>
            <button id="sidebarToggle" class="text-slate-400 hover:text-white lg:hidden">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <nav class="p-4 space-y-1 flex-1 overflow-y-auto scrollbar-hide">
        <a href="<?php echo SITE_URL; ?>admin/dashboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition text-sm <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-amber-600 text-white font-semibold' : 'text-slate-300 hover:bg-[#1a0f0a] hover:text-white'; ?>">
            <i class="fas fa-tachometer-alt w-5"></i><span>Dashboard</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/rooms/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition text-sm <?php echo (strpos($_SERVER['PHP_SELF'], '/rooms/') !== false || strpos($_SERVER['PHP_SELF'], '/room-types/') !== false || strpos($_SERVER['PHP_SELF'], '/floors/') !== false) ? 'bg-amber-600 text-white font-semibold' : 'text-slate-300 hover:bg-[#1a0f0a] hover:text-white'; ?>">
            <i class="fas fa-bed w-5"></i><span>Rooms</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/amenities/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition text-sm <?php echo (strpos($_SERVER['PHP_SELF'], '/amenities/') !== false || strpos($_SERVER['PHP_SELF'], '/requests/') !== false) ? 'bg-amber-600 text-white font-semibold' : 'text-slate-300 hover:bg-[#1a0f0a] hover:text-white'; ?>">
            <i class="fas fa-concierge-bell w-5"></i><span>Amenities</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/bookings/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition text-sm <?php echo strpos($_SERVER['PHP_SELF'], 'bookings') !== false && basename($_SERVER['PHP_SELF']) !== 'payments.php' ? 'bg-amber-600 text-white font-semibold' : 'text-slate-300 hover:bg-[#1a0f0a] hover:text-white'; ?>">
            <i class="fas fa-calendar-check w-5"></i><span>Bookings</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/bookings/payments.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition text-sm <?php echo strpos($_SERVER['PHP_SELF'], 'payments') !== false ? 'bg-amber-600 text-white font-semibold' : 'text-slate-300 hover:bg-[#1a0f0a] hover:text-white'; ?>">
            <i class="fas fa-credit-card w-5"></i><span>Payments</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/users/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition text-sm <?php echo strpos($_SERVER['PHP_SELF'], 'users') !== false ? 'bg-amber-600 text-white font-semibold' : 'text-slate-300 hover:bg-[#1a0f0a] hover:text-white'; ?>">
            <i class="fas fa-users w-5"></i><span>Customers</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/reports/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition text-sm <?php echo strpos($_SERVER['PHP_SELF'], 'reports') !== false ? 'bg-amber-600 text-white font-semibold' : 'text-slate-300 hover:bg-[#1a0f0a] hover:text-white'; ?>">
            <i class="fas fa-chart-bar w-5"></i><span>Reports</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/settings/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition text-sm <?php echo strpos($_SERVER['PHP_SELF'], 'settings') !== false && basename($_SERVER['PHP_SELF']) !== 'profile.php' ? 'bg-amber-600 text-white font-semibold' : 'text-slate-300 hover:bg-[#1a0f0a] hover:text-white'; ?>">
            <i class="fas fa-cog w-5"></i><span>Settings</span>
        </a>
    </nav>
</aside>
