<?php
define('PAGE_TITLE', 'Our Rooms');
require_once 'config/db.php';

$checkIn = isset($_GET['check_in']) ? sanitize($_GET['check_in']) : '';
$checkOut = isset($_GET['check_out']) ? sanitize($_GET['check_out']) : '';
$guests = isset($_GET['guests']) ? (int)$_GET['guests'] : 0;
$typeId = isset($_GET['type']) ? (int)$_GET['type'] : 0;
$floorId = isset($_GET['floor']) ? (int)$_GET['floor'] : 0;

$sql = "SELECT r.*, rt.type_name, rt.price_per_night, rt.bed_type, rt.room_size, rt.max_capacity, f.floor_name,
    (SELECT image_path FROM room_images WHERE room_id = r.room_id LIMIT 1) AS image
    FROM rooms r
    JOIN room_types rt ON r.type_id = rt.type_id
    JOIN floors f ON r.floor_id = f.floor_id
    WHERE r.status = 'Available'";
$params = [];

if ($checkIn && $checkOut) {
    $sql .= " AND NOT EXISTS (
        SELECT 1 FROM reservations
        WHERE room_id = r.room_id
        AND booking_status NOT IN ('Cancelled', 'Checked Out', 'Completed', 'Rejected')
        AND check_in_date < ? AND check_out_date > ?
    )";
    $params[] = $checkOut;
    $params[] = $checkIn;
}
if ($typeId > 0) { $sql .= " AND r.type_id = ?"; $params[] = $typeId; }
if ($floorId > 0) { $sql .= " AND r.floor_id = ?"; $params[] = $floorId; }
if ($guests > 0) { $sql .= " AND rt.max_capacity >= ?"; $params[] = $guests; }

$stmt = $pdo->prepare($sql . " ORDER BY rt.price_per_night ASC");
$stmt->execute($params);
$rooms = $stmt->fetchAll();

$types = $pdo->query("SELECT * FROM room_types ORDER BY type_name")->fetchAll();
$floors = $pdo->query("SELECT * FROM floors ORDER BY sort_order")->fetchAll();
include 'includes/header.php';
?>

<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="text-blue-600 font-semibold tracking-wider uppercase text-sm">Accommodation</span>
            <h1 class="font-[Playfair_Display] text-5xl font-bold mt-2">Our Rooms</h1>
        </div>

        <!-- Filter -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-10">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Check In</label>
                    <input type="date" name="check_in" value="<?php echo $checkIn; ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Check Out</label>
                    <input type="date" name="check_out" value="<?php echo $checkOut; ?>" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Guests</label>
                    <select name="guests" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                        <option value="">Any</option>
                        <?php for($i=1;$i<=5;$i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $guests==$i?'selected':''; ?>><?php echo $i; ?> Guest<?php echo $i>1?'s':''; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Room Type</label>
                    <select name="type" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm">
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
            <?php foreach($rooms as $room): ?>
            <div class="room-card bg-white shadow-md">
                <div class="relative overflow-hidden">
                    <img src="<?php echo $room['image'] ?: 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=600'; ?>" alt="<?php echo $room['room_name']; ?>" class="w-full">
                    <span class="absolute top-4 right-4 bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-semibold"><?php echo formatCurrency($room['price_per_night']); ?>/night</span>
                    <span class="badge-status badge-<?php echo badgeClass($room['status']); ?> absolute top-4 left-4"><?php echo $room['status']; ?></span>
                </div>
                <div class="p-6">
                    <h3 class="font-[Playfair_Display] text-xl font-bold mb-2"><?php echo $room['room_name'] ?: 'Room '.$room['room_number']; ?></h3>
                    <div class="flex items-center space-x-4 text-sm text-gray-500 mb-4">
                        <span><i class="fas fa-bed text-blue-600 mr-1"></i><?php echo $room['bed_type']; ?></span>
                        <span><i class="fas fa-arrows-alt text-blue-600 mr-1"></i><?php echo $room['room_size']; ?></span>
                        <span><i class="fas fa-user text-blue-600 mr-1"></i>Max <?php echo $room['max_capacity']; ?></span>
                    </div>
                    <p class="text-gray-500 text-sm mb-4"><?php echo substr($room['type_name'], 0, 60); ?></p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-blue-600"><?php echo formatCurrency($room['price_per_night']); ?></span>
                        <div class="space-x-2">
                            <a href="room-details.php?id=<?php echo $room['room_id']; ?>" class="border border-blue-600 text-blue-600 px-3 py-2 rounded-lg hover:bg-blue-50 transition text-sm"><i class="fas fa-eye"></i></a>
                            <a href="reservation.php?room_id=<?php echo $room['room_id']; ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">Book Now</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
