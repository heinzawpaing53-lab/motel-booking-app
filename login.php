<?php
define('PAGE_TITLE', 'Login');
require_once 'config/db.php';

if (isLoggedIn()) {
    if (isAdmin()) { redirect('admin/dashboard.php'); }
    redirect('index.php');
}

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

<section class="py-10 bg-luxury-50 min-h-screen flex items-center">
    <div class="max-w-md mx-auto px-4 w-full">
        <div class="bg-luxury-800 rounded-2xl shadow-sm p-6">
            <div class="text-center mb-6">
                <i class="fas fa-hotel text-luxury-400 text-4xl mb-3"></i>
                <h1 class="font-[Playfair_Display] text-3xl font-bold text-luxury-100">Welcome Back</h1>
                <p class="text-luxury-300">Sign in to your account</p>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
            <div class="fixed inset-0 z-[100] flex items-center justify-center bg-neutral-950/60 backdrop-blur-sm modal-backdrop">
                <div class="relative z-[101] bg-[#FAF7F2] text-[#2C1810] max-w-sm w-full mx-4 p-8 rounded-2xl shadow-2xl text-center modal-card">
                    <div class="w-14 h-14 mx-auto mb-5 rounded-full bg-[#2E8B57]/10 flex items-center justify-center">
                        <i class="fas fa-check text-[#2E8B57] text-xl"></i>
                    </div>
                    <p class="font-serif text-[#2E8B57] text-xs font-semibold tracking-[0.2em] uppercase mb-4 block">Success</p>
                    <p class="font-sans text-[#2C1810] text-sm font-medium tracking-wide leading-relaxed mb-6"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
                    <button type="button" onclick="this.closest('.fixed').remove()" class="relative z-[102] pointer-events-auto cursor-pointer font-sans text-xs uppercase tracking-[0.15em] font-semibold text-[#FAF7F2] bg-[#5A3A22] hover:bg-[#3B2418] transition-all duration-300 py-3 px-8 rounded-xl">Continue to Login</button>
                </div>
            </div>
            <?php endif; ?>
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

            <form method="POST">
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-luxury-300 mb-1">Email Address</label>
                    <input type="email" name="email" value="<?php echo sanitize($_POST['email'] ?? ''); ?>" class="w-full px-4 py-3 border border-luxury-600 rounded-lg focus:ring-2 focus:ring-luxury-400 outline-none bg-luxury-700 text-luxury-100" required>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-luxury-300 mb-1">Password</label>
                    <input type="password" name="password" class="w-full px-4 py-3 border border-luxury-600 rounded-lg focus:ring-2 focus:ring-luxury-400 outline-none bg-luxury-700 text-luxury-100" required>
                </div>
                <button type="submit" class="btn-primary w-full text-lg py-3"><i class="fas fa-sign-in-alt mr-2"></i>Sign In</button>
            </form>
            <div class="text-center mt-6">
                <p class="text-luxury-300">Don't have an account? <a href="register.php" class="text-luxury-400 font-semibold hover:underline">Register</a></p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
