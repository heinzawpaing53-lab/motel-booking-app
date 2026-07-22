<?php
define('PAGE_TITLE', 'My Profile');
require_once 'config/db.php';

if (!isLoggedIn()) { redirect('login.php'); }

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $firstName = sanitize($_POST['first_name'] ?? '');
        $lastName = sanitize($_POST['last_name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $gender = sanitize($_POST['gender'] ?? '');
        $nationality = sanitize($_POST['nationality'] ?? '');
        $address = sanitize($_POST['address'] ?? '');

        if (empty($firstName) || empty($lastName)) {
            $error = 'First name and last name are required.';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, phone=?, gender=?, nationality=?, address=? WHERE user_id=?");
            $stmt->execute([$firstName, $lastName, $phone, $gender, $nationality, $address, $userId]);
            $success = 'Profile updated successfully.';
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
        }
    }
}

// Fetch bookings
$stmt = $pdo->prepare("SELECT r.*, rm.room_number, rm.room_name, rt.type_name, rt.price_per_night
    FROM reservations r
    JOIN rooms rm ON r.room_id = rm.room_id
    JOIN room_types rt ON rm.type_id = rt.type_id
    WHERE r.user_id = ? AND r.customer_hidden = 0
    ORDER BY r.created_at DESC");
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();

include 'includes/header.php';
?>

<section class="bg-stone-50 min-h-screen pb-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
        <div class="mb-6">
            <div class="flex items-center gap-4">
                <!-- Avatar -->
                <div class="relative flex-shrink-0">
                    <?php
                    $imgPath = '';
                    $hasImage = false;
                    if (!empty($user['profile_image'])) {
                        $absPath = 'uploads/' . $user['profile_image'];
                        if (file_exists($absPath)) {
                            $imgPath = SITE_URL . 'uploads/' . $user['profile_image'];
                            $hasImage = true;
                        }
                    }
                    ?>
                    <?php if ($hasImage): ?>
                        <img src="<?php echo $imgPath; ?>" alt="Profile" class="w-24 h-24 rounded-full border-4 border-white shadow-lg object-cover">
                    <?php else: ?>
                        <div class="w-24 h-24 rounded-full border-4 border-white shadow-lg bg-luxury-200 flex items-center justify-center">
                            <span class="text-3xl font-bold text-luxury-600"><?php echo strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)); ?></span>
                        </div>
                    <?php endif; ?>
                    <button onclick="document.getElementById('avatarUpload').click()" class="absolute bottom-0 right-0 w-8 h-8 bg-amber-500 hover:bg-amber-600 text-white rounded-full flex items-center justify-center shadow-md transition-colors" title="Change photo">
                        <i class="fas fa-camera text-xs"></i>
                    </button>
                    <input type="file" id="avatarUpload" accept="image/*" class="hidden">
                </div>
                <!-- Name & meta -->
                <div class="flex-1">
                    <h1 class="text-xl font-bold text-stone-900"><?php echo htmlspecialchars($user['first_name'].' '.$user['last_name']); ?></h1>
                    <p class="text-gray-500 text-sm"><?php echo htmlspecialchars($user['email']); ?></p>
                    <span class="inline-block mt-1.5 px-2.5 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-xs font-medium">Guest</span>
                </div>
            </div>
        </div>

        <?php if ($success): ?>
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- User Info Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <h2 class="text-base font-semibold text-stone-900">Personal Information</h2>
                <div id="headerActions">
                    <button onclick="toggleEditMode()" id="editToggleBtn" class="text-sm font-medium text-amber-600 hover:text-amber-700 transition-colors">
                        <i class="fas fa-pen mr-1"></i>Edit Profile
                    </button>
                </div>
            </div>
            <form method="POST" id="profileForm">
                <input type="hidden" name="action" value="update_profile">
                <div class="p-5">
                    <!-- Display Mode (default) -->
                    <div id="displayMode" class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2 flex items-start gap-3">
                            <div class="w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i class="fas fa-user text-amber-500 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-0.5">Name</p>
                                <p class="text-stone-900 font-semibold text-base"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i class="fas fa-envelope text-amber-500 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-0.5">Email</p>
                                <p class="text-stone-800 font-medium text-sm"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i class="fas fa-phone text-amber-500 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-0.5">Phone</p>
                                <p class="text-stone-800 font-medium text-sm"><?php echo htmlspecialchars($user['phone'] ?: '—'); ?></p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i class="fas fa-venus-mars text-amber-500 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-0.5">Gender</p>
                                <p class="text-stone-800 font-medium text-sm"><?php echo htmlspecialchars($user['gender'] ?: '—'); ?></p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i class="fas fa-globe text-amber-500 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-0.5">Nationality</p>
                                <p class="text-stone-800 font-medium text-sm"><?php echo htmlspecialchars($user['nationality'] ?: '—'); ?></p>
                            </div>
                        </div>
                        <div class="sm:col-span-2 flex items-start gap-3">
                            <div class="w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i class="fas fa-map-marker-alt text-amber-500 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-0.5">Address</p>
                                <p class="text-stone-800 font-medium text-sm"><?php echo htmlspecialchars($user['address'] ?: '—'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Mode (hidden by default) -->
                    <div id="editMode" class="hidden space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                                <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none transition bg-gray-50 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                                <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none transition bg-gray-50 text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled class="w-full px-4 py-2.5 border border-gray-100 rounded-xl bg-gray-50 text-gray-400 text-sm cursor-not-allowed">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none transition bg-gray-50 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                                <select name="gender" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none transition bg-gray-50 text-sm">
                                    <option value="">Select</option>
                                    <option value="Male" <?php echo ($user['gender']??'')=='Male'?'selected':''; ?>>Male</option>
                                    <option value="Female" <?php echo ($user['gender']??'')=='Female'?'selected':''; ?>>Female</option>
                                    <option value="Other" <?php echo ($user['gender']??'')=='Other'?'selected':''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nationality</label>
                            <input type="text" name="nationality" value="<?php echo htmlspecialchars($user['nationality'] ?? ''); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none transition bg-gray-50 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <textarea name="address" rows="2" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none transition bg-gray-50 text-sm"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-semibold px-6 py-2.5 rounded-xl transition-colors duration-200 text-sm">
                                <i class="fas fa-save mr-1"></i>Save Changes
                            </button>
                            <button type="button" onclick="toggleEditMode()" class="border border-gray-200 text-gray-600 hover:bg-gray-50 font-medium px-6 py-2.5 rounded-xl transition-colors duration-200 text-sm">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- My Bookings Section -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <h2 class="text-base font-semibold text-stone-900">Recent Bookings</h2>
                <a href="<?php echo SITE_URL; ?>booking-history.php" class="text-sm font-medium text-amber-600 hover:text-amber-700 transition-colors">View All <i class="fas fa-arrow-right ml-1 text-xs"></i></a>
            </div>
            <?php if (empty($bookings)): ?>
                <div class="p-8 text-center">
                    <i class="fas fa-calendar-times text-4xl text-gray-200 mb-3"></i>
                    <p class="text-gray-400 text-sm">No bookings yet</p>
                    <a href="<?php echo SITE_URL; ?>rooms.php" class="inline-block mt-3 text-sm font-medium text-amber-600 hover:text-amber-700">Browse Rooms <i class="fas fa-arrow-right ml-1 text-xs"></i></a>
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-50">
                    <?php foreach(array_slice($bookings, 0, 5) as $b): ?>
                    <a href="<?php echo SITE_URL; ?>booking-details.php?id=<?php echo $b['reservation_id']; ?>" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center">
                                <i class="fas fa-bed text-amber-500 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-stone-800"><?php echo htmlspecialchars($b['room_name'] ?: 'Room '.$b['room_number']); ?></p>
                                <p class="text-xs text-gray-400"><?php echo formatDate($b['check_in_date']); ?> — <?php echo formatDate($b['check_out_date']); ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                <?php
                                $bs = $b['booking_status'];
                                if ($bs === 'Approved' || $bs === 'Checked In') echo 'bg-emerald-50 text-emerald-700';
                                elseif ($bs === 'Pending') echo 'bg-amber-50 text-amber-700';
                                elseif ($bs === 'Rejected' || $bs === 'Cancelled') echo 'bg-red-50 text-red-600';
                                else echo 'bg-gray-100 text-gray-600';
                                ?>
                            "><?php echo $bs; ?></span>
                            <p class="text-xs text-gray-400 mt-0.5"><?php echo formatCurrency($b['total_price']); ?></p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
function toggleEditMode() {
    var display = document.getElementById('displayMode');
    var edit = document.getElementById('editMode');
    var btn = document.getElementById('editToggleBtn');
    if (edit.classList.contains('hidden')) {
        display.classList.add('hidden');
        edit.classList.remove('hidden');
        btn.innerHTML = '<i class="fas fa-times mr-1"></i>Cancel';
    } else {
        edit.classList.add('hidden');
        display.classList.remove('hidden');
        btn.innerHTML = '<i class="fas fa-pen mr-1"></i>Edit Profile';
    }
}
</script>

<script src="<?php echo SITE_URL; ?>assets/js/main.js"></script>
</body>
</html>
