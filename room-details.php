<?php
define('PAGE_TITLE', 'Room Details');
require_once 'config/db.php';

$roomId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$roomId) { header("Location: rooms.php"); exit(); }

$stmt = $pdo->prepare("SELECT r.*, rt.type_name, rt.description AS type_desc, rt.price_per_night, rt.bed_type, rt.room_size, rt.max_capacity, f.floor_name, f.floor_id
    FROM rooms r JOIN room_types rt ON r.type_id = rt.type_id JOIN floors f ON r.floor_id = f.floor_id WHERE r.room_id = ?");
$stmt->execute([$roomId]);
$room = $stmt->fetch();
if (!$room) { header("Location: rooms.php"); exit(); }

$images = $pdo->prepare("SELECT * FROM room_images WHERE room_id = ?");
$images->execute([$roomId]);
$roomImages = $images->fetchAll();

$amenities = $pdo->prepare("SELECT a.* FROM amenities a JOIN room_amenities ra ON a.amenity_id = ra.amenity_id WHERE ra.room_id = ?");
$amenities->execute([$roomId]);
$roomAmenities = $amenities->fetchAll();

include 'includes/header.php';
?>
<style>
    .btn-primary, a.btn-primary, button.btn-primary {
        background: linear-gradient(135deg, #C8A96A, #A68B5B) !important;
        color: #2C1810 !important;
        border: none !important;
        font-weight: 600;
    }
    .btn-primary:hover, a.btn-primary:hover, button.btn-primary:hover {
        background: linear-gradient(135deg, #A68B5B, #8B7548) !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(200,169,106,0.3) !important;
    }
</style>

<section class="py-10 bg-luxury-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <div>
                <!-- Main Gallery -->
                <div class="relative rounded-xl overflow-hidden shadow-lg" id="galleryMain">
                    <img src="<?php echo $roomImages[0]['image_path'] ?? 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=800'; ?>" alt="<?php echo $room['room_name']; ?>" class="w-full h-96 object-cover transition-opacity duration-500" id="galleryHero">
                    <button onclick="galleryPrev()" class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 bg-luxury-900/50 hover:bg-luxury-900/70 text-luxury-100 rounded-full flex items-center justify-center transition"><i class="fas fa-chevron-left"></i></button>
                    <button onclick="galleryNext()" class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 bg-luxury-900/50 hover:bg-luxury-900/70 text-luxury-100 rounded-full flex items-center justify-center transition"><i class="fas fa-chevron-right"></i></button>
                    <button onclick="openLightbox()" class="absolute top-3 right-3 w-10 h-10 bg-luxury-900/50 hover:bg-luxury-900/70 text-luxury-100 rounded-full flex items-center justify-center transition"><i class="fas fa-expand"></i></button>
                </div>
                <div class="flex flex-row flex-wrap justify-center gap-2 mt-3 pb-2 w-full" id="galleryThumbs">
                    <?php
                    $fallbackImages = [
                        'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=800',
                        'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=800',
                        'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=800',
                        'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800',
                        'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=800'
                    ];
                    $allImages = [];
                    if (!empty($roomImages)) {
                        foreach ($roomImages as $ri) { $allImages[] = $ri['image_path']; }
                    }
                    if (count($allImages) < 4) {
                        foreach ($fallbackImages as $fi) {
                            if (count($allImages) >= 5) break;
                            $allImages[] = $fi;
                        }
                    }
                    foreach ($allImages as $idx => $imgSrc): ?>
                    <img src="<?php echo $imgSrc; ?>" class="flex-none w-20 h-16 rounded-lg object-cover cursor-pointer border-2 transition <?php echo $idx === 0 ? 'border-luxury-400' : 'border-transparent hover:border-luxury-600'; ?>" data-index="<?php echo $idx; ?>" onclick="galleryGoTo(<?php echo $idx; ?>)">
                    <?php endforeach; ?>
                </div>
                <div id="lightboxModal" class="fixed inset-0 z-50 hidden bg-luxury-900/90 flex items-center justify-center" onclick="closeLightbox(event)">
                    <button onclick="closeLightbox(event)" class="absolute top-4 right-4 w-10 h-10 bg-luxury-700/50 hover:bg-luxury-700/70 text-luxury-100 rounded-full flex items-center justify-center transition z-10"><i class="fas fa-times"></i></button>
                    <button onclick="lightboxPrev(event)" class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-luxury-700/50 hover:bg-luxury-700/70 text-luxury-100 rounded-full flex items-center justify-center transition z-10"><i class="fas fa-chevron-left"></i></button>
                    <button onclick="lightboxNext(event)" class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-luxury-700/50 hover:bg-luxury-700/70 text-luxury-100 rounded-full flex items-center justify-center transition z-10"><i class="fas fa-chevron-right"></i></button>
                    <img src="" alt="Full View" class="max-w-[90vw] max-h-[85vh] object-contain rounded-lg" id="lightboxImg">
                </div>
            </div>
            <div class="text-center">
                <span class="text-luxury-400 font-semibold tracking-wider uppercase text-sm"><?php echo $room['type_name']; ?></span>
                <h1 class="font-[Playfair_Display] text-4xl font-bold mt-2 mb-4 text-luxury-900"><?php echo $room['room_name'] ?: 'Room '.$room['room_number']; ?></h1>
                <div class="flex items-center justify-center space-x-6 text-sm text-luxury-600 mb-6">
                    <span><i class="fas fa-bed text-luxury-400 mr-1"></i><?php echo $room['bed_type']; ?></span>
                    <span><i class="fas fa-arrows-alt text-luxury-400 mr-1"></i><?php echo $room['room_size']; ?></span>
                    <span><i class="fas fa-user text-luxury-400 mr-1"></i>Max <?php echo $room['max_capacity']; ?> Guests</span>
                    <span><i class="fas fa-layer-group text-luxury-400 mr-1"></i><?php echo $room['floor_name']; ?></span>
                </div>
                <p class="text-luxury-600 leading-relaxed mb-6"><?php echo $room['type_desc'] ?: $room['description']; ?></p>
                <div class="text-4xl font-bold text-luxury-400 mb-8"><?php echo formatCurrency($room['price_per_night']); ?> <span class="text-lg text-luxury-300 font-normal">/night</span></div>

                <?php if($roomAmenities): ?>
                <h3 class="font-semibold text-lg mb-4 text-luxury-900">Amenities</h3>
                <div class="grid grid-cols-2 gap-3 mb-8">
                    <?php foreach($roomAmenities as $a): ?>
                    <div class="flex items-center space-x-2"><i class="fas fa-check-circle text-success"></i><span class="text-luxury-800"><?php echo $a['amenity_name']; ?></span></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <a href="reservation.php?room_id=<?php echo $room['room_id']; ?>" class="btn-primary text-lg px-8 py-3"><i class="fas fa-calendar-check mr-2"></i>Book This Room</a>
            </div>
        </div>
    </div>
</section>

<script>
(function() {
    var hero = document.getElementById('galleryHero');
    var thumbs = document.querySelectorAll('#galleryThumbs img');
    var lightboxImg = document.getElementById('lightboxImg');
    var lightboxModal = document.getElementById('lightboxModal');
    var sources = [];
    thumbs.forEach(function(t) { sources.push(t.src); });
    var current = 0;
    var autoTimer = null;

    function showSlide(i) {
        current = (i + sources.length) % sources.length;
        hero.style.opacity = '0';
        setTimeout(function() {
            hero.src = sources[current];
            hero.style.opacity = '1';
        }, 250);
        thumbs.forEach(function(t, idx) {
            t.className = t.className.replace('border-luxury-400', 'border-transparent');
            if (idx === current) {
                t.className = t.className.replace('border-transparent', 'border-luxury-400');
            }
        });
    }

    window.galleryGoTo = function(i) { showSlide(i); resetAuto(); };
    window.galleryNext = function() { showSlide(current + 1); resetAuto(); };
    window.galleryPrev = function() { showSlide(current - 1); resetAuto(); };

    function resetAuto() {
        clearInterval(autoTimer);
        autoTimer = setInterval(function() { showSlide(current + 1); }, 4000);
    }

    window.openLightbox = function() {
        lightboxImg.src = sources[current];
        lightboxModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    window.closeLightbox = function(e) {
        if (e.target === lightboxModal || e.currentTarget === e.target) {
            lightboxModal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    };

    window.lightboxNext = function(e) {
        e.stopPropagation();
        current = (current + 1) % sources.length;
        lightboxImg.src = sources[current];
        hero.src = sources[current];
        thumbs.forEach(function(t, idx) {
            t.className = t.className.replace('border-luxury-400', 'border-transparent');
            if (idx === current) t.className = t.className.replace('border-transparent', 'border-luxury-400');
        });
    };

    window.lightboxPrev = function(e) {
        e.stopPropagation();
        current = (current - 1 + sources.length) % sources.length;
        lightboxImg.src = sources[current];
        hero.src = sources[current];
        thumbs.forEach(function(t, idx) {
            t.className = t.className.replace('border-luxury-400', 'border-transparent');
            if (idx === current) t.className = t.className.replace('border-transparent', 'border-luxury-400');
        });
    };

    resetAuto();
})();
</script>

<?php include 'includes/footer.php'; ?>
