<footer class="bg-luxury-900 text-luxury-200 pt-20 pb-8">
    <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-10 mb-10">
            <div>
                <div class="flex items-center space-x-2.5 mb-4">
                    <i class="fas fa-hotel text-luxury-400 text-lg"></i>
                    <span class="font-serif text-lg font-semibold text-luxury-100 tracking-wide">Luxury Motel</span>
                </div>
                <p class="text-luxury-300 text-xs leading-relaxed mb-4">Experience luxury and comfort at its finest. Your perfect getaway awaits at Luxury Motel.</p>
                <div class="flex space-x-3">
                    <a href="#" class="w-9 h-9 bg-luxury-800 border border-luxury-600/30 rounded-full flex items-center justify-center hover:bg-luxury-400 hover:text-luxury-900 hover:border-luxury-400 transition-all duration-300 text-sm"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="w-9 h-9 bg-luxury-800 border border-luxury-600/30 rounded-full flex items-center justify-center hover:bg-luxury-400 hover:text-luxury-900 hover:border-luxury-400 transition-all duration-300 text-sm"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="w-9 h-9 bg-luxury-800 border border-luxury-600/30 rounded-full flex items-center justify-center hover:bg-luxury-400 hover:text-luxury-900 hover:border-luxury-400 transition-all duration-300 text-sm"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div>
                <h4 class="text-luxury-100 font-semibold mb-4 text-xs tracking-[0.15em] uppercase">Quick Links</h4>
                <ul class="space-y-2.5 text-xs">
                    <li><a href="<?php echo SITE_URL; ?>index.php" class="text-luxury-300 hover:text-luxury-400 transition-colors duration-300">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>rooms.php" class="text-luxury-300 hover:text-luxury-400 transition-colors duration-300">Rooms</a></li>
                    <li><a href="<?php echo SITE_URL; ?>about.php" class="text-luxury-300 hover:text-luxury-400 transition-colors duration-300">About Us</a></li>
                    <li><a href="<?php echo SITE_URL; ?>register.php" class="text-luxury-300 hover:text-luxury-400 transition-colors duration-300">Register</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-luxury-100 font-semibold mb-4 text-xs tracking-[0.15em] uppercase">Services</h4>
                <ul class="space-y-2.5 text-xs">
                    <li><span class="text-luxury-300 hover:text-luxury-400 transition-colors duration-300 cursor-pointer">Room Booking</span></li>
                    <li><span class="text-luxury-300 hover:text-luxury-400 transition-colors duration-300 cursor-pointer">Restaurant</span></li>
                    <li><span class="text-luxury-300 hover:text-luxury-400 transition-colors duration-300 cursor-pointer">Spa & Wellness</span></li>
                    <li><span class="text-luxury-300 hover:text-luxury-400 transition-colors duration-300 cursor-pointer">Event Hall</span></li>
                </ul>
            </div>
            <div>
                <h4 class="text-luxury-100 font-semibold mb-4 text-xs tracking-[0.15em] uppercase">Contact</h4>
                <ul class="space-y-2.5 text-xs">
                    <li class="flex items-start space-x-2.5"><i class="fas fa-map-marker-alt mt-0.5 text-luxury-400 text-[10px]"></i><span class="text-luxury-300">123 Luxury Street, City</span></li>
                    <li class="flex items-center space-x-2.5"><i class="fas fa-phone text-luxury-400 text-[10px]"></i><span class="text-luxury-300">+1234567890</span></li>
                    <li class="flex items-center space-x-2.5"><i class="fas fa-envelope text-luxury-400 text-[10px]"></i><span class="text-luxury-300">info@luxurymotel.com</span></li>
                </ul>
            </div>
        </div>
        <hr class="border-luxury-700/40 mb-6">
        <div class="text-center text-xs text-luxury-400/60 pb-2">
            &copy; <?php echo date('Y'); ?> Luxury Motel. All rights reserved.
        </div>
    </div>
</footer>
<div id="systemModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm transition-all duration-300">
    <div id="systemModalContent" class="bg-luxury-800 rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6 transform transition-all duration-300 scale-95 opacity-0">
        <div class="text-center">
            <div id="modalIconWrapper" class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center">
                <i id="modalIcon" class="text-2xl"></i>
            </div>
            <h3 id="modalTitle" class="text-xl font-bold text-luxury-100 mb-2"></h3>
            <p id="modalMessage" class="text-luxury-300 text-sm mb-6"></p>
        </div>
        <div id="modalActions" class="flex gap-3 justify-center"></div>
    </div>
</div>

<script src="<?php echo SITE_URL; ?>assets/js/main.js"></script>
</body>
</html>
