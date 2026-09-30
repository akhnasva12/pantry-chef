<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT status FROM creators WHERE user_id=?");
$stmt->execute([$_SESSION['user_id']]);
$creator = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>
  window.BASE_URL="<?= BASE_URL ?>";
  window.IS_LOGGED_IN=true;
</script>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Creator Request Status — PantryChef</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Syne:wght@500;600;700&family=DM+Sans:wght@300;400;500&display=swap">
<link rel="stylesheet" href="<?= ASSETS ?>/css/main.css">

<style>
.status-card {
  background:#fff;
  border-radius:16px;
  padding:36px;
  text-align:center;
  box-shadow:var(--shadow-card);
}

.status-badge {
  display:inline-block;
  padding:6px 14px;
  border-radius:999px;
  font-size:12px;
  font-weight:600;
  margin-bottom:14px;
}

.pending  { background:#fef3c7; color:#92400e; }
.approved { background:#dcfce7; color:#166534; }
.rejected { background:#fee2e2; color:#991b1b; }

.status-title {
  font-family:'Syne',sans-serif;
  font-size:1.5rem;
  font-weight:700;
  margin-bottom:10px;
}

.status-text {
  font-size:14px;
  color:var(--text-muted);
  line-height:1.6;
  max-width:440px;
  margin:0 auto;
}
</style>

</head>
<body>

<div class="auth-page" style="padding:80px 20px;">
  <div class="auth-card auth-card--wide" style="max-width:640px;margin:auto;">

    <!-- Logo -->
    <div class="auth-logo">
      <a href="<?= BASE_URL ?>index.php" class="nav-logo">
        <span class="logo-icon">🍳</span>
        <span class="logo-text">Pantry<strong>Chef</strong></span>
      </a>
    </div>

    <div class="status-card">

      <?php if ($creator && $creator['status'] === 'pending'): ?>

        <span class="status-badge pending">Pending Review</span>
        <div class="status-title">Your request is under review</div>
        <p class="status-text">
          Thank you for applying to become a creator. Our team is currently reviewing your profile. 
          You will be notified once the review process is complete.
        </p>

      <?php elseif ($creator && $creator['status'] === 'approved'): ?>

        <span class="status-badge approved">Approved</span>
        <div class="status-title">Your creator account is active</div>
        <p class="status-text">
          Your request has been approved. You now have full access to creator features.
        </p>

        <a href="<?= BASE_URL ?>creator/dashboard.php" 
           class="btn btn-primary btn-lg" 
           style="margin-top:24px;">
          Go to Dashboard
        </a>

      <?php elseif ($creator && $creator['status'] === 'rejected'): ?>

        <span class="status-badge rejected">Not Approved</span>
        <div class="status-title">We couldn’t approve your request</div>
        <p class="status-text">
          We appreciate your interest in PantryChef. At this time, your request does not meet our review criteria.
          You may apply again later.
        </p>

        <a href="<?= BASE_URL ?>index.php" 
           class="btn btn-ghost" 
           style="margin-top:24px;">
          Back to Home
        </a>

      <?php else: ?>

        <div class="status-title">No request found</div>
        <p class="status-text">
          You have not submitted a creator request yet.
        </p>

        <a href="<?= BASE_URL ?>creator-signup.php" 
           class="btn btn-primary" 
           style="margin-top:24px;">
          Apply as Creator
        </a>

      <?php endif; ?>


      <!-- Contact Support (only for pending/rejected) -->
      <?php if ($creator && $creator['status'] !== 'approved'): ?>
        <div style="margin-top:28px;">
          <button onclick="contactSupport()" class="btn btn-ghost">
            Contact Support
          </button>
        </div>
      <?php endif; ?>

    </div>

  </div>
</div>

<script src="<?= ASSETS ?>/js/main.js"></script>

<script>
function contactSupport() {
  const email = "pantrychefweb@gmail.com"; // use your real email
  const subject = "Creator Request Inquiry";
  const body = `Hello PantryChef Team,

    I would like to inquire about my creator request.

    User ID: <?= $_SESSION['user_id'] ?>


    Thank you.`;

  const url = `https://mail.google.com/mail/?view=cm&fs=1&to=${email}&su=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;

  window.open(url, "_blank");
}
</script>

</body>
</html>