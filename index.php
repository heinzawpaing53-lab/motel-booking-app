<?php
define('PAGE_TITLE', 'Home');
require_once 'config/db.php';
$stmt = $pdo->query("SELECT r.*, rt.type_name, rt.price_per_night, rt.bed_type, rt.room_size, f.floor_name,
    (SELECT image_path FROM room_images WHERE room_id = r.room_id LIMIT 1) AS image
    FROM rooms r JOIN room_types rt ON r.type_id = rt.type_id JOIN floors f ON r.floor_id = f.floor_id
    WHERE r.status = 'Available' LIMIT 6");
$featuredRooms = $stmt->fetchAll();
?>
<style>
    .amenities-track {
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        width: max-content;
        gap: 1.5rem;
        padding: 0.5rem 0;
    }
    .amenities-track.slide-right {
        animation: amenities-slide-right 45s linear infinite;
    }
    .amenities-track.slide-left {
        animation: amenities-slide-left 45s linear infinite;
    }
    .amenities-track:hover {
        animation-play-state: paused;
    }
    .amenity-card {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
    }
    .amenity-card:has(.fa-parking) {
        background: url('https://images.unsplash.com/photo-1506521781263-d8422e82f27a?w=600') center/cover no-repeat;
    }
    .amenity-card:has(.fa-snowflake) {
        background: url('https://images.unsplash.com/photo-1585338107529-13afc5f02586?w=600') center/cover no-repeat;
    }
    .amenity-card:has(.fa-tv) {
        background: url('https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=600') center/cover no-repeat;
    }
    .btn-primary,
    a.btn-primary {
        background: linear-gradient(135deg, #D4AF37, #B8960F) !important;
        color: #fff !important;
        border: none !important;
    }
    .btn-primary:hover,
    a.btn-primary:hover {
        background: linear-gradient(135deg, #B8960F, #9A7D0A) !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(212,175,55,0.3) !important;
    }
    @keyframes amenities-slide-right {
        0%   { transform: translateX(-50%); }
        100% { transform: translateX(0%); }
    }
    @keyframes amenities-slide-left {
        0%   { transform: translateX(0%); }
        100% { transform: translateX(-50%); }
    }
    @keyframes testimonial-slide-left {
        0%   { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .animate-testimonial-slide {
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        gap: 1.5rem;
        width: max-content;
        animation: testimonial-slide-left 40s linear infinite;
    }
    .animate-testimonial-slide:hover {
        animation-play-state: paused;
    }
</style>

<?php include 'includes/header.php'; ?>

<!-- Hero Slider -->
<section class="hero-slider">
    <div class="hero-slide active" style="background-image: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1600');"></div>
    <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1582719508461-905c673771fd?w=1600');"></div>
    <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1590490360182-c33d57733427?w=1600');"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content px-4">
        <p class="text-amber-300 font-semibold tracking-widest uppercase text-sm mb-4">Welcome to Luxury</p>
        <h1 class="font-[Playfair_Display] text-5xl md:text-7xl font-bold mb-6 leading-tight">Experience <span class="text-amber-400">Ultimate</span><br>Comfort & Luxury</h1>
        <p class="text-lg text-gray-200 max-w-2xl mb-8">Book your perfect stay with us. Enjoy premium rooms, exceptional service, and unforgettable memories.</p>
    </div>
</section>



<!-- Facilities -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <span class="text-amber-500 font-semibold tracking-wider uppercase text-sm">Our Facilities</span>
            <h2 class="font-[Playfair_Display] text-4xl font-bold mt-2">Premium Amenities</h2>
        </div>
    </div>

    <!-- Row 1: Slides RIGHT — WiFi, Pool, Parking, AC, TV -->
    <div class="w-full overflow-hidden mb-6">
        <div class="amenities-track slide-right">
            <?php
            $row1 = [
                ['fa-wifi', 'Free WiFi', 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?w=600'],
                ['fa-swimming-pool', 'Swimming Pool', 'https://images.unsplash.com/photo-1576013551627-0cc20b96c2a7?w=600'],
                ['fa-parking', 'Parking', 'https://images.unsplash.com/photo-1573349252278-60408a505f70?w=600'],
                ['fa-snowflake', 'Air Conditioning', 'https://images.unsplash.com/photo-1631545806609-9ba214a13a26?w=600'],
                ['fa-tv', 'Flat Screen TV', 'https://images.unsplash.com/photo-1593784991095-a20594af0433?w=600']
            ];
            for ($i = 0; $i < 2; $i++):
                foreach ($row1 as $f): ?>
                    <div class="amenity-card relative w-72 h-48 flex-none rounded-xl overflow-hidden cursor-pointer group">
                        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('<?php echo $f[2]; ?>');"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
                        <div class="relative h-full flex flex-col items-center justify-end p-4 text-center">
                            <div class="text-amber-400 text-2xl mb-2"><i class="fas <?php echo $f[0]; ?>"></i></div>
                            <h4 class="font-semibold text-sm text-white"><?php echo $f[1]; ?></h4>
                        </div>
                    </div>
                <?php endforeach;
            endfor; ?>
        </div>
    </div>

    <!-- Row 2: Slides LEFT — Laundry, Restaurant, Coffee, Security, Elevator -->
    <div class="w-full overflow-hidden">
        <div class="amenities-track slide-left">
            <?php
            $row2 = [
                ['fa-tshirt', 'Laundry', 'https://images.unsplash.com/photo-1563453392212-326f5e854473?w=600'],
                ['fa-utensils', 'Restaurant', 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=600'],
                ['fa-coffee', 'Coffee Shop', 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=600'],
                ['fa-shield-alt', 'Security', 'https://images.unsplash.com/photo-1557597774-9d273605dfa9?w=600'],
                ['fa-elevator', 'Elevator', 'https://images.unsplash.com/photo-1551632436-cbf8dd35adfa?w=600']
            ];
            for ($i = 0; $i < 2; $i++):
                foreach ($row2 as $f): ?>
                    <div class="amenity-card relative w-72 h-48 flex-none rounded-xl overflow-hidden cursor-pointer group">
                        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('<?php echo $f[2]; ?>');"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
                        <div class="relative h-full flex flex-col items-center justify-end p-4 text-center">
                            <div class="text-amber-400 text-2xl mb-2"><i class="fas <?php echo $f[0]; ?>"></i></div>
                            <h4 class="font-semibold text-sm text-white"><?php echo $f[1]; ?></h4>
                        </div>
                    </div>
                <?php endforeach;
            endfor; ?>
        </div>
    </div>
</section>

<!-- Featured Rooms -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <span class="text-amber-500 font-semibold tracking-wider uppercase text-sm">Our Rooms</span>
            <h2 class="font-[Playfair_Display] text-4xl font-bold mt-2">Featured Rooms</h2>
            <p class="text-gray-500 mt-2">Choose from our selection of premium rooms</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php
            $roomTypeImages = [
                'Standard Room' => 'https://images.unsplash.com/photo-1595576508898-0ad5c879a061?w=600',
                'Deluxe Room'   => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=600',
                'Superior Room' => 'https://images.unsplash.com/photo-1584132967334-10e028bd69f7?w=600',
                'Family Room'   => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=600',
                'Suite Room'    => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600',
                'VIP Room'      => 'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=600'
            ];
            ?>
            <?php foreach ($featuredRooms as $room): ?>
                <div class="room-card bg-white shadow-md">
                    <div class="relative overflow-hidden">
                        <img src="<?php echo $room['image'] ?: ($roomTypeImages[$room['type_name']] ?? 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=600'); ?>" alt="<?php echo $room['room_name']; ?>" class="w-full">
                        <span class="absolute top-4 right-4 bg-amber-500 text-white px-3 py-1 rounded-full text-sm font-semibold"><?php echo formatCurrency($room['price_per_night']); ?>/night</span>
                    </div>
                    <div class="p-6">
                        <h3 class="font-[Playfair_Display] text-xl font-bold mb-2"><?php echo $room['room_name'] ?: 'Room ' . $room['room_number']; ?></h3>
                        <div class="flex items-center space-x-4 text-sm text-gray-500 mb-4">
                            <span><i class="fas fa-bed text-amber-500 mr-1"></i><?php echo $room['bed_type']; ?></span>
                            <span><i class="fas fa-arrows-alt text-amber-500 mr-1"></i><?php echo $room['room_size']; ?></span>
                            <span><i class="fas fa-layer-group text-amber-500 mr-1"></i><?php echo $room['floor_name']; ?></span>
                        </div>
                        <p class="text-gray-500 text-sm mb-4"><?php echo substr($room['type_name'], 0, 60); ?></p>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-bold text-amber-500"><?php echo formatCurrency($room['price_per_night']); ?></span>
                            <a href="room-details.php?id=<?php echo $room['room_id']; ?>" class="bg-amber-500 text-white px-4 py-2 rounded-lg hover:bg-amber-600 transition text-sm">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-10">
            <a href="rooms.php" class="btn-primary">View All Rooms <i class="fas fa-arrow-right ml-2"></i></a>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="text-amber-500 font-semibold tracking-wider uppercase text-sm">About Us</span>
                <h2 class="font-[Playfair_Display] text-4xl font-bold mt-2 mb-6">A Perfect Blend of <span class="text-amber-500">Luxury</span> & Comfort</h2>
                <p class="text-gray-600 mb-6 leading-relaxed">Welcome to Luxury Motel, where every stay is crafted to perfection. Our motel combines modern elegance with warm hospitality to create an unforgettable experience for every guest.</p>
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="flex items-start space-x-3"><i class="fas fa-check-circle text-amber-500 mt-1"></i><span class="text-sm">Premium Rooms</span></div>
                    <div class="flex items-start space-x-3"><i class="fas fa-check-circle text-amber-500 mt-1"></i><span class="text-sm">24/7 Service</span></div>
                    <div class="flex items-start space-x-3"><i class="fas fa-check-circle text-amber-500 mt-1"></i><span class="text-sm">Free WiFi</span></div>
                    <div class="flex items-start space-x-3"><i class="fas fa-check-circle text-amber-500 mt-1"></i><span class="text-sm">Best Rates</span></div>
                </div>
                <a href="about.php" class="btn-primary">Learn More <i class="fas fa-arrow-right ml-2"></i></a>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <img src="https://images.unsplash.com/photo-1590490360182-c33d57733427?w=400" class="rounded-xl h-64 object-cover" alt="Room">
                <img src="https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=400" class="rounded-xl h-64 object-cover mt-8" alt="Lobby">
            </div>
        </div>
    </div>
</section>

<!-- Gallery -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <span class="text-amber-500 font-semibold tracking-wider uppercase text-sm">Gallery</span>
            <h2 class="font-[Playfair_Display] text-4xl font-bold mt-2">Our Gallery</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php
            $galleryImages = [
                'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=600',
                'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=600',
                'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=600',
                'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=600',
                'https://images.unsplash.com/photo-1584132967334-10e028bd69f7?w=600',
                'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=600',
                'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=600',
                'https://images.unsplash.com/photo-1595576508898-0ad5c879a061?w=600'
            ];
            foreach ($galleryImages as $img): ?>
                <div class="gallery-item">
                    <img src="<?php echo $img; ?>" alt="Gallery">
                    <div class="gallery-overlay"><i class="fas fa-search-plus text-white text-3xl"></i></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- Stats -->
<section class="py-12 bg-amber-500 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <?php
            $stats = [
                ['fa-hotel', '120+', 'Rooms'],
                ['fa-users', '5000+', 'Happy Guests'],
                ['fa-calendar-check', '10+', 'Years Experience'],
                ['fa-award', '50+', 'Awards']
            ];
            foreach ($stats as $s): ?>
                <div class="stat-card">
                    <i class="fas <?php echo $s[0]; ?> text-4xl mb-3"></i>
                    <h3 class="text-4xl font-bold mb-1"><?php echo $s[1]; ?></h3>
                    <p class="text-amber-200"><?php echo $s[2]; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- Testimonials -->
<section class="py-12 bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <div class="text-center">
            <span class="text-amber-500 font-semibold tracking-wider uppercase text-sm">Testimonials</span>
            <h2 class="font-[Playfair_Display] text-4xl font-bold mt-2">What Our Guests Say</h2>
        </div>
    </div>
    <div class="w-full overflow-hidden">
        <div class="animate-testimonial-slide">
            <?php
            $testimonials = [
                ['John D.', 'Business Traveler', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face', 'Extremely clean room, lightning-fast Wi-Fi, and a seamless check-in process. Perfect for a productive stay.'],
                ['Sarah M.', 'Family Vacation', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&h=150&fit=crop&crop=face', 'The Family Executive room was spacious, beautifully arranged, and the kids loved being close to the pool!'],
                ['Michael R.', 'Solo Traveler', 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=150&h=150&fit=crop&crop=face', 'Excellent value for money. Very quiet floor, comfortable bedding, and friendly staff. Will definitely book again.'],
                ['Sophia L.', 'Romantic Getaway', 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150&h=150&fit=crop&crop=face', 'Absolutely breathtaking! The attention to detail in the suite design and the gold-standard service made our anniversary unforgettable.'],
                ['David K.', 'Executive Travel', 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=150&h=150&fit=crop&crop=face', 'The perfect balance of luxury and functionality. The workspace in the room was excellent, and the coffee shop was top-tier.'],
                ['Marcus & Elena', 'Weekend Escape', 'https://images.unsplash.com/photo-1551836022-deb4988cc6c0?w=150&h=150&fit=crop&crop=face', 'From the pristine pool to the high-end room amenities, every corner of this motel exudes luxury. We will definitely be returning!']
            ];
            for ($i = 0; $i < 2; $i++):
                foreach ($testimonials as $t): ?>
                    <div class="testimonial-card bg-gray-50 p-8 shadow-sm rounded-xl w-[400px] flex-none">
                        <div class="flex items-center space-x-1 text-yellow-400 mb-4">
                            <?php for ($s = 0; $s < 5; $s++): ?><i class="fa-solid fa-star"></i><?php endfor; ?>
                        </div>
                        <p class="text-gray-600 mb-6 italic">"<?php echo $t[3]; ?>"</p>
                        <div class="flex items-center space-x-3">
                            <img src="<?php echo $t[2]; ?>" alt="<?php echo $t[0]; ?>" class="w-12 h-12 rounded-full object-cover">
                            <div>
                                <h4 class="font-semibold"><?php echo $t[0]; ?></h4>
                                <p class="text-sm text-gray-500"><?php echo $t[1]; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach;
            endfor; ?>
        </div>
    </div>
</section>



<?php include 'includes/footer.php'; ?>