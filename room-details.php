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

<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <div>
                <div class="rounded-xl overflow-hidden shadow-lg mb-4">
                    <img src="<?php echo $roomImages[0]['image_path'] ?? 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=800'; ?>" alt="<?php echo $room['room_name']; ?>" class="w-full h-96 object-cover">
                </div>
                <?php if(count($roomImages) > 1): ?>
                <div class="grid grid-cols-4 gap-2">
                    <?php foreach($roomImages as $img): ?>
                    <img src="<?php echo $img['image_path']; ?>" class="rounded-lg h-24 w-full object-cover cursor-pointer hover:opacity-80 transition" onclick="this.parentElement.parentElement.previousElementSibling.querySelector('img').src=this.src">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <div>
                <span class="text-blue-600 font-semibold tracking-wider uppercase text-sm"><?php echo $room['type_name']; ?></span>
                <h1 class="font-[Playfair_Display] text-4xl font-bold mt-2 mb-4"><?php echo $room['room_name'] ?: 'Room '.$room['room_number']; ?></h1>
                <div class="flex items-center space-x-6 text-sm text-gray-500 mb-6">
                    <span><i class="fas fa-bed text-blue-600 mr-1"></i><?php echo $room['bed_type']; ?></span>
                    <span><i class="fas fa-arrows-alt text-blue-600 mr-1"></i><?php echo $room['room_size']; ?></span>
                    <span><i class="fas fa-user text-blue-600 mr-1"></i>Max <?php echo $room['max_capacity']; ?> Guests</span>
                    <span><i class="fas fa-layer-group text-blue-600 mr-1"></i><?php echo $room['floor_name']; ?></span>
                </div>
                <p class="text-gray-600 leading-relaxed mb-6"><?php echo $room['type_desc'] ?: $room['description']; ?></p>
                <div class="text-4xl font-bold text-blue-600 mb-8"><?php echo formatCurrency($room['price_per_night']); ?> <span class="text-lg text-gray-500 font-normal">/night</span></div>

                <?php if($roomAmenities): ?>
                <h3 class="font-semibold text-lg mb-4">Amenities</h3>
                <div class="grid grid-cols-2 gap-3 mb-8">
                    <?php foreach($roomAmenities as $a): ?>
                    <div class="flex items-center space-x-2"><i class="fas fa-check-circle text-green-500"></i><span><?php echo $a['amenity_name']; ?></span></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <a href="reservation.php?room_id=<?php echo $room['room_id']; ?>" class="btn-primary text-lg px-8 py-3"><i class="fas fa-calendar-check mr-2"></i>Book This Room</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
