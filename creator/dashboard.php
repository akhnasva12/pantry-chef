<?php
// creator/dashboard.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireCreator();

$user   = currentUser();
$userId = (int)$user['id'];

// Fetch creator info
$cStmt = $pdo->prepare("SELECT * FROM creators WHERE user_id = ?");
$cStmt->execute([$userId]);
$creator = $cStmt->fetch();

// Analytics
$analyticsStmt = $pdo->prepare("
    SELECT
        COUNT(CASE WHEN r.status = 'active' THEN 1 END)  as active_recipes,
        COUNT(CASE WHEN r.status = 'draft'  THEN 1 END)  as draft_recipes,
        COUNT(r.id)                                       as total_recipes,
        COALESCE(SUM(v_count.cnt), 0)                     as total_views,
        COALESCE(SUM(f_count.cnt), 0)                     as total_favorites
    FROM recipes r
    LEFT JOIN (SELECT recipe_id, COUNT(*) as cnt FROM views   GROUP BY recipe_id) v_count ON v_count.recipe_id = r.id
    LEFT JOIN (SELECT recipe_id, COUNT(*) as cnt FROM favorites GROUP BY recipe_id) f_count ON f_count.recipe_id = r.id
    WHERE r.created_by = ? AND r.status != 'deleted'
");
$analyticsStmt->execute([$userId]);
$analytics = $analyticsStmt->fetch();

// Top recipe this month
$topStmt = $pdo->prepare("
    SELECT r.id, r.title,
           COUNT(v.id) as view_count
    FROM recipes r
    LEFT JOIN views v ON v.recipe_id = r.id AND v.viewed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    WHERE r.created_by = ? AND r.status = 'active'
    GROUP BY r.id ORDER BY view_count DESC LIMIT 1
");
$topStmt->execute([$userId]);
$topRecipe = $topStmt->fetch();

// Fetch my recipes
$recipesStmt = $pdo->prepare("
    SELECT r.id, r.title, r.image, r.cooking_time, r.budget, r.status,
           c.name as category_name, r.cuisine, r.created_at,
           (SELECT COUNT(*) FROM views    WHERE recipe_id = r.id) as view_count,
           (SELECT COUNT(*) FROM favorites WHERE recipe_id = r.id) as fav_count
    FROM recipes r
    LEFT JOIN categories c ON r.category_id = c.id
    WHERE r.created_by = ? AND r.status != 'deleted'
    ORDER BY r.created_at DESC
");
$recipesStmt->execute([$userId]);
$recipes = $recipesStmt->fetchAll();

$flashMsg = '';
if (isset($_GET['added']))  $flashMsg = 'Recipe published successfully! 🎉';
if (isset($_GET['edited'])) $flashMsg = 'Recipe updated successfully! ✓';
if (isset($_GET['deleted'])) $flashMsg = 'Recipe deleted.';
if (isset($_GET['welcome'])) $flashMsg = 'Welcome to PantryChef! Add your first recipe to get started. 🍳';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>window.BASE_URL="<?= BASE_URL ?>";window.IS_LOGGED_IN=<?= isLoggedIn() ? 'true' : 'false' ?>;</script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap">
<link rel="stylesheet" href="<?= ASSETS ?>/css/main.css">
<title>My Kitchen — PantryChef</title>
<style>
.dashboard-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 32px; }
.insight-card { background: #fff; border-radius: var(--radius); box-shadow: var(--shadow-card); padding: 22px; }
.mini-chart { display: flex; align-items: flex-end; gap: 4px; height: 48px; margin-top: 12px; }
.bar { flex: 1; background: var(--primary-light); border-radius: 4px 4px 0 0; min-height: 4px; transition: background .2s; }
.bar:hover { background: var(--primary); }
@media (max-width: 768px) { .dashboard-grid { grid-template-columns: 1fr; } }
</style>
</head>
<body>
<?php include '../components/navbar.php'; ?>

<div class="container" style="padding-top:40px; padding-bottom:80px;">

  <!-- Header -->
  <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:32px; flex-wrap:wrap; gap:16px;">
    <div style="display:flex; align-items:center; gap:16px;">
      <div class="profile-avatar" style="width:64px; height:64px; background:var(--primary-light);">
        <?php if ($creator && $creator['profile_image']): ?>
          <img src="<?= UPLOADS ?>/creators/<?= htmlspecialchars($creator['profile_image']) ?>" alt="">
        <?php else: ?>
          <span style="font-size:2rem">👨‍🍳</span>
        <?php endif; ?>
      </div>
      <div>
        <h1 style="font-family:'Syne',sans-serif; font-size:1.6rem; font-weight:800;">
          <?= htmlspecialchars($creator['chef_name'] ?? $user['name']) ?>'s Kitchen
        </h1>
        <p style="color:var(--text-muted); font-size:14px;">
          <?= htmlspecialchars($creator['speciality_cuisine'] ?? '') ?>
          <?php if ($creator['location']): ?>
            · 📍 <?= htmlspecialchars($creator['location']) ?>
          <?php endif; ?>
        </p>
      </div>
    </div>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
      <a href="<?= BASE_URL ?>creator-profile.php?id=<?= (int)$creator['id'] ?>" class="btn btn-ghost btn-sm">View Public Profile</a>
      <a href="<?= BASE_URL ?>creator/add-recipe.php" class="btn btn-primary">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add New Recipe
      </a>
    </div>
  </div>

  <?php if ($flashMsg): ?>
  <div class="alert alert--success fade-in" style="margin-bottom:24px;">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
    <?= htmlspecialchars($flashMsg) ?>
  </div>
  <?php endif; ?>

  <!-- Stat Cards -->
  <div class="stat-cards fade-in">
    <div class="stat-card">
      <div class="stat-icon stat-icon--orange">🍳</div>
      <div class="stat-info">
        <h3><?= (int)$analytics['total_recipes'] ?></h3>
        <p>Total Recipes
          <span style="font-size:11px; color:var(--success); margin-left:4px;">
            <?= (int)$analytics['active_recipes'] ?> active
          </span>
        </p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon stat-icon--blue">👁</div>
      <div class="stat-info">
        <h3><?= number_format($analytics['total_views']) ?></h3>
        <p>Total Views</p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon stat-icon--green">❤️</div>
      <div class="stat-info">
        <h3><?= number_format($analytics['total_favorites']) ?></h3>
        <p>Total Favorites</p>
      </div>
    </div>
  </div>

  <?php if ($topRecipe): ?>
  <div style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color:#fff; border-radius:var(--radius); padding:20px 24px; margin-bottom:32px; display:flex; align-items:center; gap:16px; flex-wrap:wrap;" class="fade-in">
    <span style="font-size:1.8rem;">🏆</span>
    <div>
      <p style="font-size:12px; opacity:.75; text-transform:uppercase; letter-spacing:.06em; margin-bottom:3px;">Top Recipe This Month</p>
      <p style="font-family:'Syne',sans-serif; font-weight:700; font-size:1.05rem;">
        <a href="<?= BASE_URL ?>recipe.php?id=<?= (int)$topRecipe['id'] ?>" style="color:#fff"><?= htmlspecialchars($topRecipe['title']) ?></a>
      </p>
    </div>
    <div style="margin-left:auto; text-align:center;">
      <div style="font-family:'Syne',sans-serif; font-size:1.6rem; font-weight:800;"><?= number_format($topRecipe['view_count']) ?></div>
      <div style="font-size:12px; opacity:.75;">views</div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Recipes Table -->
  <div class="section-header fade-in">
    <h2 class="section-title">My <span>Recipes</span></h2>
    <div style="display:flex; gap:8px; align-items:center;">
      <span style="font-size:13px; color:var(--text-muted);">
        <?= (int)$analytics['active_recipes'] ?> active · <?= (int)$analytics['draft_recipes'] ?> drafts
      </span>
    </div>
  </div>

  <?php if ($recipes): ?>
  <div style="overflow-x:auto; border-radius:var(--radius); box-shadow:var(--shadow-card);" class="fade-in">
    <table class="data-table">
      <thead>
        <tr>
          <th>Recipe</th>
          <th>Category</th>
          <th>Time</th>
          <th>Budget</th>
          <th>Views</th>
          <th>Favs</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recipes as $r): ?>
        <tr id="recipe-row-<?= $r['id'] ?>">
          <td>
            <div style="display:flex; align-items:center; gap:12px;">
              <img src="<?= $r['image'] 
              ? UPLOADS . '/recipes/' . htmlspecialchars($r['image']) 
              : ASSETS . '/images/placeholder.jpg' ?>"
              style="width:44px; height:44px; border-radius:8px; object-fit:cover; flex-shrink:0;" alt="">
              <div>
                <a href="<?= BASE_URL ?>recipe.php?id=<?= $r['id'] ?>" style="font-weight:600; font-size:14px; color:var(--text);">
                  <?= htmlspecialchars($r['title']) ?>
                </a>
                <p style="font-size:11px; color:var(--text-muted);"><?= date('M j, Y', strtotime($r['created_at'])) ?></p>
              </div>
            </div>
          </td>
          <td style="font-size:13px; color:var(--text-muted);"><?= htmlspecialchars($r['category_name'] ?? '—') ?></td>
          <td style="font-size:13px;">⏱ <?= (int)$r['cooking_time'] ?> min</td>
          <td style="font-size:13px;">₹<?= number_format((float)$r['budget'], 0) ?></td>
          <td style="font-size:13px;">👁 <?= number_format((int)$r['view_count']) ?></td>
          <td style="font-size:13px;">❤️ <?= number_format((int)$r['fav_count']) ?></td>
          <td>
            <span id="status-badge-<?= $r['id'] ?>"
                  class="badge-role"
                  style="<?= $r['status'] === 'active'
                    ? 'background:#dcfce7; color:#166534;'
                    : 'background:#fef3c7; color:#92400e;' ?> cursor:pointer;"
                  onclick="toggleStatus(<?= $r['id'] ?>)"
                  title="Click to toggle status">
              <?= $r['status'] === 'active' ? 'Active' : 'Draft' ?>
            </span>
          </td>
          <td>
            <div style="display:flex; gap:6px;">
              <a href="<?= BASE_URL ?>creator/edit-recipe.php?id=<?= $r['id'] ?>" class="btn btn-ghost btn-sm">✏️ Edit</a>
              <button class="btn btn-sm" style="background:#fee2e2; color:var(--danger);"
                      onclick="deleteRecipe(<?= $r['id'] ?>)">🗑 Del</button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php else: ?>
    <div class="empty-state fade-in" style="background:#fff; border-radius:var(--radius); padding:64px 20px; box-shadow:var(--shadow-card);">
      <div class="icon">🍳</div>
      <h3>No recipes yet</h3>
      <p>Start sharing your culinary creations with the world!</p>
      <a href="<?= BASE_URL ?>creator/add-recipe.php" class="btn btn-primary" style="margin-top:20px; display:inline-flex;">Add Your First Recipe →</a>
    </div>
  <?php endif; ?>

</div>

<?php include '../components/footer.php'; ?>
<div id="toast-container"></div>
<script src="<?= ASSETS ?>/js/main.js"></script>
<script>
function deleteRecipe(id) {
  if (!confirm('Delete this recipe? This cannot be undone.')) return;
  fetch('<?= BASE_URL ?>ajax/delete-recipe.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    body: JSON.stringify({ recipe_id: id })
  }).then(r => r.json()).then(data => {
    if (data.success) {
      const row = document.getElementById('recipe-row-' + id);
      row.style.opacity = '0';
      row.style.transition = 'opacity .3s';
      setTimeout(() => row.remove(), 300);
      showToast('Recipe deleted', 'success');
    } else {
      showToast(data.message || 'Error deleting recipe', 'error');
    }
  }).catch(() => showToast('Network error', 'error'));
}

function toggleStatus(id) {
  fetch('<?= BASE_URL ?>ajax/toggle-recipe-status.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    body: JSON.stringify({ recipe_id: id })
  }).then(r => r.json()).then(data => {
    if (data.success) {
      const badge = document.getElementById('status-badge-' + id);
      if (data.status === 'active') {
        badge.textContent = 'Active';
        badge.style.background = '#dcfce7';
        badge.style.color = '#166534';
      } else {
        badge.textContent = 'Draft';
        badge.style.background = '#fef3c7';
        badge.style.color = '#92400e';
      }
      showToast('Status updated to ' + data.status, 'success');
    } else {
      showToast(data.message || 'Error', 'error');
    }
  }).catch(() => showToast('Network error', 'error'));
}

document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
</script>
</body>
</html>