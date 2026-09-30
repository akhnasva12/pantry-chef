<?php
// 404.php
require_once 'includes/config.php';
http_response_code(404);
require_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>window.BASE_URL="<?= BASE_URL ?>";window.IS_LOGGED_IN=<?= isLoggedIn() ? 'true' : 'false' ?>;</script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>404 — Page Not Found | PantryChef</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap">
<link rel="stylesheet" href="<?= ASSETS ?>/css/main.css">
</head>
<body>
<?php include 'components/navbar.php'; ?>
<div class="not-found container">
  <div>
    <h1>404</h1>
    <h2>Page Not Found</h2>
    <p>Hmm, this page seems to have burned in the kitchen. 🔥<br>Let's get you back to something delicious.</p>
    <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
      <a href="<?= BASE_URL ?>index.php" class="btn btn-primary btn-lg">Back to Recipes</a>
      <a href="javascript:history.back()" class="btn btn-ghost btn-lg">Go Back</a>
    </div>
  </div>
</div>
<script src="<?= ASSETS ?>/js/main.js"></script>
</body>
</html>