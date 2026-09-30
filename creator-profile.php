<?php
// creator-profile.php — Public chef profile
require_once 'includes/auth.php';
require_once 'includes/db.php';

$creatorId = (int)($_GET['id'] ?? 0);
if (!$creatorId) { header('Location: ' . BASE_URL . '404.php'); exit; }

$user   = currentUser();
$userId = $user['id'];

$stmt = $pdo->prepare("
    SELECT cr.*, u.name, u.email, u.created_at as member_since
    FROM creators cr
    JOIN users u ON cr.user_id = u.id
    WHERE cr.id = ?
");
$stmt->execute([$creatorId]);
$chef = $stmt->fetch();
if (!$chef) { header('Location: ' . BASE_URL . '404.php'); exit; }

// Fetch chef's recipes
$recipesStmt = $pdo->prepare("
    SELECT r.id, r.title, r.image, r.cooking_time, r.budget, c.name as category_name,
           COALESCE(cr.chef_name, u.name) as chef_name,
           " . ($userId ? "CASE WHEN f.id IS NOT NULL THEN 1 ELSE 0 END" : "0") . " as is_favorited
    FROM recipes r
    LEFT JOIN categories c ON r.category_id = c.id
    LEFT JOIN users u ON r.created_by = u.id
    LEFT JOIN creators cr ON u.id = cr.user_id
    " . ($userId ? "LEFT JOIN favorites f ON f.recipe_id = r.id AND f.user_id = $userId" : "") . "
    WHERE r.created_by = ? AND r.status = 'active'
    ORDER BY r.created_at DESC
");
$recipesStmt->execute([$chef['user_id']]);
$recipes = $recipesStmt->fetchAll();

$profileImg = $chef['profile_image'] ? UPLOADS . '/creators/' . $chef['profile_image'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>window.BASE_URL="<?= BASE_URL ?>";window.IS_LOGGED_IN=<?= isLoggedIn() ? 'true' : 'false' ?>;</script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($chef['chef_name']) ?> — PantryChef Creator</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap">
<link rel="stylesheet" href="<?= ASSETS ?>/css/main.css">
</head>
<body>
<?php include 'components/navbar.php'; ?>

<!-- Chef Hero Banner -->
<div style="background:linear-gradient(135deg,#1a1a1a,#2d1a0f); padding:60px 0;">
  <div class="container" style="display:flex; align-items:center; gap:32px; flex-wrap:wrap;">
    <div class="profile-avatar" style="width:100px; height:100px; border:3px solid rgba(31, 90, 16, 0.5);">
      <?php if ($profileImg): ?>
        <img src="<?= $profileImg ?>" alt="">
      <?php else: ?>
        <span style="font-size:3rem">👨‍🍳</span>
      <?php endif; ?>
    </div>
    <div style="color:#fff;">
      <h1 style="font-family:'Syne',sans-serif; font-size:2rem; margin-bottom:8px;"><?= htmlspecialchars($chef['chef_name']) ?></h1>
      <?php if ($chef['speciality_cuisine']): ?>
        <p style="color:var(--primary); font-weight:600; margin-bottom:8px;">🌍 <?= htmlspecialchars($chef['speciality_cuisine']) ?> Cuisine Specialist</p>
      <?php endif; ?>
      <div style="display:flex; gap:20px; flex-wrap:wrap;">
        <?php if ($chef['location']): ?>
          <span style="color:rgba(255,255,255,.65); font-size:14px;">📍 <?= htmlspecialchars($chef['location']) ?></span>
        <?php endif; ?>
        <?php if ($chef['experience_years']): ?>
          <span style="color:rgba(255,255,255,.65); font-size:14px;">⭐ <?= (int)$chef['experience_years'] ?> years experience</span>
        <?php endif; ?>
        <span style="color:rgba(255,255,255,.65); font-size:14px;">🍳 <?= count($recipes) ?> recipes</span>
      </div>
    </div>
    <?php if ($chef['social_link']): ?>
      <div style="margin-left:auto;">
        <a href="<?= htmlspecialchars($chef['social_link']) ?>" target="_blank" rel="noopener"
           class="btn btn-outline" style="border-color:rgba(255,255,255,.3); color:#fff;">
          🔗 Follow
        </a>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="container" style="padding-top:48px; padding-bottom:80px;">
  <?php if ($chef['bio']): ?>
  <div style="background:#fff; border-radius:16px; padding:28px; box-shadow:var(--shadow-card); margin-bottom:40px; max-width:700px;">
    <h3 style="font-family:'Syne',sans-serif; margin-bottom:12px;">About <?= htmlspecialchars($chef['chef_name']) ?></h3>
    <p style="color:var(--text-muted); line-height:1.8;"><?= nl2br(htmlspecialchars($chef['bio'])) ?></p>
  </div>
  <?php endif; ?>

  <div class="section-header">
    <h2 class="section-title">Recipes by <span><?= htmlspecialchars($chef['chef_name']) ?></span></h2>
  </div>

  <?php if ($recipes): ?>
  <div class="recipes-grid">
    <?php foreach ($recipes as $recipe): ?>
      <?php include 'components/recipe-card.php'; ?>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="empty-state"><div class="icon">🍳</div><h3>No recipes yet</h3><p>Check back soon!</p></div>
  <?php endif; ?>
</div>

<?php include 'components/footer.php'; ?>
<div id="toast-container"></div>
<script src="<?= ASSETS ?>/js/main.js"></script>
<script>document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));</script>
</body>
</html>