<?php
// signup.php — User registration
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (isLoggedIn()) { header('Location: ' . BASE_URL . 'index.php'); exit; }

$errors = [];
$formData = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        $formData = ['name' => $name, 'email' => $email];

        if (!$name)               $errors[] = 'Full name is required.';
        if (!validateEmail($email)) $errors[] = 'Please enter a valid email.';
        if (!validatePassword($password)) $errors[] = 'Password must be at least 8 characters with letters and numbers.';
        if ($password !== $confirm) $errors[] = 'Passwords do not match.';

        if (!$errors) {
            // Check email uniqueness
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'This email is already registered. <a href="<?= BASE_URL ?>login.php">Login instead</a>';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
                $stmt->execute([$name, $email, $hash]);
                $userId = $pdo->lastInsertId();
                session_regenerate_id(true);
                $_SESSION['user_id']   = $userId;
                $_SESSION['user_name'] = $name;
                $_SESSION['role']      = 'user';
                header('Location: ' . BASE_URL . 'index.php?welcome=1');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>window.BASE_URL="<?= BASE_URL ?>";window.IS_LOGGED_IN=<?= isLoggedIn() ? 'true' : 'false' ?>;</script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign Up — PantryChef</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap">
<link rel="stylesheet" href="<?= ASSETS ?>/css/main.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <a href="<?= BASE_URL ?>index.php" class="nav-logo">
        <span class="logo-icon">🍳</span>
        <span class="logo-text">Pantry<strong>Chef</strong></span>
      </a>
      <p>Join thousands of food lovers</p>
    </div>

    <?php foreach ($errors as $e): ?>
    <div class="alert alert--error">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= $e ?>
    </div>
    <?php endforeach; ?>

    <form method="POST" action="<?= BASE_URL ?>signup.php" novalidate>
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

      <div class="form-group">
        <label class="form-label" for="name">Full Name</label>
        <input type="text" id="name" name="name" class="form-input"
               value="<?= htmlspecialchars($formData['name']) ?>"
               placeholder="John Doe" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <input type="email" id="email" name="email" class="form-input"
               value="<?= htmlspecialchars($formData['email']) ?>"
               placeholder="you@example.com" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input type="password" id="password" name="password" class="form-input"
               placeholder="Min 8 chars, letters + numbers" required>
        <p class="form-hint">Minimum 8 characters with at least one letter and one number.</p>
      </div>

      <div class="form-group">
        <label class="form-label" for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" class="form-input"
               placeholder="Repeat your password" required>
      </div>

      <button type="submit" class="btn btn-primary btn-lg" style="width:100%; margin-top:8px">
        Create Account →
      </button>
    </form>

    <div class="auth-footer" style="margin-top:20px">
      Already have an account? <a href="<?= BASE_URL ?>login.php">Login</a>
    </div>
    <div class="auth-footer" style="margin-top:8px">
      Want to share recipes? <a href="<?= BASE_URL ?>creator-signup.php">Join as Creator Chef</a>
    </div>
  </div>
</div>
<script src="<?= ASSETS ?>/js/main.js"></script>
</body>
</html>