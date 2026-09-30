<?php
// creator/add-recipe.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireCreator();

$user   = currentUser();
$userId = (int)$user['id'];

$errors   = [];
$formData = ['title' => '', 'description' => '', 'cooking_time' => '', 'budget' => '', 'cuisine' => '', 'category_id' => ''];

// Fetch categories
$catStmt = $pdo->query("SELECT id, name, type FROM categories ORDER BY type, sort_order");
$categories = $catStmt->fetchAll();

$cuisines = ['Indian','Kerala','South Indian','North Indian','Chinese','Arabic','Continental','Italian','Thai','Japanese','Mexican','Mediterranean'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request token.';
    } else {
        foreach ($formData as $k => $v) {
            $formData[$k] = trim($_POST[$k] ?? '');
        }
        $ingredients = array_filter(array_map('trim', $_POST['ingredients'] ?? []));
        $steps       = array_filter(array_map('trim', $_POST['steps'] ?? []));

        if (!$formData['title'])       $errors[] = 'Recipe title is required.';
        if (!$formData['cooking_time']) $errors[] = 'Cooking time is required.';
        if (!$formData['budget'])      $errors[] = 'Budget is required.';
        if (empty($ingredients))       $errors[] = 'Add at least one ingredient.';
        if (empty($steps))             $errors[] = 'Add at least one step.';

        // Handle image
        $recipeImage = null;
        if (!empty($_FILES['recipe_image']['name'])) {
            $file    = $_FILES['recipe_image'];
            $allowed = ['image/jpeg','image/png','image/webp'];
            if (!in_array($file['type'], $allowed)) {
                $errors[] = 'Image must be JPG, PNG, or WebP.';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $errors[] = 'Image must be under 5MB.';
            } else {
                $ext         = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $recipeImage = uniqid('recipe_', true) . '.' . $ext;
                $uploadDir   = __DIR__ . '/../uploads/recipes/';
                if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
                $dest        = $uploadDir . $recipeImage;
                if (!move_uploaded_file($file['tmp_name'], $dest)) {
                    $errors[] = 'Upload failed. Please ensure uploads/recipes/ is writable.';
                    $recipeImage = null;
                }
            }
        }

        if (!$errors) {
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO recipes (title, description, image, cooking_time, budget, category_id, cuisine, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $formData['title'],
                    $formData['description'],
                    $recipeImage,
                    (int)$formData['cooking_time'],
                    (float)$formData['budget'],
                    $formData['category_id'] ?: null,
                    $formData['cuisine'],
                    $userId
                ]);
                $recipeId = $pdo->lastInsertId();

                // Insert ingredients
                $ingStmt = $pdo->prepare("INSERT INTO ingredients (recipe_id, ingredient_name, sort_order) VALUES (?, ?, ?)");
                foreach (array_values($ingredients) as $i => $ing) {
                    $ingStmt->execute([$recipeId, $ing, $i + 1]);
                }

                // Insert steps
                $stepStmt = $pdo->prepare("INSERT INTO steps (recipe_id, step_number, description) VALUES (?, ?, ?)");
                foreach (array_values($steps) as $i => $step) {
                    $stepStmt->execute([$recipeId, $i + 1, $step]);
                }

                $pdo->commit();
                header("Location: " . BASE_URL . "creator/dashboard.php?added=1");
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = 'Failed to save recipe. Please try again.';
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
<title>Add New Recipe — PantryChef</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap">
<link rel="stylesheet" href="<?= ASSETS ?>/css/main.css">
</head>
<body>
<?php include '../components/navbar.php'; ?>

<div class="container" style="max-width:860px; padding-top:40px; padding-bottom:80px;">
  <div style="margin-bottom:28px;">
    <a href="<?= BASE_URL ?>creator/dashboard.php" style="color:var(--text-muted); font-size:14px; display:inline-flex; align-items:center; gap:6px;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><polyline points="15 18 9 12 15 6"/></svg>
      Back to Dashboard
    </a>
  </div>

  <h1 style="font-family:'Syne',sans-serif; font-size:2rem; margin-bottom:6px;">🍳 Add New Recipe</h1>
  <p style="color:var(--text-muted); margin-bottom:32px;">Share your culinary creation with the world</p>

  <?php foreach ($errors as $e): ?>
  <div class="alert alert--error">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <?= htmlspecialchars($e) ?>
  </div>
  <?php endforeach; ?>

  <form method="POST" action="<?= BASE_URL ?>creator/add-recipe.php" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

    <!-- Basic Info -->
    <div style="background:#fff; border-radius:16px; padding:28px; box-shadow:var(--shadow-card); margin-bottom:24px;">
      <p class="form-section-title">📝 Basic Details</p>

      <div class="form-group">
        <label class="form-label">Recipe Title *</label>
        <input type="text" name="title" class="form-input"
               value="<?= htmlspecialchars($formData['title']) ?>"
               placeholder="e.g. Creamy Butter Chicken" required>
      </div>

      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-textarea"
                  placeholder="Tell us about this recipe, its flavors, and what makes it special..."><?= htmlspecialchars($formData['description']) ?></textarea>
      </div>

      <div class="form-group">
        <label class="form-label">Recipe Image</label>
        <div class="upload-area">
          <input type="file" id="recipe_image" name="recipe_image" accept="image/*">
          <div class="upload-icon">📷</div>
          <div class="upload-text">Click to upload a mouth-watering photo<br><strong>JPG, PNG or WebP · Max 5MB</strong></div>
        </div>
        <div class="upload-preview" id="recipePreview">
          <img src="" alt="Preview">
          <button type="button" class="upload-clear" id="clearRecipeImg">×</button>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Cooking Time (minutes) *</label>
          <input type="number" name="cooking_time" class="form-input"
                 value="<?= (int)$formData['cooking_time'] ?>"
                 min="1" max="600" placeholder="e.g. 30" required>
        </div>
        <div class="form-group">
          <label class="form-label">Estimated Budget (₹) *</label>
          <input type="number" name="budget" class="form-input"
                 value="<?= htmlspecialchars($formData['budget']) ?>"
                 min="0" step="10" placeholder="e.g. 150" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Category</label>
          <select name="category_id" class="form-select">
            <option value="">Select category...</option>
            <optgroup label="Meal Type">
              <?php foreach ($categories as $cat): if ($cat['type'] !== 'meal') continue; ?>
                <option value="<?= $cat['id'] ?>" <?= $formData['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </optgroup>
            <optgroup label="Food Type">
              <?php foreach ($categories as $cat): if ($cat['type'] !== 'food_type') continue; ?>
                <option value="<?= $cat['id'] ?>" <?= $formData['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </optgroup>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Cuisine</label>
          <select name="cuisine" class="form-select">
            <option value="">Select cuisine...</option>
            <?php foreach ($cuisines as $c): ?>
              <option value="<?= $c ?>" <?= $formData['cuisine'] === $c ? 'selected' : '' ?>><?= $c ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <!-- Ingredients -->
    <div style="background:#fff; border-radius:16px; padding:28px; box-shadow:var(--shadow-card); margin-bottom:24px;">
      <p class="form-section-title">🥬 Ingredients</p>
      <div class="dynamic-list" id="ingredientsList">
        <div class="dynamic-item">
          <input type="text" class="form-input" name="ingredients[]" placeholder="e.g. Chicken breast — 500g" required>
          <button type="button" class="remove-btn" onclick="removeDynamicItem(this)">×</button>
        </div>
      </div>
      <button type="button" class="add-field-btn" onclick="addDynamicField('ingredientsList', 'Add ingredient...')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Ingredient
      </button>
    </div>

    <!-- Steps -->
    <div style="background:#fff; border-radius:16px; padding:28px; box-shadow:var(--shadow-card); margin-bottom:24px;">
      <p class="form-section-title">📋 Cooking Steps</p>
      <div class="dynamic-list" id="stepsList">
        <div class="dynamic-item">
          <span class="step-num">1</span>
          <textarea class="form-input form-textarea" name="steps[]" rows="2" placeholder="Step 1 description..." required></textarea>
          <button type="button" class="remove-btn" onclick="removeDynamicItem(this)">×</button>
        </div>
      </div>
      <button type="button" class="add-field-btn" onclick="addDynamicField('stepsList', '', true)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Step
      </button>
    </div>

    <div style="display:flex; gap:12px; justify-content:flex-end;">
      <a href="<?= BASE_URL ?>creator/dashboard.php" class="btn btn-ghost">Cancel</a>
      <button type="submit" class="btn btn-primary btn-lg">
        🍳 Publish Recipe
      </button>
    </div>
  </form>
</div>

<?php include '../components/footer.php'; ?>
<script src="<?= ASSETS ?>/js/main.js"></script>
<script>
initUploadPreview('recipe_image', 'recipePreview', 'clearRecipeImg');
</script>
</body>
</html>