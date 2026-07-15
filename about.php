<?php
define('PAGE_TITLE', 'About Us');
require_once 'config/db.php';
include 'includes/header.php';
?>

<section class="py-10 bg-luxury-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <span class="text-luxury-400 font-semibold tracking-wider uppercase text-sm">About Us</span>
            <h1 class="font-[Playfair_Display] text-5xl font-bold mt-2 text-luxury-900">Luxury Motel</h1>
            <p class="text-luxury-300 mt-4 max-w-2xl mx-auto">Where luxury meets comfort. Experience world-class hospitality and premium accommodations.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-12">
            <div>
                <h2 class="font-[Playfair_Display] text-3xl font-bold mb-6 text-luxury-900">Our History</h2>
                <p class="text-luxury-600 leading-relaxed mb-4">Founded in 2015, Luxury Motel has grown from a small boutique establishment into one of the most sought-after accommodation destinations. Our commitment to excellence and guest satisfaction has been the cornerstone of our success.</p>
                <p class="text-luxury-600 leading-relaxed">Over the years, we have welcomed thousands of guests from around the world, providing them with unforgettable experiences and exceptional service.</p>
            </div>
            <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=600" class="rounded-xl shadow-lg" alt="Hotel History">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
            <div class="bg-luxury-800 p-8 rounded-xl">
                <h3 class="font-[Playfair_Display] text-2xl font-bold mb-4 text-luxury-100"><i class="fas fa-bullseye text-luxury-400 mr-2"></i>Our Mission</h3>
                <p class="text-luxury-200">To provide exceptional hospitality experiences that exceed guest expectations through personalized service, attention to detail, and a commitment to quality in every aspect of their stay.</p>
            </div>
            <div class="bg-luxury-800 p-8 rounded-xl">
                <h3 class="font-[Playfair_Display] text-2xl font-bold mb-4 text-luxury-100"><i class="fas fa-eye text-luxury-400 mr-2"></i>Our Vision</h3>
                <p class="text-luxury-200">To be the leading choice for travelers seeking luxury accommodation, recognized globally for our innovative approach to hospitality and unwavering dedication to guest satisfaction.</p>
            </div>
        </div>

        <!-- Services -->
        <div class="mb-12">
            <h2 class="font-[Playfair_Display] text-3xl font-bold mb-8 text-center text-luxury-900">Our Services</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php
                $services = [
                    ['fa-concierge-bell', 'Room Service', '24/7 room service with a diverse menu'],
                    ['fa-spa', 'Spa & Wellness', 'Relax and rejuvenate at our premium spa'],
                    ['fa-dumbbell', 'Fitness Center', 'State-of-the-art fitness facilities'],
                    ['fa-utensils', 'Fine Dining', 'Exquisite dining experience'],
                    ['fa-car', 'Airport Shuttle', 'Convenient airport transfers'],
                    ['fa-wifi', 'Free WiFi', 'High-speed internet throughout']
                ];
                foreach($services as $s): ?>
                <div class="flex items-start space-x-4 p-6 bg-luxury-800 rounded-xl">
                    <div class="w-12 h-12 bg-luxury-700 rounded-full flex items-center justify-center flex-shrink-0"><i class="fas <?php echo $s[0]; ?> text-luxury-400"></i></div>
                    <div><h4 class="font-semibold mb-1 text-luxury-100"><?php echo $s[1]; ?></h4><p class="text-sm text-luxury-300"><?php echo $s[2]; ?></p></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Policies -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
            <div class="bg-luxury-700 p-8 rounded-xl">
                <h3 class="font-[Playfair_Display] text-2xl font-bold mb-4 text-luxury-100"><i class="fas fa-clock text-luxury-400 mr-2"></i>Check-in / Check-out Policy</h3>
                <ul class="space-y-3 text-luxury-200">
                    <li><i class="fas fa-sign-in-alt text-luxury-400 mr-2"></i><strong>Check-in Time:</strong> 2:00 PM</li>
                    <li><i class="fas fa-sign-out-alt text-luxury-400 mr-2"></i><strong>Check-out Time:</strong> 12:00 PM</li>
                    <li><i class="fas fa-id-card text-luxury-400 mr-2"></i>Valid ID required at check-in</li>
                    <li><i class="fas fa-credit-card text-luxury-400 mr-2"></i>Security deposit required</li>
                </ul>
            </div>
            <div class="bg-luxury-800 p-8 rounded-xl">
                <h3 class="font-[Playfair_Display] text-2xl font-bold mb-4 text-luxury-100"><i class="fas fa-gavel text-luxury-400 mr-2"></i>Rules & Regulations</h3>
                <ul class="space-y-3 text-luxury-200">
                    <li><i class="fas fa-ban text-error mr-2"></i>No smoking inside rooms</li>
                    <li><i class="fas fa-paw text-luxury-400 mr-2"></i>Pets allowed with prior notice</li>
                    <li><i class="fas fa-volume-up text-luxury-400 mr-2"></i>Quiet hours after 10 PM</li>
                    <li><i class="fas fa-glass-cheers text-luxury-400 mr-2"></i>No parties or events without permission</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
