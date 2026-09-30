<?php
// creator/edit-recipe.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireCreator();

$user     = currentUser();
$userId   = (int)$user['id'];
$recipeId = (int)($_GET['id'] ?? 0);
$role   = $user['role']; 

if (!$recipeId) { header('Location: ' . BASE_URL . 'creator/dashboard.php'); exit; }

// Verify ownership
if ($role === 'admin') {
  // Admin can access any recipe
  $stmt = $pdo->prepare("SELECT * FROM recipes WHERE id = ? AND status != 'deleted'");
  $stmt->execute([$recipeId]);
} else {
  // Creator can only access their own
  $stmt = $pdo->prepare("SELECT * FROM recipes WHERE id = ? AND created_by = ? AND status != 'deleted'");
  $stmt->execute([$recipeId, $userId]);
}
$recipe = $stmt->fetch();
if (!$recipe) { header('Location: ' . BASE_URL . 'creator/dashboard.php?error=notfound'); exit; }

// Fetch ingredients & steps
$ingStmt = $pdo->prepare("SELECT ingredient_name, quantity FROM ingredients WHERE recipe_id = ? ORDER BY sort_order");
$ingStmt->execute([$recipeId]);
$ingredients = $ingStmt->fetchAll(PDO::FETCH_COLUMN);

$stepStmt = $pdo->prepare("SELECT description FROM steps WHERE recipe_id = ? ORDER BY step_number");
$stepStmt->execute([$recipeId]);
$steps = $stepStmt->fetchAll(PDO::FETCH_COLUMN);

$catStmt = $pdo->query("SELECT id, name, type FROM categories ORDER BY type, sort_order");
$categories = $catStmt->fetchAll();
$cuisines = ['Indian','Kerala','South Indian','North Indian','Chinese','Arabic','Continental','Italian','Thai','Japanese','Mexican','Mediterranean'];

