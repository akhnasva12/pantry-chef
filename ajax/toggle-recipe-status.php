<?php
// ajax/toggle-recipe-status.php — Creator can toggle recipe active/draft
header('Content-Type: application/json');
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    http_response_code(403); die(json_encode(['error' => 'Forbidden']));
}
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireCreator();

$data     = json_decode(file_get_contents('php://input'), true);
$recipeId = (int)($data['recipe_id'] ?? 0);
$userId   = (int)$_SESSION['user_id'];

if (!$recipeId) { echo json_encode(['success' => false, 'message' => 'Invalid ID']); exit; }

// Verify ownership
$stmt = $pdo->prepare("SELECT id, status FROM recipes WHERE id = ? AND created_by = ? AND status != 'deleted'");
$stmt->execute([$recipeId, $userId]);
$recipe = $stmt->fetch();

if (!$recipe) {
    echo json_encode(['success' => false, 'message' => 'Recipe not found or unauthorized']);
    exit;
}

$newStatus = $recipe['status'] === 'active' ? 'draft' : 'active';
$pdo->prepare("UPDATE recipes SET status = ? WHERE id = ?")->execute([$newStatus, $recipeId]);

echo json_encode(['success' => true, 'status' => $newStatus]);
