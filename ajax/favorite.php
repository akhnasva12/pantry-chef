<?php
// ajax/favorite.php
header('Content-Type: application/json');
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    http_response_code(403); die(json_encode(['error' => 'Forbidden']));
}
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'redirect' => BASE_URL . 'login.php']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$recipeId = (int)($data['recipe_id'] ?? 0);
$userId   = (int)$_SESSION['user_id'];

if (!$recipeId) { echo json_encode(['success' => false, 'message' => 'Invalid recipe']); exit; }

// Check if recipe exists
$stmt = $pdo->prepare("SELECT id FROM recipes WHERE id = ? AND status = 'active'");
$stmt->execute([$recipeId]);
if (!$stmt->fetch()) { echo json_encode(['success' => false, 'message' => 'Recipe not found']); exit; }

// Toggle
$check = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND recipe_id = ?");
$check->execute([$userId, $recipeId]);
$existing = $check->fetch();

if ($existing) {
    $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?")->execute([$userId, $recipeId]);
    echo json_encode(['success' => true, 'action' => 'removed']);
} else {
    $pdo->prepare("INSERT INTO favorites (user_id, recipe_id) VALUES (?, ?)")->execute([$userId, $recipeId]);
    echo json_encode(['success' => true, 'action' => 'added']);
}
