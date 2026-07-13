<?php
define('PAGE_TITLE', 'Home');
require_once 'config/db.php';
$stmt = $pdo->query("SELECT r.*, rt.type_name, rt.price_per_night, rt.bed_type, rt.room_size, f.floor_name,
    (SELECT image_path FROM room_images WHERE room_id = r.room_id LIMIT 1) AS image
    FROM rooms r JOIN room_types rt ON r.type_id = rt.type_id JOIN floors f ON r.floor_id = f.floor_id
    WHERE r.status = 'Available' LIMIT 6");
$featuredRooms = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<!-- Hero Slider -->
<section class="hero-slider">
    <div class="hero-slide active" style="background-image: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1600');"></div>
    <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1582719508461-905c673771fd?w=1600');"></div>
    <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1590490360182-c33d57733427?w=1600');"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content px-4">
        <p class="text-blue-300 font-semibold tracking-widest uppercase text-sm mb-4">Welcome to Luxury</p>
        <h1 class="font-[Playfair_Display] text-5xl md:text-7xl font-bold mb-6 leading-tight">Experience <span class="text-blue-400">Ultimate</span><br>Comfort & Luxury</h1>
        <p class="text-lg text-gray-200 max-w-2xl mb-8">Book your perfect stay with us. Enjoy premium rooms, exceptional service, and unforgettable memories.</p>
        <!-- <div class="search-box max-w-4xl w-full">
            <form action="rooms.php" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Check In</label>
                    <input type="date" name="check_in" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Check Out</label>
                    <input type="date" name="check_out" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Guests</label>
                    <select name="guests" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm">
                        <?php for($i=1;$i<=5;$i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?> Guest<?php echo $i>1?'s':''; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Room Type</label>
                    <select name="type" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm">
                        <option value="">All Types</option>
                        <?php $types = $pdo->query("SELECT * FROM room_types")->fetchAll(); foreach($types as $t): ?>
                        <option value="<?php echo $t['type_id']; ?>"><?php echo $t['type_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-primary w-full"><i class="fas fa-search mr-2"></i>Search</button>
                </div>
            </form>
        </div> -->
    </div>
</section>

<!-- About Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="text-blue-600 font-semibold tracking-wider uppercase text-sm">About Us</span>
                <h2 class="font-[Playfair_Display] text-4xl font-bold mt-2 mb-6">A Perfect Blend of <span class="text-blue-600">Luxury</span> & Comfort</h2>
                <p class="text-gray-600 mb-6 leading-relaxed">Welcome to Luxury Motel, where every stay is crafted to perfection. Our motel combines modern elegance with warm hospitality to create an unforgettable experience for every guest.</p>
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="flex items-start space-x-3"><i class="fas fa-check-circle text-blue-600 mt-1"></i><span class="text-sm">Premium Rooms</span></div>
                    <div class="flex items-start space-x-3"><i class="fas fa-check-circle text-blue-600 mt-1"></i><span class="text-sm">24/7 Service</span></div>
                    <div class="flex items-start space-x-3"><i class="fas fa-check-circle text-blue-600 mt-1"></i><span class="text-sm">Free WiFi</span></div>
                    <div class="flex items-start space-x-3"><i class="fas fa-check-circle text-blue-600 mt-1"></i><span class="text-sm">Best Rates</span></div>
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

<!-- Facilities -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="text-blue-600 font-semibold tracking-wider uppercase text-sm">Our Facilities</span>
            <h2 class="font-[Playfair_Display] text-4xl font-bold mt-2">Premium Amenities</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
            <?php
            $facilities = [
                ['fa-wifi', 'Free WiFi'], ['fa-swimming-pool', 'Swimming Pool'], ['fa-parking', 'Parking'],
                ['fa-snowflake', 'Air Conditioning'], ['fa-tv', 'Flat Screen TV'], ['fa-tshirt', 'Laundry'],
                ['fa-utensils', 'Restaurant'], ['fa-coffee', 'Coffee Shop'], ['fa-shield-alt', 'Security'],
                ['fa-elevator', 'Elevator']
            ];
            foreach($facilities as $f): ?>
            <div class="feature-card bg-white rounded-xl p-6 text-center shadow-sm hover:shadow-md transition">
                <div class="feature-icon bg-blue-100 text-blue-600 mx-auto mb-4"><i class="fas <?php echo $f[0]; ?>"></i></div>
                <h4 class="font-semibold text-sm"><?php echo $f[1]; ?></h4>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Rooms -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="text-blue-600 font-semibold tracking-wider uppercase text-sm">Our Rooms</span>
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
            <?php foreach($featuredRooms as $room): ?>
            <div class="room-card bg-white shadow-md">
                <div class="relative overflow-hidden">
                    <img src="<?php echo $room['image'] ?: ($roomTypeImages[$room['type_name']] ?? 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=600'); ?>" alt="<?php echo $room['room_name']; ?>" class="w-full">
                    <span class="absolute top-4 right-4 bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-semibold"><?php echo formatCurrency($room['price_per_night']); ?>/night</span>
                </div>
                <div class="p-6">
                    <h3 class="font-[Playfair_Display] text-xl font-bold mb-2"><?php echo $room['room_name'] ?: 'Room '.$room['room_number']; ?></h3>
                    <div class="flex items-center space-x-4 text-sm text-gray-500 mb-4">
                        <span><i class="fas fa-bed text-blue-600 mr-1"></i><?php echo $room['bed_type']; ?></span>
                        <span><i class="fas fa-arrows-alt text-blue-600 mr-1"></i><?php echo $room['room_size']; ?></span>
                        <span><i class="fas fa-layer-group text-blue-600 mr-1"></i><?php echo $room['floor_name']; ?></span>
                    </div>
                    <p class="text-gray-500 text-sm mb-4"><?php echo substr($room['type_name'], 0, 60); ?></p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-blue-600"><?php echo formatCurrency($room['price_per_night']); ?></span>
                        <a href="room-details.php?id=<?php echo $room['room_id']; ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">View Details</a>
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

<!-- Gallery -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="text-blue-600 font-semibold tracking-wider uppercase text-sm">Gallery</span>
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
            foreach($galleryImages as $img): ?>
            <div class="gallery-item">
                <img src="<?php echo $img; ?>" alt="Gallery">
                <div class="gallery-overlay"><i class="fas fa-search-plus text-white text-3xl"></i></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="text-blue-600 font-semibold tracking-wider uppercase text-sm">Testimonials</span>
            <h2 class="font-[Playfair_Display] text-4xl font-bold mt-2">What Our Guests Say</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php
            $testimonials = [
                ['John D.', 'Business Traveler', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face', 'Extremely clean room, lightning-fast Wi-Fi, and a seamless check-in process. Perfect for a productive stay.'],
                ['Sarah M.', 'Family Vacation', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&h=150&fit=crop&crop=face', 'The Family Executive room was spacious, beautifully arranged, and the kids loved being close to the pool!'],
                ['Michael R.', 'Solo Traveler', 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=150&h=150&fit=crop&crop=face', 'Excellent value for money. Very quiet floor, comfortable bedding, and friendly staff. Will definitely book again.']
            ];
            foreach($testimonials as $t): ?>
            <div class="testimonial-card bg-gray-50 p-8 shadow-sm">
                <div class="flex items-center space-x-1 text-yellow-400 mb-4">
                    <?php for($i=0;$i<5;$i++): ?><i class="fa-solid fa-star"></i><?php endfor; ?>
                </div>
                <p class="text-gray-600 mb-6 italic">"<?php echo $t[3]; ?>"</p>
                <div class="flex items-center space-x-3">
                    <img src="<?php echo $t[2]; ?>" alt="<?php echo $t[0]; ?>" class="w-12 h-12 rounded-full object-cover">
                    <div><h4 class="font-semibold"><?php echo $t[0]; ?></h4><p class="text-sm text-gray-500"><?php echo $t[1]; ?></p></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="py-16 bg-blue-600 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <?php
            $stats = [
                ['fa-hotel', '120+', 'Rooms'], ['fa-users', '5000+', 'Happy Guests'],
                ['fa-calendar-check', '10+', 'Years Experience'], ['fa-award', '50+', 'Awards']
            ];
            foreach($stats as $s): ?>
            <div class="stat-card">
                <i class="fas <?php echo $s[0]; ?> text-4xl mb-3"></i>
                <h3 class="text-4xl font-bold mb-1"><?php echo $s[1]; ?></h3>
                <p class="text-blue-200"><?php echo $s[2]; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
