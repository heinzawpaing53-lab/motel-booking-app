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

<section class="py-20 bg-gray-50 min-h-screen flex items-center">
    <div class="max-w-2xl mx-auto px-4 w-full">
        <div class="bg-white rounded-2xl shadow-sm p-8">
            <div class="text-center mb-8">
                <i class="fas fa-hotel text-blue-600 text-4xl mb-3"></i>
                <h1 class="font-[Playfair_Display] text-3xl font-bold">Create Account</h1>
                <p class="text-gray-500">Join Luxury Motel today</p>
            </div>

            <?php if ($error): ?>
            <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
            <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $success; ?> <a href="login.php" class="font-semibold underline">Login here</a></div>
            <?php endif; ?>

            <form method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">First Name *</label>
                        <input type="text" name="first_name" value="<?php echo sanitize($_POST['first_name'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Last Name *</label>
                        <input type="text" name="last_name" value="<?php echo sanitize($_POST['last_name'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Email *</label>
                    <input type="email" name="email" value="<?php echo sanitize($_POST['email'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Password *</label>
                        <input type="password" name="password" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Confirm Password *</label>
                        <input type="password" name="confirm_password" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Phone *</label>
                        <input type="text" name="phone" value="<?php echo sanitize($_POST['phone'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Gender</label>
                        <select name="gender" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="">Select</option>
                            <option value="Male" <?php echo ($_POST['gender']??'')=='Male'?'selected':''; ?>>Male</option>
                            <option value="Female" <?php echo ($_POST['gender']??'')=='Female'?'selected':''; ?>>Female</option>
                            <option value="Other" <?php echo ($_POST['gender']??'')=='Other'?'selected':''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Nationality *</label>
                    <input type="text" name="nationality" value="<?php echo sanitize($_POST['nationality'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Address *</label>
                    <textarea name="address" rows="2" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required><?php echo sanitize($_POST['address'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn-primary w-full text-lg py-3"><i class="fas fa-user-plus mr-2"></i>Create Account</button>
            </form>
            <div class="text-center mt-6">
                <p class="text-gray-500">Already have an account? <a href="login.php" class="text-blue-600 font-semibold hover:underline">Sign In</a></p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
