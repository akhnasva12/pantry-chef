<?php
// ajax/recipes.php — AJAX recipe fetching with filters
header('Content-Type: application/json');
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    http_response_code(403); die(json_encode(['error' => 'Forbidden']));
}

require_once '../includes/db.php';
require_once '../includes/auth.php';

$page      = max(1, (int)($_GET['page'] ?? 1));
$perPage   = 8;
$offset    = ($page - 1) * $perPage;
$userId    = $_SESSION['user_id'] ?? null;

$where  = ["r.status = 'active'"];
$params = [];

// Search query — using LIKE for reliability across all dataset sizes
if (!empty($_GET['q'])) {
    $searchTerm = '%' . trim($_GET['q']) . '%';
    $where[] = "(r.title LIKE ? OR r.description LIKE ? OR r.cuisine LIKE ?)";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Category filter
if (!empty($_GET['category'])) {
    $where[] = "c.name = ?";
    $params[] = $_GET['category'];
}

// Tag filter (special category)
if (!empty($_GET['tag'])) {
    $where[] = "EXISTS (SELECT 1 FROM categories ct2 JOIN recipes r2 ON r2.category_id = ct2.id WHERE r2.id = r.id AND ct2.name = ? AND ct2.type = 'special')";
    // fallback: search title
    $where[count($where)-1] = "(c.name = ? OR r.title LIKE ?)";
    $params[] = $_GET['tag'];
    $params[] = '%' . $_GET['tag'] . '%';
}

// Cuisine filter
if (!empty($_GET['cuisine'])) {
    $where[] = "r.cuisine = ?";
    $params[] = $_GET['cuisine'];
}

// Budget filter
if (!empty($_GET['budget'])) {
    $where[] = "r.budget <= ?";
    $params[] = (float)$_GET['budget'];
}

// Time filter
if (!empty($_GET['time'])) {
    $where[] = "r.cooking_time <= ?";
    $params[] = (int)$_GET['time'];
}

$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

// Sort
$sort = match($_GET['sort'] ?? 'latest') {
    'budget_asc'  => 'r.budget ASC',
    'budget_desc' => 'r.budget DESC',
    'time_asc'    => 'r.cooking_time ASC',
    default       => 'r.created_at DESC',
};

$favJoin  = $userId ? "LEFT JOIN favorites f ON f.recipe_id = r.id AND f.user_id = $userId" : "";
$favField = $userId ? "CASE WHEN f.id IS NOT NULL THEN 1 ELSE 0 END" : "0";

// Count total
$countSql = "SELECT COUNT(*) FROM recipes r LEFT JOIN categories c ON r.category_id = c.id LEFT JOIN users u ON r.created_by = u.id LEFT JOIN creators cr ON u.id = cr.user_id $favJoin $whereSQL";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

// Fetch recipes
$sql = "
    SELECT r.id, r.title, r.description, r.image, r.cooking_time, r.budget,
           c.name as category_name, r.cuisine,
           COALESCE(cr.chef_name, u.name) as chef_name,
           $favField as is_favorited
    FROM recipes r
    LEFT JOIN categories c ON r.category_id = c.id
    LEFT JOIN users u ON r.created_by = u.id
    LEFT JOIN creators cr ON u.id = cr.user_id
    $favJoin
    $whereSQL
    ORDER BY $sort
    LIMIT $perPage OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$recipes = $stmt->fetchAll();

echo json_encode([
    'recipes' => $recipes,
    'total'   => $total,
    'page'    => $page,
    'hasMore' => ($offset + $perPage) < $total,
]);