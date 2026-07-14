<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
<nav class="bg-white shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative flex items-center justify-between h-16">
            <div class="flex items-center">
                <a href="<?php echo SITE_URL; ?>index.php" class="flex items-center space-x-2">
                    <i class="fas fa-hotel text-amber-500 text-2xl"></i>
                    <span class="font-[Playfair_Display] text-xl font-bold text-stone-800">Luxury Motel</span>
                </a>
            </div>
            <div class="hidden md:flex absolute left-1/2 transform -translate-x-1/2 items-center space-x-10">
                <a href="<?php echo SITE_URL; ?>index.php" class="<?php echo $currentPage === 'index.php' ? 'text-amber-500 font-semibold' : 'text-gray-600'; ?> hover:text-amber-500 transition font-medium">Home</a>
                <a href="<?php echo SITE_URL; ?>rooms.php" class="<?php echo $currentPage === 'rooms.php' ? 'text-amber-500 font-semibold' : 'text-gray-600'; ?> hover:text-amber-500 transition font-medium">Rooms</a>
                <a href="<?php echo SITE_URL; ?>about.php" class="<?php echo $currentPage === 'about.php' ? 'text-amber-500 font-semibold' : 'text-gray-600'; ?> hover:text-amber-500 transition font-medium">About</a>
                <a href="<?php echo SITE_URL; ?>contact.php" class="<?php echo $currentPage === 'contact.php' ? 'text-amber-500 font-semibold' : 'text-gray-600'; ?> hover:text-amber-500 transition font-medium">Contact Us</a>
            </div>
            <div class="hidden md:flex items-center space-x-6">
                <?php if (isLoggedIn()): ?>
                    <div class="relative group">
                        <button class="flex items-center space-x-1 text-gray-600 hover:text-amber-500 transition font-medium">
                            <i class="fas fa-user-circle text-lg"></i>
                            <span><?php echo htmlspecialchars($_SESSION['first_name'] ?? ''); ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                            <a href="<?php echo SITE_URL; ?>profile.php" class="block px-4 py-2 text-gray-700 hover:bg-amber-50 hover:text-amber-500"><i class="fas fa-user mr-2"></i>My Profile</a>
                            <a href="<?php echo SITE_URL; ?>booking-history.php" class="block px-4 py-2 text-gray-700 hover:bg-amber-50 hover:text-amber-500"><i class="fas fa-calendar-check mr-2"></i>My Bookings</a>
                            <a href="<?php echo SITE_URL; ?>profile.php" class="block px-4 py-2 text-gray-700 hover:bg-amber-50 hover:text-amber-500"><i class="fas fa-cog mr-2"></i>Settings</a>
                            <?php if (isAdmin()): ?>
                                <hr class="my-1">
                                <a href="<?php echo SITE_URL; ?>admin/dashboard.php" class="block px-4 py-2 text-gray-700 hover:bg-amber-50 hover:text-amber-500"><i class="fas fa-tachometer-alt mr-2"></i>Admin Panel</a>
                            <?php endif; ?>
                            <hr class="my-1">
                            <a href="<?php echo SITE_URL; ?>logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-50"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>login.php" class="<?php echo $currentPage === 'login.php' ? 'text-amber-500 font-semibold' : 'text-gray-600'; ?> hover:text-amber-500 transition font-medium">Login</a>
                    <a href="<?php echo SITE_URL; ?>register.php" class="<?php echo $currentPage === 'register.php' ? 'text-amber-500 font-semibold' : 'text-gray-600'; ?> hover:text-amber-500 transition font-medium">Register</a>
                <?php endif; ?>
            </div>
            <button id="mobile-menu-btn" class="md:hidden text-gray-600 text-2xl">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
    <div id="mobile-menu" class="hidden md:hidden bg-white border-t">
        <a href="<?php echo SITE_URL; ?>index.php" class="block px-4 py-2 <?php echo $currentPage === 'index.php' ? 'text-amber-500 font-semibold bg-amber-50' : 'text-gray-600'; ?> hover:bg-amber-50">Home</a>
        <a href="<?php echo SITE_URL; ?>rooms.php" class="block px-4 py-2 <?php echo $currentPage === 'rooms.php' ? 'text-amber-500 font-semibold bg-amber-50' : 'text-gray-600'; ?> hover:bg-amber-50">Rooms</a>
        <a href="<?php echo SITE_URL; ?>about.php" class="block px-4 py-2 <?php echo $currentPage === 'about.php' ? 'text-amber-500 font-semibold bg-amber-50' : 'text-gray-600'; ?> hover:bg-amber-50">About</a>
        <a href="<?php echo SITE_URL; ?>contact.php" class="block px-4 py-2 <?php echo $currentPage === 'contact.php' ? 'text-amber-500 font-semibold bg-amber-50' : 'text-gray-600'; ?> hover:bg-amber-50">Contact Us</a>
        <?php if (isLoggedIn()): ?>
            <a href="<?php echo SITE_URL; ?>profile.php" class="block px-4 py-2 text-gray-600 hover:bg-amber-50">Profile</a>
            <a href="<?php echo SITE_URL; ?>booking-history.php" class="block px-4 py-2 text-gray-600 hover:bg-amber-50">My Bookings</a>
            <a href="<?php echo SITE_URL; ?>profile.php" class="block px-4 py-2 text-gray-600 hover:bg-amber-50"><i class="fas fa-cog mr-2"></i>Settings</a>
            <?php if (isAdmin()): ?>
                <a href="<?php echo SITE_URL; ?>admin/dashboard.php" class="block px-4 py-2 text-gray-600 hover:bg-amber-50">Admin Panel</a>
            <?php endif; ?>
            <a href="<?php echo SITE_URL; ?>logout.php" class="block px-4 py-2 text-red-600">Logout</a>
        <?php else: ?>
            <a href="<?php echo SITE_URL; ?>login.php" class="block px-4 py-2 <?php echo $currentPage === 'login.php' ? 'text-amber-500 font-semibold bg-amber-50' : 'text-gray-600'; ?> hover:bg-amber-50">Login</a>
            <a href="<?php echo SITE_URL; ?>register.php" class="block px-4 py-2 <?php echo $currentPage === 'register.php' ? 'text-amber-500 font-semibold bg-amber-50' : 'text-gray-600'; ?> hover:bg-amber-50">Register</a>
        <?php endif; ?>
    </div>
</nav>
