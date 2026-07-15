<?php
define('PAGE_TITLE', 'Register');
require_once 'config/db.php';

if (isLoggedIn()) { redirect('index.php'); }

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');
    $gender = sanitize($_POST['gender'] ?? '');
    $nationality = sanitize($_POST['nationality'] ?? '');
    $address = sanitize($_POST['address'] ?? '');

    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($phone) || empty($nationality) || empty($address)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Email already registered.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (role_id, first_name, last_name, email, password, phone, gender, nationality, address) VALUES (2,?,?,?,?,?,?,?,?)");
            $stmt->execute([$firstName, $lastName, $email, $hashed, $phone, $gender, $nationality, $address]);
            $_SESSION['success'] = 'Registration successful! Please login.';
            redirect('login.php');
        }
    }
}

include 'includes/header.php';
?>

<section class="py-12 bg-luxury-50 min-h-screen flex items-center">
    <div class="max-w-xl mx-auto px-6 w-full">
        <div class="bg-luxury-800 rounded-2xl p-8">
            <div class="text-center mb-8">
                <i class="fas fa-hotel text-luxury-400 text-3xl mb-3"></i>
                <h1 class="font-serif text-2xl font-semibold text-luxury-100">Create Account</h1>
                <p class="text-luxury-300 text-sm mt-1">Join Luxury Motel today</p>
            </div>

            <?php if ($error): ?>
            <div class="fixed inset-0 z-[100] flex items-center justify-center bg-luxury-900/60 backdrop-blur-sm modal-backdrop">
                <div class="relative z-[101] bg-[#FAF7F2] text-[#2C1810] max-w-md w-full mx-4 p-8 rounded-2xl shadow-2xl text-center modal-card">
                    <div class="w-14 h-14 mx-auto mb-5 rounded-full bg-[#8B2E2E]/10 flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-[#8B2E2E] text-xl"></i>
                    </div>
                    <p class="font-serif text-[#8B2E2E] text-xs font-semibold tracking-[0.2em] uppercase mb-4 block">Attention</p>
                    <p class="font-sans text-[#2C1810] text-sm font-medium tracking-wide leading-relaxed mb-6"><?php echo $error; ?></p>
                    <button type="button" onclick="this.closest('.fixed').remove()" class="relative z-[102] pointer-events-auto cursor-pointer font-sans text-xs uppercase tracking-[0.15em] font-semibold text-[#FAF7F2] bg-[#5A3A22] hover:bg-[#3B2418] transition-all duration-300 py-3 px-8 rounded-xl">Dismiss</button>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($success): ?>
            <div class="bg-success/20 border-l-4 border-success text-success p-4 rounded-r font-medium mb-6"><?php echo $success; ?> <a href="login.php" class="font-semibold underline">Login here</a></div>
            <?php endif; ?>

            <form method="POST">
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="block text-xs font-semibold text-luxury-400 mb-1 uppercase tracking-wide">First Name</label>
                        <input type="text" name="first_name" value="<?php echo sanitize($_POST['first_name'] ?? ''); ?>" class="w-full px-3 py-2.5 border border-luxury-600 rounded-lg focus:ring-2 focus:ring-luxury-400 outline-none bg-luxury-700 text-luxury-100 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-luxury-400 mb-1 uppercase tracking-wide">Last Name</label>
                        <input type="text" name="last_name" value="<?php echo sanitize($_POST['last_name'] ?? ''); ?>" class="w-full px-3 py-2.5 border border-luxury-600 rounded-lg focus:ring-2 focus:ring-luxury-400 outline-none bg-luxury-700 text-luxury-100 text-sm" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-luxury-400 mb-1 uppercase tracking-wide">Email</label>
                    <input type="email" name="email" value="<?php echo sanitize($_POST['email'] ?? ''); ?>" class="w-full px-3 py-2.5 border border-luxury-600 rounded-lg focus:ring-2 focus:ring-luxury-400 outline-none bg-luxury-700 text-luxury-100 text-sm" required>
                </div>
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="block text-xs font-semibold text-luxury-400 mb-1 uppercase tracking-wide">Password</label>
                        <input type="password" name="password" class="w-full px-3 py-2.5 border border-luxury-600 rounded-lg focus:ring-2 focus:ring-luxury-400 outline-none bg-luxury-700 text-luxury-100 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-luxury-400 mb-1 uppercase tracking-wide">Confirm Password</label>
                        <input type="password" name="confirm_password" class="w-full px-3 py-2.5 border border-luxury-600 rounded-lg focus:ring-2 focus:ring-luxury-400 outline-none bg-luxury-700 text-luxury-100 text-sm" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="block text-xs font-semibold text-luxury-400 mb-1 uppercase tracking-wide">Phone</label>
                        <input type="tel" name="phone" id="phone" oninput="this.value = this.value.replace(/[^0-9]/g, '')" placeholder="" value="<?php echo sanitize($_POST['phone'] ?? ''); ?>" class="w-full px-3 py-2.5 border border-luxury-600 rounded-lg focus:ring-2 focus:ring-luxury-400 outline-none bg-luxury-700 text-luxury-100 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-luxury-400 mb-1 uppercase tracking-wide">Gender</label>
                        <select name="gender" class="w-full px-3 py-2.5 border border-luxury-600 rounded-lg focus:ring-2 focus:ring-luxury-400 outline-none bg-luxury-700 text-luxury-100 text-sm">
                            <option value="">Select</option>
                            <option value="Male" <?php echo ($_POST['gender']??'')=='Male'?'selected':''; ?>>Male</option>
                            <option value="Female" <?php echo ($_POST['gender']??'')=='Female'?'selected':''; ?>>Female</option>
                            <option value="Other" <?php echo ($_POST['gender']??'')=='Other'?'selected':''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-luxury-400 mb-1 uppercase tracking-wide">Nationality</label>
                    <input type="text" name="nationality" value="<?php echo sanitize($_POST['nationality'] ?? ''); ?>" class="w-full px-3 py-2.5 border border-luxury-600 rounded-lg focus:ring-2 focus:ring-luxury-400 outline-none bg-luxury-700 text-luxury-100 text-sm" required>
                </div>
                <div class="mb-5">
                    <label class="block text-xs font-semibold text-luxury-400 mb-1 uppercase tracking-wide">Address</label>
                    <textarea name="address" rows="2" class="w-full px-3 py-2.5 border border-luxury-600 rounded-lg focus:ring-2 focus:ring-luxury-400 outline-none bg-luxury-700 text-luxury-100 text-sm resize-none" required><?php echo sanitize($_POST['address'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn-primary w-full py-2.5 text-sm"><i class="fas fa-user-plus mr-2"></i>Create Account</button>
            </form>
            <div class="text-center mt-5">
                <p class="text-luxury-300 text-sm">Already have an account? <a href="login.php" class="text-luxury-400 font-semibold hover:underline">Sign In</a></p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
