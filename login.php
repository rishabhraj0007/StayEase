<?php
require_once 'config/db.php';
require_once 'includes/header.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>

<div class="container">
    <div class="auth-card">
        <div class="text-center mb-4">
            <i class="bi bi-house-heart-fill fs-1 text-primary"></i>
            <h2 class="mt-2">Welcome Back</h2>
            <p class="text-muted small">Login to your StayEase account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-custom"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold small">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold small">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn-primary-custom">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login
            </button>
        </form>

        <p class="text-center mt-4 mb-0 small text-muted">
            Don't have an account?
            <a href="/student-accommodation/signup.php" class="text-primary fw-semibold text-decoration-none">Sign Up</a>
        </p>

        <div class="mt-3 p-3 bg-light rounded small text-muted text-center">
            <strong>Test account:</strong><br>
            Email: test@student.com &nbsp;|&nbsp; Password: password123
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
