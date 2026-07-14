<?php
define('PAGE_TITLE', 'Our Rooms');
require_once 'config/db.php';

$checkIn = isset($_GET['check_in']) ? sanitize($_GET['check_in']) : '';
$checkOut = isset($_GET['check_out']) ? sanitize($_GET['check_out']) : '';
$guests = isset($_GET['guests']) ? (int)$_GET['guests'] : 0;
$typeId = isset($_GET['type']) ? (int)$_GET['type'] : 0;
$floorId = isset($_GET['floor']) ? (int)$_GET['floor'] : 0;

$conditions = [];
$params = [];

$sql = "SELECT r.*, rt.type_name, rt.price_per_night, rt.bed_type, rt.room_size, rt.max_capacity, f.floor_name,
    (SELECT image_path FROM room_images WHERE room_id = r.room_id LIMIT 1) AS image
    FROM rooms r
    JOIN room_types rt ON r.type_id = rt.type_id
    JOIN floors f ON r.floor_id = f.floor_id";

if (isset($_SESSION['user_id'])) {
    $conditions[] = "r.status = 'Available'";
}

if ($checkIn && $checkOut) {
    $conditions[] = "NOT EXISTS (
        SELECT 1 FROM reservations
        WHERE room_id = r.room_id
        AND booking_status NOT IN ('Cancelled', 'Checked Out', 'Completed', 'Rejected')
        AND check_in_date < ? AND check_out_date > ?
    )";
    $params[] = $checkOut;
    $params[] = $checkIn;
}
if ($typeId > 0) { $conditions[] = "r.type_id = ?"; $params[] = $typeId; }
if ($floorId > 0) { $conditions[] = "r.floor_id = ?"; $params[] = $floorId; }
if ($guests > 0) { $conditions[] = "rt.max_capacity >= ?"; $params[] = $guests; }

if (!empty($conditions)) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

$stmt = $pdo->prepare($sql . " ORDER BY rt.price_per_night ASC");
$stmt->execute($params);
$rooms = $stmt->fetchAll();

$types = $pdo->query("SELECT * FROM room_types ORDER BY type_name")->fetchAll();
$floors = $pdo->query("SELECT * FROM floors ORDER BY sort_order")->fetchAll();
include 'includes/header.php';
?>

<section class="py-10 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <span class="text-amber-500 font-semibold tracking-wider uppercase text-sm">Accommodation</span>
            <h1 class="font-[Playfair_Display] text-5xl font-bold mt-2">Our Rooms</h1>
        </div>

        <!-- Filter -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-10">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Check In</label>
                    <input type="date" name="check_in" value="<?php echo $checkIn; ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Check Out</label>
                    <input type="date" name="check_out" value="<?php echo $checkOut; ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Guests</label>
                    <select name="guests" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none text-sm">
                        <option value="">Any</option>
                        <?php for($i=1;$i<=5;$i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $guests==$i?'selected':''; ?>><?php echo $i; ?> Guest<?php echo $i>1?'s':''; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Room Type</label>
                    <select name="type" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none text-sm">
                        <option value="">All Types</option>
                        <?php foreach($types as $t): ?>
                        <option value="<?php echo $t['type_id']; ?>" <?php echo $typeId==$t['type_id']?'selected':''; ?>><?php echo $t['type_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit" class="btn-primary flex-1"><i class="fas fa-search mr-2"></i>Search</button>
                    <a href="rooms.php" class="px-4 py-2.5 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50"><i class="fas fa-redo"></i></a>
                </div>
            </form>
        </div>

        <!-- Results -->
        <?php if (empty($rooms)): ?>
        <div class="text-center py-16 bg-white rounded-xl">
            <i class="fas fa-bed text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-2xl font-bold text-gray-400 mb-2">No Rooms Found</h3>
            <p class="text-gray-500">Try adjusting your search criteria</p>
        </div>
        <?php else: ?>
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
            <?php foreach($rooms as $room): ?>
            <div class="room-card bg-white shadow-md flex flex-col">
                <div class="relative overflow-hidden">
                    <img src="<?php echo $room['image'] ?: ($roomTypeImages[$room['type_name']] ?? 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=600'); ?>" alt="<?php echo $room['room_name']; ?>" class="w-full">
                </div>
                <div class="p-6 flex flex-col flex-1">
                    <h3 class="font-[Playfair_Display] text-xl font-bold mb-2"><?php echo $room['room_name'] ?: 'Room '.$room['room_number']; ?></h3>
                    <div class="flex items-center space-x-4 text-sm text-gray-500 mb-3">
                        <span><i class="fas fa-bed text-amber-500 mr-1"></i><?php echo $room['bed_type']; ?></span>
                        <span><i class="fas fa-arrows-alt text-amber-500 mr-1"></i><?php echo $room['room_size']; ?></span>
                        <span><i class="fas fa-user text-amber-500 mr-1"></i>Max <?php echo $room['max_capacity']; ?></span>
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-gray-500 text-sm"><?php echo substr($room['type_name'], 0, 60); ?></p>
                        <span class="text-xl font-bold text-amber-500"><?php echo formatCurrency($room['price_per_night']); ?></span>
                    </div>
                    <div class="flex items-center justify-between mt-auto pt-4 border-t border-gray-100">
                        <a href="room-details.php?id=<?php echo $room['room_id']; ?>" class="border-2 border-amber-500 text-amber-500 px-5 py-2 rounded-lg hover:bg-amber-50 transition text-sm font-semibold">View</a>
                        <a href="reservation.php?room_id=<?php echo $room['room_id']; ?>" class="bg-amber-500 text-white px-5 py-2 rounded-lg hover:bg-amber-600 transition text-sm font-semibold">Book Now</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
