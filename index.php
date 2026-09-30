<?php
// index.php — Homepage
require_once 'includes/auth.php';
require_once 'includes/db.php';

$user = currentUser();

// Fetch categories for chips
$catStmt = $pdo->query("SELECT id, name, icon FROM categories WHERE type = 'meal' ORDER BY sort_order");
$categories = $catStmt->fetchAll();

$specialStmt = $pdo->query("SELECT id, name, icon FROM categories WHERE type = 'special' ORDER BY sort_order");
$specialTags = $specialStmt->fetchAll();

// Fetch cuisines for filter dropdown
$cuisines = ['Indian','Kerala','South Indian','North Indian','Chinese','Arabic','Continental','Italian','Thai','Japanese'];

// Fetch featured recipes (latest 8)
$featStmt = $pdo->prepare("
    SELECT r.id, r.title, r.description, r.image, r.cooking_time, r.budget,
           c.name as category_name, r.cuisine,
           COALESCE(cr.chef_name, u.name) as chef_name,
           " . (isLoggedIn() ? "CASE WHEN f.id IS NOT NULL THEN 1 ELSE 0 END" : "0") . " as is_favorited
    FROM recipes r
    LEFT JOIN categories c ON r.category_id = c.id
    LEFT JOIN users u ON r.created_by = u.id
    LEFT JOIN creators cr ON u.id = cr.user_id
    " . (isLoggedIn() ? "LEFT JOIN favorites f ON f.recipe_id = r.id AND f.user_id = {$user['id']}" : "") . "
    WHERE r.status = 'active'
    ORDER BY r.created_at DESC
    LIMIT 8
");
$featStmt->execute();
$featuredRecipes = $featStmt->fetchAll();

// Stats
$statsStmt = $pdo->query("SELECT 
    (SELECT COUNT(*) FROM recipes WHERE status='active') as recipe_count,
    (SELECT COUNT(*) FROM users WHERE role='creator') as chef_count,
    (SELECT COUNT(*) FROM users) as user_count");
$stats = $statsStmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>window.BASE_URL="<?= BASE_URL ?>";window.IS_LOGGED_IN=<?= isLoggedIn() ? 'true' : 'false' ?>;</script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PantryChef — Recipes For Every Budget</title>
<meta name="description" content="Discover delicious recipes that fit your budget. Search by ingredients, cuisine, and price.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap">
<link rel="stylesheet" href="<?= ASSETS ?>/css/main.css">
</head>
<body>

<?php include 'components/navbar.php'; ?>

<!-- HERO SECTION -->
<section class="hero">
  <div class="hero-content">
    <div class="hero-eyebrow">The Smart Recipe Platform</div>
    <h1>What are you <span>cooking</span> today?</h1>
    <p>Find budget-friendly recipes from talented chefs around the world — filtered by price, time, and taste.</p>

    <div class="hero-search">
      <input type="text" id="heroSearch" placeholder="Search recipes, cuisines, ingredients..."
             autocomplete="off" oninput="debounceHeroSearch(this.value)">
      <button class="btn btn-primary" id="searchButton" onclick="applyFilters()">Search</button>
    </div>

    <div class="hero-stats">
      <div class="hero-stat">
        <div class="num"><?= number_format($stats['recipe_count']) ?>+</div>
        <div class="label">RECIPES</div>
      </div>
      <div class="hero-stat">
        <div class="num"><?= number_format($stats['chef_count']) ?>+</div>
        <div class="label">CHEFS</div>
      </div>
      <div class="hero-stat">
        <div class="num"><?= number_format($stats['user_count']) ?>+</div>
        <div class="label">FOOD LOVERS</div>
      </div>
    </div>
  </div>
</section>

<!-- MAIN CONTENT -->
<main>
  <div class="container">

    <!-- CATEGORY CHIPS -->
    <section class="section--sm">
      <div class="category-scroll">
        <div class="category-chips">
          <button class="chip is-active" onclick="filterByCategory('')" data-cat="">
            🍽️ All
          </button>
          <?php foreach ($categories as $cat): ?>
          <button class="chip" onclick="filterByCategory('<?= htmlspecialchars($cat['name']) ?>')" data-cat="<?= htmlspecialchars($cat['name']) ?>">
            <?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Special Tags -->
      <div class="category-scroll" style="margin-top: 10px;">
        <div class="category-chips">
          <?php foreach ($specialTags as $tag): ?>
          <button class="chip" style="background: var(--bg); border-style: dashed;"
                  onclick="filterByTag('<?= htmlspecialchars($tag['name']) ?>')" data-tag="<?= htmlspecialchars($tag['name']) ?>">
            <?= $tag['icon'] ?> <?= htmlspecialchars($tag['name']) ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- FILTERS BAR -->
    <section class="section--sm" style="padding-top: 0;">
      <div class="filter-bar">
        <div class="filter-group">
          <label>Cuisine</label>
          <select class="filter-select" id="filterCuisine" onchange="applyFilters()">
            <option value="">All Cuisines</option>
            <?php foreach ($cuisines as $c): ?>
              <option value="<?= $c ?>"><?= $c ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="filter-group">
          <label>Cooking Time</label>
          <select class="filter-select" id="filterTime" onchange="applyFilters()">
            <option value="">Any Time</option>
            <option value="15">Under 15 min</option>
            <option value="30">Under 30 min</option>
            <option value="60">Under 1 hour</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Sort By</label>
          <select class="filter-select" id="filterSort" onchange="applyFilters()">
            <option value="latest">Latest</option>
            <option value="budget_asc">Budget: Low to High</option>
            <option value="budget_desc">Budget: High to Low</option>
            <option value="time_asc">Cooking Time</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Max Budget: <span id="budgetDisplay">₹500</span></label>
          <input type="range" class="budget-range" id="filterBudget"
                 min="0" max="2000" step="50" value="500"
                 oninput="document.getElementById('budgetDisplay').textContent='₹'+this.value"
                 onchange="applyFilters()">
        </div>
        <div class="filter-group" style="justify-content: flex-end;">
          <button class="btn btn-ghost btn-sm" onclick="clearFilters()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg>
            Clear
          </button>
        </div>
      </div>
    </section>

    <!-- RECIPES GRID -->
    <section class="section" style="padding-top: 0;">
      <div class="section-header">
        <h2 class="section-title">Featured <span>Recipes</span></h2>
        <span id="resultsCount" class="section-link" style="color: var(--text-muted); font-weight:400"></span>
      </div>

      <div class="recipes-grid" id="recipesGrid">
        <?php foreach ($featuredRecipes as $recipe): ?>
          <?php include 'components/recipe-card.php'; ?>
        <?php endforeach; ?>
        <?php if (empty($featuredRecipes)): ?>
          <div class="empty-state" style="grid-column: 1/-1;">
            <div class="icon">🍽️</div>
            <h3>No recipes yet</h3>
            <p>Be the first to post a delicious recipe!</p>
          </div>
        <?php endif; ?>
      </div>

      <button class="btn btn-outline load-more-btn" id="loadMoreBtn" onclick="loadMore()" style="display:none;">
        Load More Recipes
      </button>
    </section>

    <!-- BECOME A CREATOR CTA -->
    <?php if (!isLoggedIn()): ?>
    <section class="section" style="padding-top:0; padding-bottom: 80px;">
      <div style="background: linear-gradient(135deg, #1a1a1a, #2d1a0f); border-radius: 24px; padding: 60px 48px; text-align:center; color:#fff; position:relative; overflow:hidden;">
        <div style="position:absolute; inset:0; background: url(\"data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Cg fill='%23FF6B35' fill-opacity='0.07'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E\");"></div>
        <div style="position:relative; z-index:1;">
          <div style="font-size: 3rem; margin-bottom: 16px;"></div>
          <h2 style="font-family: 'Syne', sans-serif; font-size: 2rem; margin-bottom: 12px;">Are You a Chef?</h2>
          <p style="color: rgba(255,255,255,.7); font-size: 1.05rem; margin-bottom: 28px; max-width: 500px; margin-left: auto; margin-right: auto;">
            Share your recipes, build your brand, and reach thousands of food lovers on PantryChef.
          </p>
          <a href="<?= BASE_URL ?>creator-signup.php" class="btn btn-primary btn-lg">
            Join as Creator Chef →
          </a>
        </div>
      </div>
    </section>
    <?php endif; ?>

  </div>
</main>

<?php include 'components/footer.php'; ?>
<div id="toast-container"></div>

<script src="<?= ASSETS ?>/js/main.js"></script>
<script>
// All filter logic is now in main.js
<?php if (isset($_GET['welcome'])): ?>
showToast('Welcome to PantryChef! 🎉', 'success');
<?php endif; ?>
</script>
<script>
document.getElementById('searchButton').addEventListener('click', function(){
  setTimeout(() => {
    const searchResult = document.getElementById("resultsCount");
    if (searchResult){
      searchResult.scrollIntoView({
        behavior : "smooth"
      });
    }
  }, 200);
})
</script>

</body>
</html>