$errors = [];
$formData = [
    'title'       => $recipe['title'],
    'description' => $recipe['description'],
    'cooking_time'=> $recipe['cooking_time'],
    'budget'      => $recipe['budget'],
    'cuisine'     => $recipe['cuisine'],
    'category_id' => $recipe['category_id'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request token.';
    } else {
        foreach ($formData as $k => $v) {
            $formData[$k] = trim($_POST[$k] ?? '');
        }
        $newIngredients = array_filter(array_map('trim', $_POST['ingredients'] ?? []));
        $newSteps       = array_filter(array_map('trim', $_POST['steps'] ?? []));

        if (!$formData['title'])       $errors[] = 'Title is required.';
        if (!$formData['cooking_time']) $errors[] = 'Cooking time is required.';
        if (!$formData['budget'])      $errors[] = 'Budget is required.';
        if (empty($newIngredients))    $errors[] = 'At least one ingredient is required.';
        if (empty($newSteps))          $errors[] = 'At least one step is required.';

        // Handle image update
        $newImage = $recipe['image'];
        if (!empty($_FILES['recipe_image']['name'])) {
            $file    = $_FILES['recipe_image'];
            $allowed = ['image/jpeg','image/png','image/webp'];
            if (!in_array($file['type'], $allowed)) {
                $errors[] = 'Image must be JPG, PNG, or WebP.';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $errors[] = 'Image must be under 5MB.';
            } else {
                $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $newImage  = uniqid('recipe_', true) . '.' . $ext;
                $uploadDir = __DIR__ . '/../uploads/recipes/';
                if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
                $dest      = $uploadDir . $newImage;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    // Delete old image
                    if ($recipe['image'] && file_exists($uploadDir . $recipe['image'])) {
                        @unlink($uploadDir . $recipe['image']);
                    }
                } else {
                    $errors[] = 'Upload failed. Please ensure uploads/recipes/ is writable.';
                    $newImage = $recipe['image'];
                }
            }
        }

        if (!$errors) {
            $pdo->beginTransaction();
            try {
                $pdo->prepare("UPDATE recipes SET title=?, description=?, image=?, cooking_time=?, budget=?, category_id=?, cuisine=? WHERE id=?")->execute([
                    $formData['title'], $formData['description'], $newImage,
                    (int)$formData['cooking_time'], (float)$formData['budget'],
                    $formData['category_id'] ?: null, $formData['cuisine'], $recipeId
                ]);
                $pdo->prepare("DELETE FROM ingredients WHERE recipe_id=?")->execute([$recipeId]);
                $pdo->prepare("DELETE FROM steps WHERE recipe_id=?")->execute([$recipeId]);
                $ingStmt2 = $pdo->prepare("INSERT INTO ingredients (recipe_id, ingredient_name, sort_order) VALUES (?, ?, ?)");
                foreach (array_values($newIngredients) as $i => $ing) { $ingStmt2->execute([$recipeId, $ing, $i+1]); }
                $stepStmt2 = $pdo->prepare("INSERT INTO steps (recipe_id, step_number, description) VALUES (?, ?, ?)");
                foreach (array_values($newSteps) as $i => $s) { $stepStmt2->execute([$recipeId, $i+1, $s]); }
                $pdo->commit();
                header("Location: " . BASE_URL . "creator/dashboard.php?edited=1");
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = 'Failed to update. Please try again.';
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
<title>Edit Recipe — PantryChef</title>
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

  <h1 style="font-family:'Syne',sans-serif; font-size:2rem; margin-bottom:6px;">✏️ Edit Recipe</h1>
  <p style="color:var(--text-muted); margin-bottom:32px;">Update your recipe details</p>

  <?php foreach ($errors as $e): ?>
  <div class="alert alert--error"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg><?= htmlspecialchars($e) ?></div>
  <?php endforeach; ?>

  <form method="POST" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

    <div style="background:#fff; border-radius:16px; padding:28px; box-shadow:var(--shadow-card); margin-bottom:24px;">
      <p class="form-section-title">📝 Basic Details</p>
      <div class="form-group">
        <label class="form-label">Recipe Title *</label>
        <input type="text" name="title" class="form-input" value="<?= htmlspecialchars($formData['title']) ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-textarea"><?= htmlspecialchars($formData['description']) ?></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Update Recipe Image</label>
        <?php if ($recipe['image']): ?>
          <div style="margin-bottom:12px;">
            <img src="<?= UPLOADS ?>/recipes/<?= htmlspecialchars($recipe['image']) ?>" style="height:120px; border-radius:8px; object-fit:cover;">
            <p style="font-size:12px; color:var(--text-muted); margin-top:4px;">Current image. Upload new to replace.</p>
          </div>
        <?php endif; ?>
        <div class="upload-area">
          <input type="file" id="recipe_image" name="recipe_image" accept="image/*">
          <div class="upload-icon">📷</div>
          <div class="upload-text">Click to upload new image<br><strong>JPG, PNG or WebP · Max 5MB</strong></div>
        </div>
        <div class="upload-preview" id="recipePreview"><img src="" alt="Preview"><button type="button" class="upload-clear" id="clearRecipeImg">×</button></div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Cooking Time (min) *</label>
          <input type="number" name="cooking_time" class="form-input" value="<?= (int)$formData['cooking_time'] ?>" min="1" required>
        </div>
        <div class="form-group">
          <label class="form-label">Budget (₹) *</label>
          <input type="number" name="budget" class="form-input" value="<?= htmlspecialchars($formData['budget']) ?>" min="0" required>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Category</label>
          <select name="category_id" class="form-select">
            <option value="">Select category...</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= $formData['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
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

    <div style="background:#fff; border-radius:16px; padding:28px; box-shadow:var(--shadow-card); margin-bottom:24px;">
      <p class="form-section-title">🥬 Ingredients</p>
      <div class="dynamic-list" id="ingredientsList">
        <?php foreach ($ingredients as $ing): ?>
        <div class="dynamic-item">
          <input type="text" class="form-input" name="ingredients[]" value="<?= htmlspecialchars($ing) ?>" required>
          <button type="button" class="remove-btn" onclick="removeDynamicItem(this)">×</button>
        </div>
        <?php endforeach; ?>
        <?php if (empty($ingredients)): ?>
        <div class="dynamic-item">
          <input type="text" class="form-input" name="ingredients[]" placeholder="Add ingredient..." required>
          <button type="button" class="remove-btn" onclick="removeDynamicItem(this)">×</button>
        </div>
        <?php endif; ?>
      </div>
      <button type="button" class="add-field-btn" onclick="addDynamicField('ingredientsList','Add ingredient...')">+ Add Ingredient</button>
    </div>

    <div style="background:#fff; border-radius:16px; padding:28px; box-shadow:var(--shadow-card); margin-bottom:24px;">
      <p class="form-section-title">📋 Cooking Steps</p>
      <div class="dynamic-list" id="stepsList">
        <?php foreach ($steps as $i => $step): ?>
        <div class="dynamic-item">
          <span class="step-num"><?= $i + 1 ?></span>
          <textarea class="form-input form-textarea" name="steps[]" rows="2" required><?= htmlspecialchars($step) ?></textarea>
          <button type="button" class="remove-btn" onclick="removeDynamicItem(this)">×</button>
        </div>
        <?php endforeach; ?>
        <?php if (empty($steps)): ?>
        <div class="dynamic-item">
          <span class="step-num">1</span>
          <textarea class="form-input form-textarea" name="steps[]" rows="2" placeholder="Step 1..." required></textarea>
          <button type="button" class="remove-btn" onclick="removeDynamicItem(this)">×</button>
        </div>
        <?php endif; ?>
      </div>
      <button type="button" class="add-field-btn" onclick="addDynamicField('stepsList','',true)">+ Add Step</button>
    </div>

    <div style="display:flex; gap:12px; justify-content:flex-end;">
      <a href="<?= BASE_URL ?>creator/dashboard.php" class="btn btn-ghost">Cancel</a>
      <button type="submit" class="btn btn-primary btn-lg">💾 Save Changes</button>
    </div>
  </form>
</div>

<?php include '../components/footer.php'; ?>
<script src="<?= ASSETS ?>/js/main.js"></script>
<script>initUploadPreview('recipe_image','recipePreview','clearRecipeImg');</script>
</body>
</html>