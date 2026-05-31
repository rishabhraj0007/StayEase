<?php
require_once 'config/db.php';
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (!$name || !$email || !$password || !$confirm) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check if email already exists
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone) VALUES (?,?,?,?)");
            $stmt->execute([$name, $email, $hashed, $phone]);
            $success = 'Account created successfully! You can now login.';
        }
    }
}
?>

<div class="container">
    <div class="auth-card">
        <div class="text-center mb-4">
            <i class="bi bi-person-plus-fill fs-1 text-primary"></i>
            <h2 class="mt-2">Create Account</h2>
            <p class="text-muted small">Join StayEase and find your perfect PG</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-custom"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success alert-custom">
                <?= htmlspecialchars($success) ?>
                <a href="/student-accommodation/login.php" class="fw-semibold ms-1">Login now →</a>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold small">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Your full name" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold small">Email Address <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold small">Phone Number</label>
                <input type="tel" name="phone" class="form-control" placeholder="10-digit mobile number">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold small">Password <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold small">Confirm Password <span class="text-danger">*</span></label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Repeat your password" required>
            </div>
            <button type="submit" class="btn-primary-custom">
                <i class="bi bi-person-check me-2"></i>Create Account
            </button>
        </form>

        <p class="text-center mt-4 mb-0 small text-muted">
            Already have an account?
            <a href="/student-accommodation/login.php" class="text-primary fw-semibold text-decoration-none">Login</a>
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
