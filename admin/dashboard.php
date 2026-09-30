<?php
// admin/dashboard.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireAdmin();

// Stats
$stats = $pdo->query("
    SELECT
        (SELECT COUNT(*) FROM users)                        as total_users,
        (SELECT COUNT(*) FROM users WHERE role='creator')   as total_creators,
        (SELECT COUNT(*) FROM recipes WHERE status='active') as total_recipes,
        (SELECT COUNT(*) FROM favorites)                    as total_favorites,
        (SELECT COUNT(*) FROM views)                        as total_views
")->fetch();

// Recent users
$recentUsers = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 10")->fetchAll();
// Recent recipes
$recentRecipes = $pdo->query("
    SELECT r.id, r.title, r.status, r.created_at,
           COALESCE(cr.chef_name, u.name) as chef_name
    FROM recipes r
    JOIN users u ON r.created_by = u.id
    LEFT JOIN creators cr ON u.id = cr.user_id
    ORDER BY r.created_at DESC LIMIT 10
")->fetchAll();

$activeTab = $_GET['tab'] ?? 'overview';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>window.BASE_URL="<?= BASE_URL ?>";window.IS_LOGGED_IN=<?= isLoggedIn() ? 'true' : 'false' ?>;</script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard — PantryChef</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap">
<link rel="stylesheet" href="<?= ASSETS ?>/css/main.css">
<style>
.admin-layout { display:grid; grid-template-columns:240px 1fr; min-height:calc(100vh - var(--nav-h)); }
</style>
</head>
<body>
<?php include '../components/navbar.php'; ?>

<div class="admin-layout">
  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div style="padding:20px; border-bottom:1px solid rgba(255,255,255,.1);">
      <p style="font-size:11px; letter-spacing:.1em; color:rgba(255,255,255,.4); font-weight:600;">ADMIN PANEL</p>
    </div>
    <?php
    $navItems = [
        ['overview', '📊', 'Overview'],
        ['users',    '👥', 'Users'],
        ['creators', '👨‍🍳', 'Creators'],
        ['creator_requests', '⏳', 'Creator Requests'],
        ['recipes',  '🍳', 'Recipes'],
    ];
    foreach ($navItems as [$tab, $icon, $label]):
    ?>
    <a href="?tab=<?= $tab ?>" class="admin-nav-item <?= $activeTab === $tab ? 'is-active' : '' ?>">
      <span><?= $icon ?></span> <?= $label ?>
    </a>
    <?php endforeach; ?>
    <a href="<?= BASE_URL ?>index.php" class="admin-nav-item" style="margin-top:auto; border-top:1px solid rgba(255,255,255,.08);">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
      Back to Site
    </a>
  </aside>

  <!-- Content -->
  <main class="admin-content">
    <?php if ($activeTab === 'overview'): ?>
    <h2 style="font-family:'Syne',sans-serif; font-size:1.5rem; margin-bottom:24px;">Overview</h2>
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:16px; margin-bottom:32px;">
      <?php
      $cards = [
          ['👥', $stats['total_users'],    'Total Users',    '#eff6ff','#1d4ed8'],
          ['👨‍🍳', $stats['total_creators'], 'Creators',       '#fff7ed','#c2410c'],
          ['🍳', $stats['total_recipes'],  'Active Recipes', '#f0fdf4','#166534'],
          ['❤️', $stats['total_favorites'],'Favorites',      '#fdf2f8','#9d174d'],
          ['👁', $stats['total_views'],    'Total Views',    '#f5f3ff','#6d28d9'],
      ];
      foreach ($cards as [$icon,$num,$label,$bg,$color]):
      ?>
      <div style="background:#fff; border-radius:16px; padding:20px; box-shadow:var(--shadow-card);">
        <div style="font-size:1.8rem; margin-bottom:8px;"><?= $icon ?></div>
        <div style="font-family:'Syne',sans-serif; font-size:1.8rem; font-weight:800;"><?= number_format($num) ?></div>
        <div style="font-size:12px; color:var(--text-muted);"><?= $label ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <h3 style="font-family:'Syne',sans-serif; margin-bottom:16px;">Recent Users</h3>
    <table class="data-table" style="margin-bottom:32px;">
      <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th></tr></thead>
      <tbody>
        <?php foreach ($recentUsers as $u): ?>
        <tr>
          <td><?= htmlspecialchars($u['name']) ?></td>
          <td style="color:var(--text-muted)"><?= htmlspecialchars($u['email']) ?></td>
          <td><span class="badge-role badge-role--<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
          <td style="color:var(--text-muted); font-size:13px;"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h3 style="font-family:'Syne',sans-serif; margin-bottom:16px;">Recent Recipes</h3>
    <table class="data-table">
      <thead><tr><th>Title</th><th>Chef</th><th>Status</th><th>Added</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($recentRecipes as $r): ?>
        <tr>
          <td><a href="<?= BASE_URL ?>recipe.php?id=<?= $r['id'] ?>" style="font-weight:600;"><?= htmlspecialchars($r['title']) ?></a></td>
          <td><?= htmlspecialchars($r['chef_name']) ?></td>
          <td><span class="badge-role" style="<?= $r['status']==='active'?'background:#dcfce7;color:#166534':'background:#fef3c7;color:#92400e' ?>"><?= ucfirst($r['status']) ?></span></td>
          <td style="font-size:13px; color:var(--text-muted);"><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
          <td>
            <button class="btn btn-sm" style="background:#fee2e2;color:var(--danger);" onclick="adminDelete('recipe', <?= $r['id'] ?>)">Delete</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <?php elseif ($activeTab === 'users'): ?>
    <?php
    $allUsers = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();
    ?>
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
      <h2 style="font-family:'Syne',sans-serif; font-size:1.5rem;">All Users (<?= count($allUsers) ?>)</h2>
    </div>
    <table class="data-table">
      <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($allUsers as $u): ?>
        <tr>
          <td style="color:var(--text-muted); font-size:13px;"><?= $u['id'] ?></td>
          <td><?= htmlspecialchars($u['name']) ?></td>
          <td style="color:var(--text-muted);"><?= htmlspecialchars($u['email']) ?></td>
          <td><span class="badge-role badge-role--<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
          <td style="font-size:13px; color:var(--text-muted);"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
          <td>
            <?php if ($u['role'] !== 'admin'): ?>
            <button class="btn btn-sm" style="background:#fee2e2;color:var(--danger);" onclick="adminDelete('user', <?= $u['id'] ?>)">Delete</button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <?php elseif ($activeTab === 'creators'): ?>
    <?php
    $allCreators = $pdo->query("
        SELECT u.id, u.name, u.email, u.created_at,
              cr.chef_name, cr.speciality_cuisine, cr.location, cr.experience_years,
              (SELECT COUNT(*) FROM recipes WHERE created_by = u.id AND status='active') as recipe_count
        FROM users u
        JOIN creators cr ON cr.user_id = u.id
        WHERE cr.status = 'approved'
        ORDER BY u.created_at DESC
    ")->fetchAll();
    ?>
    <h2 style="font-family:'Syne',sans-serif; font-size:1.5rem; margin-bottom:24px;">Creators (<?= count($allCreators) ?>)</h2>
    <table class="data-table">
      <thead><tr><th>Chef Name</th><th>Real Name</th><th>Cuisine</th><th>Recipes</th><th>Location</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($allCreators as $c): ?>
        <tr>
          <td style="font-weight:600;"><?= htmlspecialchars($c['chef_name']) ?></td>
          <td><?= htmlspecialchars($c['name']) ?></td>
          <td><?= htmlspecialchars($c['speciality_cuisine'] ?? '—') ?></td>
          <td>🍳 <?= (int)$c['recipe_count'] ?></td>
          <td style="color:var(--text-muted)"><?= htmlspecialchars($c['location'] ?? '—') ?></td>
          <td><button class="btn btn-sm" style="background:#fee2e2;color:var(--danger);" onclick="adminDelete('user', <?= $c['id'] ?>)">Remove</button></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <?php elseif ($activeTab === 'creator_requests'): ?>
    <?php
    $requests = $pdo->query("
        SELECT u.id, u.name, u.email, u.created_at,
              cr.chef_name, cr.speciality_cuisine, cr.location, cr.experience_years
        FROM users u
        JOIN creators cr ON cr.user_id = u.id
        WHERE cr.status = 'pending'
        ORDER BY u.created_at DESC
    ")->fetchAll();
    ?>

    <h2 style="font-family:'Syne',sans-serif; font-size:1.5rem; margin-bottom:24px;">
      Creator Requests (<?= count($requests) ?>)
    </h2>

    <table class="data-table">
      <thead>
        <tr>
          <th>Chef Name</th>
          <th>Real Name</th>
          <th>Email</th>
          <th>Cuisine</th>
          <th>Experience</th>
          <th>Location</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($requests as $r): ?>
        <tr>
          <td style="font-weight:600;"><?= htmlspecialchars($r['chef_name']) ?></td>
          <td><?= htmlspecialchars($r['name']) ?></td>
          <td style="color:var(--text-muted)"><?= htmlspecialchars($r['email']) ?></td>
          <td><?= htmlspecialchars($r['speciality_cuisine'] ?? '—') ?></td>
          <td><?= (int)$r['experience_years'] ?> yrs</td>
          <td style="color:var(--text-muted)"><?= htmlspecialchars($r['location'] ?? '—') ?></td>
          <td>
            <button onclick="handleCreator(<?= $r['id'] ?>, 'approve')" class="btn btn-sm">Approve</button>
            <button onclick="handleCreator(<?= $r['id'] ?>, 'reject')" class="btn btn-sm" style="background:#fee2e2;color:red;">Reject</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <?php elseif ($activeTab === 'recipes'): ?>
    <?php
    $allRecipes = $pdo->query("
        SELECT r.id, r.title, r.status, r.cooking_time, r.budget, r.cuisine, r.created_at,
               c.name as category_name,
               COALESCE(cr.chef_name, u.name) as chef_name,
               (SELECT COUNT(*) FROM views WHERE recipe_id=r.id) as views,
               (SELECT COUNT(*) FROM favorites WHERE recipe_id=r.id) as favs
        FROM recipes r
        JOIN users u ON r.created_by = u.id
        LEFT JOIN creators cr ON u.id = cr.user_id
        LEFT JOIN categories c ON r.category_id = c.id
        WHERE r.status != 'deleted'
        ORDER BY r.created_at DESC
    ")->fetchAll();
    ?>
    <h2 style="font-family:'Syne',sans-serif; font-size:1.5rem; margin-bottom:24px;">All Recipes (<?= count($allRecipes) ?>)</h2>
    <table class="data-table">
      <thead><tr><th>Title</th><th>Chef</th><th>Category</th><th>Budget</th><th>Views</th><th>Favs</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($allRecipes as $r): ?>
        <tr>
          <td><a href="<?= BASE_URL ?>recipe.php?id=<?= $r['id'] ?>" style="font-weight:600"><?= htmlspecialchars($r['title']) ?></a></td>
          <td><?= htmlspecialchars($r['chef_name']) ?></td>
          <td style="font-size:13px;"><?= htmlspecialchars($r['category_name'] ?? '—') ?></td>
          <td style="font-size:13px;">₹<?= number_format($r['budget'],0) ?></td>
          <td style="font-size:13px;">👁 <?= (int)$r['views'] ?></td>
          <td style="font-size:13px;">❤️ <?= (int)$r['favs'] ?></td>
          <td><span class="badge-role" style="<?= $r['status']==='active'?'background:#dcfce7;color:#166534':'background:#fef3c7;color:#92400e' ?>"><?= ucfirst($r['status']) ?></span></td>
          <td>
            <div style="display:flex; gap:6px;">
              <a href="<?= BASE_URL ?>creator/edit-recipe.php?id=<?= $r['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
              <button class="btn btn-sm" style="background:#fee2e2;color:var(--danger);" onclick="adminDelete('recipe', <?= $r['id'] ?>)">Delete</button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </main>
</div>

<div id="toast-container"></div>
<script src="<?= ASSETS ?>/js/main.js"></script>
<script>
function adminDelete(type, id) {
  if (!confirm(`Delete this ${type}? This action cannot be undone.`)) return;
  const url = type === 'recipe' ? window.BASE_URL+'ajax/delete-recipe.php' : window.BASE_URL+'ajax/delete-user.php';
  fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    body: JSON.stringify({ [`${type}_id`]: id })
  }).then(r => r.json()).then(data => {
    if (data.success) { showToast(`${type} deleted`, 'success'); setTimeout(() => location.reload(), 800); }
    else showToast(data.message || 'Error', 'error');
  });
}

function handleCreator(userId, action) {
  if (!confirm(`Are you sure to ${action} this creator?`)) return;

  fetch(window.BASE_URL + 'ajax/handle-creator.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ user_id: userId, action })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      showToast(`Creator ${action}d`, 'success');
      setTimeout(() => location.reload(), 800);
    } else {
      showToast(data.message || 'Error', 'error');
    }
  });
}
</script>
</body>
</html>