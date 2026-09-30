<?php
// profile.php — User Profile
require_once 'includes/auth.php';
require_once 'includes/db.php';
requireLogin();

$user   = currentUser();
$userId = (int)$user['id'];

// Full user info
$stmt = $pdo->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ?");
$stmt->execute([$userId]);
$userInfo = $stmt->fetch();

// Creator info if applicable
$creatorInfo = null;
if ($userInfo['role'] === 'creator') {
    $cs = $pdo->prepare("SELECT * FROM creators WHERE user_id = ?");
    $cs->execute([$userId]);
    $creatorInfo = $cs->fetch();
}

// Favorites count
$favCount = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?");
$favCount->execute([$userId]);
$totalFavs = (int)$favCount->fetchColumn();

// History count
$histCount = $pdo->prepare("SELECT COUNT(DISTINCT recipe_id) FROM history WHERE user_id = ?");
$histCount->execute([$userId]);
$totalHist = (int)$histCount->fetchColumn();

// Favorites
$favStmt = $pdo->prepare("
    SELECT r.id, r.title, r.image, r.cooking_time, r.budget,
           c.name as category_name,
           COALESCE(cr.chef_name, u.name) as chef_name,
           1 as is_favorited
    FROM favorites f
    JOIN recipes r ON f.recipe_id = r.id AND r.status = 'active'
    JOIN users u ON r.created_by = u.id
    LEFT JOIN creators cr ON u.id = cr.user_id
    LEFT JOIN categories c ON r.category_id = c.id
    WHERE f.user_id = ?
    ORDER BY f.created_at DESC
    LIMIT 20
");
$favStmt->execute([$userId]);
$favorites = $favStmt->fetchAll();

// History
$histStmt = $pdo->prepare("
    SELECT r.id, r.title, r.image, r.cooking_time, r.budget,
           c.name as category_name,
           COALESCE(cr.chef_name, u.name) as chef_name,
           MAX(h.viewed_at) as last_viewed,
           CASE WHEN fv.id IS NOT NULL THEN 1 ELSE 0 END as is_favorited
    FROM history h
    JOIN recipes r ON h.recipe_id = r.id AND r.status = 'active'
    JOIN users u ON r.created_by = u.id
    LEFT JOIN creators cr ON u.id = cr.user_id
    LEFT JOIN categories c ON r.category_id = c.id
    LEFT JOIN favorites fv ON fv.recipe_id = r.id AND fv.user_id = ?
    WHERE h.user_id = ?
    GROUP BY r.id
    ORDER BY last_viewed DESC
    LIMIT 20
");
$histStmt->execute([$userId, $userId]);
$history = $histStmt->fetchAll();

$activeTab = $_GET['tab'] ?? 'favorites';
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
<title>My Profile — PantryChef</title>
</head>
<body>
<?php include 'components/navbar.php'; ?>

<div class="container" style="padding-top:40px; padding-bottom:80px;">
  <div class="profile-layout">

    <!-- Sidebar -->
    <aside class="profile-sidebar">
      <div class="profile-sidebar__header">
        <div class="profile-avatar">
          <?php if ($creatorInfo && $creatorInfo['profile_image']): ?>
            <img src="<?= UPLOADS ?>/creators/<?= htmlspecialchars($creatorInfo['profile_image']) ?>" alt="">
          <?php else: ?>
            <span style="font-size:2.2rem">👤</span>
          <?php endif; ?>
        </div>
        <div class="profile-name" id="profileDisplayName"><?= htmlspecialchars($userInfo['name']) ?></div>
        <div class="profile-role"><?= ucfirst($userInfo['role']) ?> Account</div>
        <?php if ($creatorInfo): ?>
          <div style="margin-top:6px; font-size:12px; opacity:.8;">👨‍🍳 <?= htmlspecialchars($creatorInfo['chef_name']) ?></div>
        <?php endif; ?>
      </div>
      <nav class="profile-nav">
        <div class="profile-nav-item <?= $activeTab === 'favorites' ? 'is-active' : '' ?>"
             onclick="showSection('favorites')">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          My Favorites
          <span style="margin-left:auto; background:var(--primary); color:#fff; border-radius:999px; font-size:11px; padding:2px 8px;"><?= $totalFavs ?></span>
        </div>
        <div class="profile-nav-item <?= $activeTab === 'history' ? 'is-active' : '' ?>"
             onclick="showSection('history')">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-.07-8.89"/></svg>
          Viewing History
          <span id="historyCount" style="margin-left:auto; background:var(--border); color:var(--text-muted); border-radius:999px; font-size:11px; padding:2px 8px;"><?= $totalHist ?></span>
        </div>
        <div class="profile-nav-item <?= $activeTab === 'info' ? 'is-active' : '' ?>"
             onclick="showSection('info')">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Account Info
        </div>
        <?php if ($userInfo['role'] === 'creator'): ?>
        <a href="<?= BASE_URL ?>creator/dashboard.php" class="profile-nav-item" style="color:var(--primary);">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          My Kitchen
        </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>logout.php" class="profile-nav-item" style="color:var(--danger); margin-top:8px; border-top:1px solid var(--border); padding-top:14px;">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Logout
        </a>
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="profile-content">

      <!-- FAVORITES -->
      <div id="section-favorites" class="profile-section">
        <h3 class="profile-section__title">❤️ My Favorites</h3>
        <?php if ($favorites): ?>
          <div class="recipes-grid" style="grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));">
            <?php foreach ($favorites as $recipe): ?>
              <?php include 'components/recipe-card.php'; ?>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="empty-state">
            <div class="icon">💔</div>
            <h3>No favorites yet</h3>
            <p>Browse recipes and tap ❤️ to save them here.</p>
            <a href="<?= BASE_URL ?>index.php" class="btn btn-primary" style="margin-top:16px; display:inline-flex;">Explore Recipes →</a>
          </div>
        <?php endif; ?>
      </div>

      <!-- HISTORY -->
      <div id="section-history" class="profile-section" style="display:none;">
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <h3 class="profile-section__title">🕐 Viewing History</h3>

        <?php if ($history): ?>
          <button class="btn btn-outline btn-sm" onclick="clearHistory()">
            🗑 Clear History
          </button>
        <?php endif; ?>
      </div>
        <?php if ($history): ?>
          <div class="recipes-grid" style="grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));">
            <?php foreach ($history as $recipe): ?>
              <?php include 'components/recipe-card.php'; ?>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="empty-state">
            <div class="icon">👀</div>
            <h3>No history yet</h3>
            <p>Recipes you view will appear here.</p>
          </div>
        <?php endif; ?>
      </div>

      <!-- ACCOUNT INFO -->
      <div id="section-info" class="profile-section" style="display:none;">
        <h3 class="profile-section__title">👤 Account Information</h3>

        <!-- Editable Name -->
        <div style="display:grid; gap:20px; max-width:440px;">
          <div>
            <p class="form-label">Full Name</p>
            <div style="display:flex; gap:8px; align-items:center;">
              <input type="text" id="editName" class="form-input"
                     value="<?= htmlspecialchars($userInfo['name']) ?>"
                     style="flex:1;" maxlength="100">
              <button class="btn btn-primary btn-sm" onclick="saveName()">Save</button>
            </div>
          </div>
          <div>
            <p class="form-label">Email Address</p>
            <p style="font-size:1rem; padding:11px 0; color:var(--text-muted);"><?= htmlspecialchars($userInfo['email']) ?></p>
          </div>
          <div>
            <p class="form-label">Account Role</p>
            <span class="badge-role badge-role--<?= $userInfo['role'] ?>"><?= ucfirst($userInfo['role']) ?></span>
          </div>
          <div>
            <p class="form-label">Member Since</p>
            <p style="font-size:1rem;"><?= date('F j, Y', strtotime($userInfo['created_at'])) ?></p>
          </div>
          <div>
            <p class="form-label">Activity</p>
            <div style="display:flex; gap:20px; flex-wrap:wrap;">
              <div style="text-align:center; padding:16px 24px; background:var(--bg); border-radius:12px;">
                <div style="font-family:'Syne',sans-serif; font-size:1.6rem; font-weight:800; color:var(--primary);"><?= $totalFavs ?></div>
                <div style="font-size:12px; color:var(--text-muted);">Favorites</div>
              </div>
              <div style="text-align:center; padding:16px 24px; background:var(--bg); border-radius:12px;">
                <div id="historyViewedCount" style="font-family:'Syne',sans-serif; font-size:1.6rem; font-weight:800; color:var(--primary);"><?= $totalHist ?></div>
                <div style="font-size:12px; color:var(--text-muted);">Viewed</div>
              </div>
            </div>
          </div>
        </div>

        <?php if ($userInfo['role'] === 'user'): ?>
        <div style="margin-top:28px; padding:22px; background:var(--primary-light); border-radius:14px; border:1px solid rgba(255,107,53,.15);">
          <p style="font-weight:700; font-size:1rem; margin-bottom:8px;">🍳 Ready to share your recipes?</p>
          <p style="font-size:14px; color:var(--text-muted); margin-bottom:14px;">
            Join as a Creator to post your own recipes, build your brand and track views & favorites.
          </p>
          <a href="<?= BASE_URL ?>creator-signup.php" class="btn btn-primary btn-sm">Become a Creator Chef →</a>
        </div>
        <?php endif; ?>

        <?php if ($creatorInfo): ?>
        <div style="margin-top:28px; padding:22px; background:#fff; border:1px solid var(--border); border-radius:14px; box-shadow:var(--shadow-card);">
          <p style="font-weight:700; font-size:1rem; margin-bottom:14px;">👨‍🍳 Chef Profile</p>
          <div style="display:grid; gap:10px; font-size:14px; color:var(--text-muted);">
            <p><strong style="color:var(--text);">Chef Name:</strong> <?= htmlspecialchars($creatorInfo['chef_name']) ?></p>
            <p><strong style="color:var(--text);">Speciality:</strong> <?= htmlspecialchars($creatorInfo['speciality_cuisine'] ?? '—') ?></p>
            <p><strong style="color:var(--text);">Location:</strong> <?= htmlspecialchars($creatorInfo['location'] ?? '—') ?></p>
            <p><strong style="color:var(--text);">Experience:</strong> <?= (int)$creatorInfo['experience_years'] ?> years</p>
            <?php if ($creatorInfo['social_link']): ?>
            <p><strong style="color:var(--text);">Social:</strong>
              <a href="<?= htmlspecialchars($creatorInfo['social_link']) ?>" target="_blank" rel="noopener"
                 style="color:var(--primary);">🔗 View Profile</a>
            </p>
            <?php endif; ?>
          </div>
          <a href="<?= BASE_URL ?>creator/dashboard.php" class="btn btn-outline btn-sm" style="margin-top:14px; display:inline-flex;">Go to My Kitchen →</a>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<?php include 'components/footer.php'; ?>
<div id="toast-container"></div>
<script src="<?= ASSETS ?>/js/main.js"></script>
<script>
function showSection(name) {
  ['favorites','history','info'].forEach(s => {
    document.getElementById('section-' + s).style.display = s === name ? 'block' : 'none';
    document.querySelectorAll('.profile-nav-item[onclick]').forEach(item => {
      if (item.getAttribute('onclick') === `showSection('${s}')`)
        item.classList.toggle('is-active', s === name);
    });
  });
}

function clearHistory() {
  if (!confirm('Are you sure you want to clear your viewing history?')) return;

  fetch('<?= BASE_URL ?>ajax/clear-history.php', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      showToast('History cleared ✓', 'success');

      // ✅ Update sidebar count
      const countEl = document.getElementById('historyCount');
      if (countEl) countEl.textContent = '0';

      // ✅ Update account info count
      const viewedEl = document.getElementById('historyViewedCount');
      if (viewedEl) viewedEl.textContent = '0';

      // ✅ Replace history UI
      document.getElementById('section-history').innerHTML = `
        <h3 class="profile-section__title">🕐 Viewing History</h3>
        <div class="empty-state">
          <div class="icon">👀</div>
          <h3>No history yet</h3>
          <p>Recipes you view will appear here.</p>
        </div>
      `;

    } else {
      showToast(data.message || 'Error clearing history', 'error');
    }
  });
}

function saveName() {
  const name = document.getElementById('editName').value.trim();
  if (!name || name.length < 2) { showToast('Name must be at least 2 characters', 'error'); return; }
  fetch('<?= BASE_URL ?>ajax/update-profile.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    body: JSON.stringify({ name })
  }).then(r => r.json()).then(data => {
    if (data.success) {
      document.getElementById('profileDisplayName').textContent = data.name;
      showToast('Name updated ✓', 'success');
    } else {
      showToast(data.message || 'Error', 'error');
    }
  });
}

<?php if ($activeTab !== 'favorites'): ?>
showSection('<?= htmlspecialchars($activeTab) ?>');
<?php endif; ?>

document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
</script>
</body>
</html>