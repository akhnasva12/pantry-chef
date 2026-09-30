<?php
// creator-signup.php — Creator (Chef) registration
require_once 'includes/auth.php';
require_once 'includes/db.php';

// $sqltochange = ALTER TABLE creators 
// ADD COLUMN status ENUM('pending','approved','rejected') DEFAULT 'pending';

if (isLoggedIn()) { header('Location: ' . BASE_URL . 'index.php'); exit; }

$errors = [];
$formData = ['name' => '', 'email' => '', 'chef_name' => '', 'bio' => '', 'location' => '', 'speciality_cuisine' => '', 'social_link' => '', 'experience_years' => ''];

$cuisines = ['Indian','Kerala','South Indian','North Indian','Chinese','Arabic','Continental','Italian','Thai','Japanese','Mexican','Mediterranean'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request token.';
    } else {
        foreach ($formData as $k => $v) {
            $formData[$k] = trim($_POST[$k] ?? '');
        }
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (!$formData['name'])       $errors[] = 'Full name is required.';
        if (!validateEmail($formData['email'])) $errors[] = 'Valid email is required.';
        if (!validatePassword($password)) $errors[] = 'Password must be min 8 chars with letters and numbers.';
        if ($password !== $confirm)   $errors[] = 'Passwords do not match.';
        if (!$formData['chef_name'])  $errors[] = 'Chef/Brand name is required.';
        if (!$formData['bio'])        $errors[] = 'A short bio is required.';

        // Handle profile image upload
        $profileImage = null;
        if (!empty($_FILES['profile_image']['name']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $file       = $_FILES['profile_image'];
            $allowed    = ['image/jpeg', 'image/png', 'image/webp'];
            $uploadDir  = __DIR__ . '/uploads/creators/';

            // Create directory if missing
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Validate type using finfo (more reliable than $_FILES['type'])
            $finfo    = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowed)) {
                $errors[] = 'Profile image must be JPG, PNG, or WebP.';
            } elseif ($file['size'] > 2 * 1024 * 1024) {
                $errors[] = 'Profile image must be under 2MB.';
            } else {
                $ext          = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $profileImage = uniqid('chef_', true) . '.' . $ext;
                $dest         = $uploadDir . $profileImage;
                if (!move_uploaded_file($file['tmp_name'], $dest)) {
                    $errors[] = 'Upload failed. Please check folder permissions (chmod 755 uploads/creators/).';
                    $profileImage = null;
                }
            }
        }

        if (!$errors) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$formData['email']]);
            if ($stmt->fetch()) {
                $errors[] = 'Email already registered. <a href="<?= BASE_URL ?>login.php">Login instead</a>';
            } else {
                $pdo->beginTransaction();
                try {
                    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
                    $stmt->execute([$formData['name'], $formData['email'], $hash]);
                    $userId = $pdo->lastInsertId();

                    $stmt = $pdo->prepare("INSERT INTO creators (user_id, chef_name, bio, profile_image, location, speciality_cuisine, social_link, experience_years, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
                    $stmt->execute([
                        $userId,
                        $formData['chef_name'],
                        $formData['bio'],
                        $profileImage,
                        $formData['location'],
                        $formData['speciality_cuisine'],
                        $formData['social_link'],
                        (int)$formData['experience_years']
                    ]);

                    $pdo->commit();
                    session_regenerate_id(true);
                    $_SESSION['user_id']   = $userId;
                    $_SESSION['user_name'] = $formData['name'];
                    $_SESSION['role']      = 'creator';
                    header('Location: ' . BASE_URL . 'creator-request-pending.php');
                    exit;
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $errors[] = 'Registration failed. Please try again.';
                }
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
<title>Join as Creator Chef — PantryChef</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap">
<link rel="stylesheet" href="<?= ASSETS ?>/css/main.css">
</head>
<body>
<div class="auth-page" style="padding: 60px 20px;">
  <div class="auth-card auth-card--wide">
    <div class="auth-logo">
      <a href="<?= BASE_URL ?>index.php" class="nav-logo">
        <span class="logo-icon">🍳</span>
        <span class="logo-text">Pantry<strong>Chef</strong></span>
      </a>
      <p>Share your recipes with the world 🌍</p>
    </div>

    <div style="background: var(--primary-light); border: 1px solid rgba(255,107,53,.2); border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: flex-start; gap: 12px;">
      <span style="font-size: 1.5rem">👨‍🍳</span>
      <div>
        <strong>Creator Account</strong>
        <p style="font-size: 13px; color: var(--text-muted); margin-top: 2px;">Post recipes, build your brand, and reach thousands of food lovers.</p>
      </div>
    </div>

    <?php foreach ($errors as $e): ?>
    <div class="alert alert--error">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= $e ?>
    </div>
    <?php endforeach; ?>

    <form method="POST" action="<?= BASE_URL ?>creator-signup.php" enctype="multipart/form-data" novalidate>
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

      <!-- Account Details -->
      <p class="form-section-title">🔐 Account Details</p>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="name">Full Name *</label>
          <input type="text" id="name" name="name" class="form-input"
                 value="<?= htmlspecialchars($formData['name']) ?>"
                 placeholder="Your real name" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="email">Email Address *</label>
          <input type="email" id="email" name="email" class="form-input"
                 value="<?= htmlspecialchars($formData['email']) ?>"
                 placeholder="you@example.com" required>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="password">Password *</label>
          <input type="password" id="password" name="password" class="form-input"
                 placeholder="Min 8 chars" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="confirm_password">Confirm Password *</label>
          <input type="password" id="confirm_password" name="confirm_password" class="form-input"
                 placeholder="Repeat password" required>
        </div>
      </div>

      <!-- Chef Profile -->
      <p class="form-section-title">👨‍🍳 Chef Profile</p>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="chef_name">Chef / Brand Name *</label>
          <input type="text" id="chef_name" name="chef_name" class="form-input"
                 value="<?= htmlspecialchars($formData['chef_name']) ?>"
                 placeholder="Chef Mario, SpiceQueen, etc." required>
        </div>
        <div class="form-group">
          <label class="form-label" for="location">City / Location</label>
          <input type="text" id="location" name="location" class="form-input"
                 value="<?= htmlspecialchars($formData['location']) ?>"
                 placeholder="e.g. Kochi, Mumbai">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="bio">Short Bio *</label>
        <textarea id="bio" name="bio" class="form-textarea"
                  placeholder="Tell us about your cooking style and passion..." required><?= htmlspecialchars($formData['bio']) ?></textarea>
      </div>

      <div class="form-group">
        <label class="form-label">Profile Photo</label>
        <div class="upload-area">
          <input type="file" id="profile_image" name="profile_image" accept="image/*">
          <div class="upload-icon">📸</div>
          <div class="upload-text">Click to upload or drag here<br><strong>JPG, PNG or WebP · Max 2MB</strong></div>
        </div>
        <div class="upload-preview" id="profilePreview">
          <img src="" alt="Preview">
          <button type="button" class="upload-clear" id="clearProfile">×</button>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="speciality_cuisine">Speciality Cuisine</label>
          <select id="speciality_cuisine" name="speciality_cuisine" class="form-select">
            <option value="">Select cuisine...</option>
            <?php foreach ($cuisines as $c): ?>
              <option value="<?= $c ?>" <?= $formData['speciality_cuisine'] === $c ? 'selected' : '' ?>><?= $c ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="experience_years">Experience (Years)</label>
          <input type="number" id="experience_years" name="experience_years" class="form-input"
                 value="<?= (int)$formData['experience_years'] ?>"
                 min="0" max="60" placeholder="e.g. 5">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="social_link">Instagram / YouTube Link</label>
        <input type="url" id="social_link" name="social_link" class="form-input"
               value="<?= htmlspecialchars($formData['social_link']) ?>"
               placeholder="https://instagram.com/yourhandle">
      </div>

      <button type="submit" class="btn btn-primary btn-lg" style="width:100%; margin-top:16px">
        🍳 Create Creator Account →
      </button>
    </form>

    <div class="auth-footer" style="margin-top:20px">
      Just here to browse? <a href="<?= BASE_URL ?>signup.php">Create a user account</a>
    </div>
    <div class="auth-footer">
      Already registered? <a href="<?= BASE_URL ?>login.php">Login</a>
    </div>
  </div>
</div>
<script src="<?= ASSETS ?>/js/main.js"></script>
<script>
  initUploadPreview('profile_image', 'profilePreview', 'clearProfile');
</script>
</body>
</html>