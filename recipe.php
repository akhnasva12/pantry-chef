<?php
// recipe.php — Recipe Detail
require_once 'includes/auth.php';
require_once 'includes/db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . BASE_URL . '404.php'); exit; }

// Guest gate — must be logged in to view recipe details
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'login.php?redirect=' . urlencode(BASE_URL . 'recipe.php?id=' . $id) . '&reason=recipe');
    exit;
}

$user = currentUser();
$userId = $user['id'];

$stmt = $pdo->prepare("
    SELECT r.*, c.name as category_name,
           u.name as user_name, u.id as user_id, cr.id as chef_id,
           cr.chef_name, cr.bio as chef_bio, cr.profile_image as chef_photo,
           cr.location, cr.speciality_cuisine, cr.social_link, cr.experience_years,
           " . ($userId ? "CASE WHEN f.id IS NOT NULL THEN 1 ELSE 0 END" : "0") . " as is_favorited,
           (SELECT COUNT(*) FROM favorites WHERE recipe_id = r.id) as fav_count,
           (SELECT COUNT(*) FROM views WHERE recipe_id = r.id) as view_count
    FROM recipes r
    LEFT JOIN categories c ON r.category_id = c.id
    LEFT JOIN users u ON r.created_by = u.id
    LEFT JOIN creators cr ON u.id = cr.user_id
    " . ($userId ? "LEFT JOIN favorites f ON f.recipe_id = r.id AND f.user_id = $userId" : "") . "
    WHERE r.id = ? AND r.status = 'active'
");
$stmt->execute([$id]);
$recipe = $stmt->fetch();
if (!$recipe) { header('Location: ' . BASE_URL . '404.php'); exit; }

// Record view
$pdo->prepare("INSERT INTO views (recipe_id, user_id, ip_address) VALUES (?, ?, ?)")
    ->execute([$id, $userId ?: null, $_SERVER['REMOTE_ADDR']]);

// Record history (for logged-in users)
if ($userId) {
    $pdo->prepare("INSERT INTO history (user_id, recipe_id) VALUES (?, ?)")->execute([$userId, $id]);
}

// Fetch ingredients
$ingStmt = $pdo->prepare("SELECT ingredient_name, quantity FROM ingredients WHERE recipe_id = ? ORDER BY sort_order");
$ingStmt->execute([$id]);
$ingredients = $ingStmt->fetchAll();

// Fetch steps
$stepStmt = $pdo->prepare("SELECT step_number, description FROM steps WHERE recipe_id = ? ORDER BY step_number");
$stepStmt->execute([$id]);
$steps = $stepStmt->fetchAll();

$chefId  = $recipe['chef_id'] ?? 0;
$chefName  = $recipe['chef_name'] ?? $recipe['user_name'];
$chefPhoto = $recipe['chef_photo'] ? UPLOADS . '/creators/' . $recipe['chef_photo'] : null;
$recipeImg = $recipe['image'] ? UPLOADS . '/recipes/' . $recipe['image'] : ASSETS . '/images/placeholder.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>window.BASE_URL="<?= BASE_URL ?>";window.IS_LOGGED_IN=<?= isLoggedIn() ? 'true' : 'false' ?>;</script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($recipe['title']) ?> — PantryChef</title>
<meta name="description" content="<?= htmlspecialchars(substr($recipe['description'] ?? '', 0, 160)) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap">
<link rel="stylesheet" href="<?= ASSETS ?>/css/main.css">
</head>
<body>
<?php include 'components/navbar.php'; ?>

<!-- RECIPE HERO -->
<div class="recipe-hero">
  <img src="<?= $recipeImg ?>" alt="<?= htmlspecialchars($recipe['title']) ?>">
  <div class="recipe-hero-info">
    <div class="container">
      <?php if ($recipe['category_name']): ?>
        <span style="display:inline-block; background: var(--primary); color:#fff; padding: 4px 14px; border-radius: 999px; font-size:12px; font-weight:700; margin-bottom:12px;">
          <?= htmlspecialchars($recipe['category_name']) ?>
        </span>
      <?php endif; ?>
      <h1><?= htmlspecialchars($recipe['title']) ?></h1>
      <div class="recipe-meta-row">
        <span class="recipe-badge">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          <?= (int)$recipe['cooking_time'] ?> min
        </span>
        <span class="recipe-badge">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
          ₹<?= number_format($recipe['budget'], 0) ?>
        </span>
        <?php if ($recipe['cuisine']): ?>
        <span class="recipe-badge">🌍 <?= htmlspecialchars($recipe['cuisine']) ?></span>
        <?php endif; ?>
        <span class="recipe-badge">❤️ <?= (int)$recipe['fav_count'] ?></span>
        <span class="recipe-badge">👁 <?= (int)$recipe['view_count'] ?></span>
      </div>
    </div>
  </div>
</div>

<!-- RECIPE BODY -->
<div class="recipe-body">

  <!-- Chef Info -->
  <div style="display:flex; align-items:center; gap:16px; margin-bottom:32px; padding:20px; background:#fff; border-radius:16px; box-shadow: var(--shadow-card);">
    <div class="profile-avatar" style="width:60px; height:60px;">
      <?php if ($chefPhoto): ?>
        <img src="<?= $chefPhoto ?>" alt="<?= htmlspecialchars($chefName) ?>">
      <?php else: ?>
        <span>👨‍🍳</span>
      <?php endif; ?>
    </div>
    <div style="flex:1;">
      <a href="<?= BASE_URL . 'creator-profile.php?id=' . $chefId ?>">
      <p style="font-size:12px; color:var(--text-muted);">Recipe by</p>
      <h3 style="font-family:'Syne',sans-serif; font-size:1.1rem;"><?= htmlspecialchars($chefName) ?></h3>
      <?php if ($recipe['speciality_cuisine']): ?>
        <p style="font-size:13px; color:var(--text-muted);">Speciality: <?= htmlspecialchars($recipe['speciality_cuisine']) ?></p>
      <?php endif; ?>
      </a>
    </div>
    <button class="recipe-card__fav <?= $recipe['is_favorited'] ? 'is-active' : '' ?>"
            style="position:static; transform:scale(1.2);"
            onclick="toggleFavorite(event, <?= $id ?>)"
            aria-label="Favorite">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="<?= $recipe['is_favorited'] ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
      </svg>
    </button>
  </div>

  <?php if ($recipe['description']): ?>
  <p style="color:var(--text-muted); line-height:1.8; margin-bottom:40px; font-size:1.05rem;">
    <?= nl2br(htmlspecialchars($recipe['description'])) ?>
  </p>
  <?php endif; ?>

  <div class="recipe-layout">
    <!-- Ingredients -->
    <div class="ingredients-card fade-in">
      <h3>🥬 Ingredients</h3>
      <?php foreach ($ingredients as $ing): ?>
      <div class="ingredient-item">
        <span class="ingredient-dot"></span>
        <span>
          <?= htmlspecialchars($ing['ingredient_name']) ?>
          <?php if ($ing['quantity']): ?>
            <span style="color:var(--text-muted);">— <?= htmlspecialchars($ing['quantity']) ?></span>
          <?php endif; ?>
        </span>
      </div>
      <?php endforeach; ?>
      <?php if (empty($ingredients)): ?>
        <p style="color:var(--text-muted); font-size:14px;">No ingredients listed.</p>
      <?php endif; ?>
    </div>

    <!-- Steps -->
    <div>
      <?php if ($steps): ?>
      <!-- Voice Guide -->
      <div class="voice-controls fade-in">
        <span style="font-size:1.2rem">🎙️</span>
        <h4>Voice Guide</h4>
        <button class="voice-btn voice-btn--play" onclick="playVoice()">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          Play
        </button>
        <button class="voice-btn voice-btn--stop" onclick="stopVoice()">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
          Stop
        </button>
        <span style="font-size:13px; color:var(--text-muted);"><?= count($steps) ?> steps</span>
      </div>
      <?php endif; ?>

      <div class="steps-list">
        <?php foreach ($steps as $step): ?>
        <div class="step-card fade-in">
          <div class="step-number"><?= (int)$step['step_number'] ?></div>
          <p class="step-text"><?= nl2br(htmlspecialchars($step['description'])) ?></p>
        </div>
        <?php endforeach; ?>
        <?php if (empty($steps)): ?>
          <p style="color:var(--text-muted);">No steps provided yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include 'components/footer.php'; ?>
<div id="toast-container"></div>
<script src="<?= ASSETS ?>/js/main.js"></script>
<script>
// Pass steps to voice guide
initVoiceGuide([
  <?php foreach ($steps as $s): ?>
    <?= json_encode($s['description']) ?>,
  <?php endforeach; ?>
]);
// Trigger fade-in
document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
</script>
</body>
</html>