<footer class="bg-luxury-900 text-luxury-200 pt-12 pb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div>
                <div class="flex items-center space-x-2 mb-3">
                    <i class="fas fa-hotel text-luxury-400 text-lg"></i>
                    <span class="font-[Playfair_Display] text-lg font-bold text-luxury-100">Luxury Motel</span>
                </div>
                <p class="text-luxury-300 text-xs leading-relaxed mb-3">Experience luxury and comfort at its finest. Your perfect getaway awaits at Luxury Motel.</p>
                <div class="flex space-x-3">
                    <a href="#" class="w-8 h-8 bg-luxury-700 rounded-full flex items-center justify-center hover:bg-luxury-400 hover:text-luxury-900 transition text-sm"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="w-8 h-8 bg-luxury-700 rounded-full flex items-center justify-center hover:bg-luxury-400 hover:text-luxury-900 transition text-sm"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="w-8 h-8 bg-luxury-700 rounded-full flex items-center justify-center hover:bg-luxury-400 hover:text-luxury-900 transition text-sm"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div>
                <h4 class="text-luxury-100 font-semibold mb-3 text-sm">Quick Links</h4>
                <ul class="space-y-1.5 text-xs">
                    <li><a href="<?php echo SITE_URL; ?>index.php" class="hover:text-luxury-400 transition">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>rooms.php" class="hover:text-luxury-400 transition">Rooms</a></li>
                    <li><a href="<?php echo SITE_URL; ?>about.php" class="hover:text-luxury-400 transition">About Us</a></li>
                    <li><a href="<?php echo SITE_URL; ?>register.php" class="hover:text-luxury-400 transition">Register</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-luxury-100 font-semibold mb-3 text-sm">Services</h4>
                <ul class="space-y-1.5 text-xs">
                    <li><span class="hover:text-luxury-400 transition cursor-pointer">Room Booking</span></li>
                    <li><span class="hover:text-luxury-400 transition cursor-pointer">Restaurant</span></li>
                    <li><span class="hover:text-luxury-400 transition cursor-pointer">Spa & Wellness</span></li>
                    <li><span class="hover:text-luxury-400 transition cursor-pointer">Event Hall</span></li>
                </ul>
            </div>
            <div>
                <h4 class="text-luxury-100 font-semibold mb-3 text-sm">Contact</h4>
                <ul class="space-y-1.5 text-xs">
                    <li class="flex items-start space-x-2"><i class="fas fa-map-marker-alt mt-0.5 text-luxury-400"></i><span>123 Luxury Street, City</span></li>
                    <li class="flex items-center space-x-2"><i class="fas fa-phone text-luxury-400"></i><span>+1234567890</span></li>
                    <li class="flex items-center space-x-2"><i class="fas fa-envelope text-luxury-400"></i><span>info@luxurymotel.com</span></li>
                </ul>
            </div>
        </div>
        <hr class="border-luxury-700 mb-4">
        <div class="text-center text-xs text-luxury-300 pb-2">
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
