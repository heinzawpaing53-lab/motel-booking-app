<?php
define('PAGE_TITLE', 'About Us');
require_once 'config/db.php';
include 'includes/header.php';
?>

<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="text-blue-600 font-semibold tracking-wider uppercase text-sm">About Us</span>
            <h1 class="font-[Playfair_Display] text-5xl font-bold mt-2">Luxury Motel</h1>
            <p class="text-gray-500 mt-4 max-w-2xl mx-auto">Where luxury meets comfort. Experience world-class hospitality and premium accommodations.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-20">
            <div>
                <h2 class="font-[Playfair_Display] text-3xl font-bold mb-6">Our History</h2>
                <p class="text-gray-600 leading-relaxed mb-4">Founded in 2015, Luxury Motel has grown from a small boutique establishment into one of the most sought-after accommodation destinations. Our commitment to excellence and guest satisfaction has been the cornerstone of our success.</p>
                <p class="text-gray-600 leading-relaxed">Over the years, we have welcomed thousands of guests from around the world, providing them with unforgettable experiences and exceptional service.</p>
            </div>
            <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=600" class="rounded-xl shadow-lg" alt="Hotel History">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-20">
            <div class="bg-gray-50 p-8 rounded-xl">
                <h3 class="font-[Playfair_Display] text-2xl font-bold mb-4"><i class="fas fa-bullseye text-blue-600 mr-2"></i>Our Mission</h3>
                <p class="text-gray-600">To provide exceptional hospitality experiences that exceed guest expectations through personalized service, attention to detail, and a commitment to quality in every aspect of their stay.</p>
            </div>
            <div class="bg-gray-50 p-8 rounded-xl">
                <h3 class="font-[Playfair_Display] text-2xl font-bold mb-4"><i class="fas fa-eye text-blue-600 mr-2"></i>Our Vision</h3>
                <p class="text-gray-600">To be the leading choice for travelers seeking luxury accommodation, recognized globally for our innovative approach to hospitality and unwavering dedication to guest satisfaction.</p>
            </div>
        </div>

        <!-- Services -->
        <div class="mb-20">
            <h2 class="font-[Playfair_Display] text-3xl font-bold mb-8 text-center">Our Services</h2>
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
                <div class="flex items-start space-x-4 p-6 bg-gray-50 rounded-xl">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0"><i class="fas <?php echo $s[0]; ?> text-blue-600"></i></div>
                    <div><h4 class="font-semibold mb-1"><?php echo $s[1]; ?></h4><p class="text-sm text-gray-500"><?php echo $s[2]; ?></p></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Policies -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-20">
            <div class="bg-blue-50 p-8 rounded-xl">
                <h3 class="font-[Playfair_Display] text-2xl font-bold mb-4"><i class="fas fa-clock text-blue-600 mr-2"></i>Check-in / Check-out Policy</h3>
                <ul class="space-y-3 text-gray-600">
                    <li><i class="fas fa-sign-in-alt text-blue-600 mr-2"></i><strong>Check-in Time:</strong> 2:00 PM</li>
                    <li><i class="fas fa-sign-out-alt text-blue-600 mr-2"></i><strong>Check-out Time:</strong> 12:00 PM</li>
                    <li><i class="fas fa-id-card text-blue-600 mr-2"></i>Valid ID required at check-in</li>
                    <li><i class="fas fa-credit-card text-blue-600 mr-2"></i>Security deposit required</li>
                </ul>
            </div>
            <div class="bg-gray-50 p-8 rounded-xl">
                <h3 class="font-[Playfair_Display] text-2xl font-bold mb-4"><i class="fas fa-gavel text-blue-600 mr-2"></i>Rules & Regulations</h3>
                <ul class="space-y-3 text-gray-600">
                    <li><i class="fas fa-ban text-red-500 mr-2"></i>No smoking inside rooms</li>
                    <li><i class="fas fa-paw text-blue-600 mr-2"></i>Pets allowed with prior notice</li>
                    <li><i class="fas fa-volume-up text-blue-600 mr-2"></i>Quiet hours after 10 PM</li>
                    <li><i class="fas fa-glass-cheers text-blue-600 mr-2"></i>No parties or events without permission</li>
                </ul>
            </div>
        </div>

        <!-- Google Map -->
        <div class="mb-8">
            <h2 class="font-[Playfair_Display] text-3xl font-bold mb-6 text-center">Find Us</h2>
            <div class="rounded-xl overflow-hidden shadow-lg">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.9663095919367!2d-73.985428!3d40.748817!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDDCsDQ0JzU1LjciTiA3M8KwNTknMDcuNSJX!5e0!3m2!1sen!2sus!4v1" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
