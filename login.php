<?php
define('PAGE_TITLE', 'Login');
require_once 'config/db.php';

if (isLoggedIn()) { redirect('index.php'); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.role_id WHERE u.email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] !== 'Active') {
                $error = 'Your account has been deactivated. Contact admin.';
            } else {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['profile_image'] = $user['profile_image'] ?? 'default_user.png';
                $_SESSION['role_name'] = $user['role_name'] ?? 'User';

                logActivity($pdo, $user['user_id'], 'Login', 'User logged in');

                if (isset($_SESSION['redirect_after_login'])) {
                    $redirect = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                    redirect($redirect);
                }

                if (isset($_SESSION['pending_reservation'])) {
                    redirect('reservation.php');
                }

                if ($user['role_id'] == 1) {
                    redirect('admin/dashboard.php');
                }
                redirect('index.php');
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

include 'includes/header.php';
?>

<section class="py-20 bg-gray-50 min-h-screen flex items-center">
    <div class="max-w-md mx-auto px-4 w-full">
        <div class="bg-white rounded-2xl shadow-sm p-8">
            <div class="text-center mb-8">
                <i class="fas fa-hotel text-blue-600 text-4xl mb-3"></i>
                <h1 class="font-[Playfair_Display] text-3xl font-bold">Welcome Back</h1>
                <p class="text-gray-500">Sign in to your account</p>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
            <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 p-4 rounded-r shadow-sm font-medium mb-6"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Email Address</label>
                    <input type="email" name="email" value="<?php echo sanitize($_POST['email'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Password</label>
                    <input type="password" name="password" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>
                <button type="submit" class="btn-primary w-full text-lg py-3"><i class="fas fa-sign-in-alt mr-2"></i>Sign In</button>
            </form>
            <div class="text-center mt-6">
                <p class="text-gray-500">Don't have an account? <a href="register.php" class="text-blue-600 font-semibold hover:underline">Register</a></p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
