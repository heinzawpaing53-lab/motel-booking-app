<aside class="bg-gray-900 text-white w-64 min-h-screen fixed left-0 top-0 z-40 transition-all duration-300" id="adminSidebar">
    <div class="p-4 border-b border-gray-800">
        <div class="flex items-center justify-between">
            <a href="<?php echo SITE_URL; ?>admin/dashboard.php" class="flex items-center space-x-2">
                <i class="fas fa-hotel text-blue-400 text-xl"></i>
                <span class="font-bold text-lg">Luxury Motel</span>
            </a>
            <button id="sidebarToggle" class="text-gray-400 hover:text-white lg:hidden">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <nav class="p-4 space-y-1">
        <a href="<?php echo SITE_URL; ?>admin/dashboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-gray-800 text-blue-400' : 'text-gray-300'; ?>">
            <i class="fas fa-tachometer-alt w-5"></i><span>Dashboard</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/rooms/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'rooms') !== false ? 'bg-gray-800 text-blue-400' : 'text-gray-300'; ?>">
            <i class="fas fa-bed w-5"></i><span>Rooms</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/room-types/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'room-types') !== false ? 'bg-gray-800 text-blue-400' : 'text-gray-300'; ?>">
            <i class="fas fa-tags w-5"></i><span>Room Types</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/floors/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'floors') !== false ? 'bg-gray-800 text-blue-400' : 'text-gray-300'; ?>">
            <i class="fas fa-layer-group w-5"></i><span>Floors</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/amenities/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'amenities') !== false ? 'bg-gray-800 text-blue-400' : 'text-gray-300'; ?>">
            <i class="fas fa-concierge-bell w-5"></i><span>Amenities</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/bookings/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'bookings') !== false ? 'bg-gray-800 text-blue-400' : 'text-gray-300'; ?>">
            <i class="fas fa-calendar-check w-5"></i><span>Bookings</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/users/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'users') !== false ? 'bg-gray-800 text-blue-400' : 'text-gray-300'; ?>">
            <i class="fas fa-users w-5"></i><span>Customers</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/requests/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'requests') !== false ? 'bg-gray-800 text-blue-400' : 'text-gray-300'; ?>">
            <i class="fas fa-clipboard-list w-5"></i><span>Special Requests</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/reports/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'reports') !== false ? 'bg-gray-800 text-blue-400' : 'text-gray-300'; ?>">
            <i class="fas fa-chart-bar w-5"></i><span>Reports</span>
        </a>
        <a href="<?php echo SITE_URL; ?>admin/settings/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition <?php echo strpos($_SERVER['PHP_SELF'], 'settings') !== false ? 'bg-gray-800 text-blue-400' : 'text-gray-300'; ?>">
            <i class="fas fa-cog w-5"></i><span>Settings</span>
        </a>
        <hr class="border-gray-800 my-4">
        <a href="<?php echo SITE_URL; ?>index.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition text-gray-300">
            <i class="fas fa-arrow-left w-5"></i><span>Back to Site</span>
        </a>
        <a href="<?php echo SITE_URL; ?>logout.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition text-red-400">
            <i class="fas fa-sign-out-alt w-5"></i><span>Logout</span>
        </a>
    </nav>
</aside>
