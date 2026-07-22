<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
<?php $isAccountPage = in_array($currentPage, ['profile.php', 'settings.php', 'booking-history.php', 'booking-details.php']); ?>
<nav style="background-color: #2C1810 !important; position: sticky; top: 0; z-index: 50; border-bottom: 1px solid rgba(122,85,52,0.2);">
    <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12">
        <div class="relative flex items-center justify-between" style="height: 56px;">
            <div class="flex items-center">
                <?php if ($isAccountPage): ?>
                    <a href="<?php echo SITE_URL; ?>index.php" class="inline-flex items-center gap-2 text-sm font-medium text-amber-100 hover:text-amber-400 transition-colors">
                        <i class="fas fa-arrow-left text-xs"></i>
                        <span>Back to Home</span>
                    </a>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>index.php" class="flex items-center space-x-2.5">
                        <i class="fas fa-hotel text-luxury-400 text-xl"></i>
                        <span class="font-serif text-lg font-semibold text-luxury-100 tracking-wide">Luxury Motel</span>
                    </a>
                <?php endif; ?>
            </div>
            <?php if (!$isAccountPage): ?>
            <div class="hidden md:flex absolute left-1/2 items-center" style="transform: translateX(-50%); gap: 2.5rem;">
                <?php
                $navLinks = [
                    'index.php' => 'Home',
                    'rooms.php' => 'Rooms',
                    'about.php' => 'About',
                    'contact.php' => 'Contact Us',
                ];
                foreach ($navLinks as $file => $label): ?>
                    <a href="<?php echo SITE_URL; ?><?php echo $file; ?>" class="<?php echo $currentPage === $file ? 'text-luxury-100 font-semibold' : 'text-luxury-300'; ?> hover:text-luxury-100 transition-colors duration-300 font-medium text-sm" style="padding: 4px 0; position: relative; letter-spacing: 0.025em;">
                        <?php echo $label; ?>
                        <?php if ($currentPage === $file): ?>
                            <span style="position: absolute; left: 0; bottom: -4px; width: 100%; height: 2px; background-color: #C8A96A; border-radius: 1px;"></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="hidden md:flex items-center">
                <span class="text-sm font-semibold text-luxury-100 tracking-wide"><?php echo defined('PAGE_TITLE') ? PAGE_TITLE : 'Account'; ?></span>
            </div>
            <?php endif; ?>
            <div class="hidden md:flex items-center" style="gap: 1.25rem;">
                <?php if (isLoggedIn()): ?>
                    <div class="relative group">
                        <button class="flex items-center space-x-1.5 text-luxury-300 hover:text-luxury-100 transition-colors duration-300 font-medium text-sm">
                            <i class="fas fa-user-circle text-base"></i>
                            <span><?php echo htmlspecialchars($_SESSION['first_name'] ?? ''); ?></span>
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </button>
                        <div class="absolute right-0 w-52 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 overflow-hidden" style="margin-top: 8px; background-color: #3B2418; border: 1px solid rgba(122,85,52,0.3); border-radius: 12px; box-shadow: 0 20px 40px rgba(0,0,0,0.4);">
                            <a href="<?php echo SITE_URL; ?>profile.php" class="block px-5 py-2.5 text-sm text-luxury-200 hover:text-luxury-400 transition-colors" style="background: transparent;"><i class="fas fa-user mr-2 text-xs"></i>My Profile</a>
                            <a href="<?php echo SITE_URL; ?>settings.php" class="block px-5 py-2.5 text-sm text-luxury-200 hover:text-luxury-400 transition-colors"><i class="fas fa-cog mr-2 text-xs"></i>Settings</a>
                            <?php if (isAdmin()): ?>
                                <hr style="margin: 4px 0; border-color: rgba(122,85,52,0.3);">
                                <a href="<?php echo SITE_URL; ?>admin/dashboard.php" class="block px-5 py-2.5 text-sm text-luxury-200 hover:text-luxury-400 transition-colors"><i class="fas fa-tachometer-alt mr-2 text-xs"></i>Admin Panel</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>login.php" class="<?php echo $currentPage === 'login.php' ? 'text-luxury-100 font-semibold' : 'text-luxury-300'; ?> hover:text-luxury-100 transition-colors duration-300 font-medium text-sm" style="padding: 4px 0; position: relative; letter-spacing: 0.025em;">
                        Login
                        <?php if ($currentPage === 'login.php'): ?>
                            <span style="position: absolute; left: 0; bottom: -4px; width: 100%; height: 2px; background-color: #C8A96A; border-radius: 1px;"></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?php echo SITE_URL; ?>register.php" class="<?php echo $currentPage === 'register.php' ? 'text-luxury-100 font-semibold' : 'text-luxury-300'; ?> hover:text-luxury-100 transition-colors duration-300 font-medium text-sm" style="padding: 4px 0; position: relative; letter-spacing: 0.025em;">
                        Register
                        <?php if ($currentPage === 'register.php'): ?>
                            <span style="position: absolute; left: 0; bottom: -4px; width: 100%; height: 2px; background-color: #C8A96A; border-radius: 1px;"></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
            </div>
            <button id="mobile-menu-btn" class="md:hidden text-luxury-300 text-xl">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
    <div id="mobile-menu" class="hidden md:hidden" style="background-color: #2C1810; border-top: 1px solid rgba(122,85,52,0.2);">
        <?php if ($isAccountPage): ?>
            <a href="<?php echo SITE_URL; ?>index.php" class="flex items-center gap-2 px-6 py-3 text-sm text-amber-100 hover:text-amber-400 transition-colors">
                <i class="fas fa-arrow-left text-xs"></i>Back to Home
            </a>
        <?php else: ?>
            <?php
            $mobileLinks = [
                'index.php' => 'Home',
                'rooms.php' => 'Rooms',
                'about.php' => 'About',
                'contact.php' => 'Contact Us',
            ];
            foreach ($mobileLinks as $file => $label): ?>
                <a href="<?php echo SITE_URL; ?><?php echo $file; ?>" class="block px-6 py-3 text-sm <?php echo $currentPage === $file ? 'text-luxury-400 font-semibold' : 'text-luxury-300'; ?> hover:text-luxury-100 transition-colors" style="background: transparent;"><?php echo $label; ?></a>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if (isLoggedIn()): ?>
            <a href="<?php echo SITE_URL; ?>profile.php" class="block px-6 py-3 text-sm text-luxury-300 hover:text-luxury-100 transition-colors">My Profile</a>
            <a href="<?php echo SITE_URL; ?>settings.php" class="block px-6 py-3 text-sm text-luxury-300 hover:text-luxury-100 transition-colors"><i class="fas fa-cog mr-2"></i>Settings</a>
            <?php if (isAdmin()): ?>
                <a href="<?php echo SITE_URL; ?>admin/dashboard.php" class="block px-6 py-3 text-sm text-luxury-300 hover:text-luxury-100 transition-colors">Admin Panel</a>
            <?php endif; ?>
        <?php else: ?>
            <a href="<?php echo SITE_URL; ?>login.php" class="block px-6 py-3 text-sm <?php echo $currentPage === 'login.php' ? 'text-luxury-400 font-semibold' : 'text-luxury-300'; ?> hover:text-luxury-100 transition-colors">Login</a>
            <a href="<?php echo SITE_URL; ?>register.php" class="block px-6 py-3 text-sm <?php echo $currentPage === 'register.php' ? 'text-luxury-400 font-semibold' : 'text-luxury-300'; ?> hover:text-luxury-100 transition-colors">Register</a>
        <?php endif; ?>
    </div>
</nav>
