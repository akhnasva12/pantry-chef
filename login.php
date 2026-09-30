<?php
// login.php
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (isLoggedIn()) { header('Location: ' . BASE_URL . 'index.php'); exit; }

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (!$email || !$password) {
            $error = 'Please enter your email and password.';
        } else {
            $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role']      = $user['role'];
                // Redirect back to where they came from (e.g. recipe page)
                $redirectTo = $_GET['redirect'] ?? $_POST['redirect'] ?? '';
                // Validate redirect URL — only allow our own domain paths
                if ($redirectTo && strpos($redirectTo, BASE_URL) === 0) {
                    header('Location: ' . $redirectTo);
                } else {
                    header('Location: ' . BASE_URL . 'index.php');
                }
                exit;
            } else {
                $error = 'Incorrect email or password.';
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
<title>Login — PantryChef</title>
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
      <p>Welcome back!👋</p>
    </div>

    <?php
    // Show friendly message if redirected from a recipe page
    $reason = $_GET['reason'] ?? '';
    if ($reason === 'recipe' && !$error):
    ?>
    <div class="alert alert--warning" style="background:#fff8e1; color:#856404; border:1px solid #ffe69c; border-radius:12px; padding:14px 18px; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
        <span style="font-size:1.3rem;">🔒</span>
        <div>
            <strong>Login to view this recipe</strong>
            <p style="font-size:13px; margin-top:2px; opacity:.85;">Sign in or create a free account to see full ingredients, steps and the voice guide.</p>
        </div>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert--error">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>login.php" novalidate>
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <input type="email" id="email" name="email" class="form-input"
               value="<?= htmlspecialchars($email) ?>" placeholder="you@example.com" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input type="password" id="password" name="password" class="form-input"
               placeholder="Enter your password" required>
      </div>

      <div class="form-group">
        <a href="<?= BASE_URL ?>forgot-password.php" 
          class="auth-link" 
          style="float:right; font-size:13px; margin-top:-8px">
          Forgot password?
        </a>
      </div>

      <button type="submit" class="btn btn-primary btn-lg" style="width:100%; margin-top:8px">
        Sign In →
      </button>
    </form>

    <div class="auth-divider">— or —</div>

    <div class="auth-footer">
      Don't have an account? <a href="<?= BASE_URL ?>signup.php">Create one</a>
    </div>
    <div class="auth-footer" style="margin-top:8px">
      Are you a chef? <a href="<?= BASE_URL ?>creator-signup.php">Join as Creator</a>
    </div>
  </div>
</div>
<script src="<?= ASSETS ?>/js/main.js"></script>
</body>
</html>