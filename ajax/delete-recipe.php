<?php
// ajax/delete-recipe.php
header('Content-Type: application/json');
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) { http_response_code(403); die(); }
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireCreator();

$data     = json_decode(file_get_contents('php://input'), true);
$recipeId = (int)($data['recipe_id'] ?? 0);
$userId   = (int)$_SESSION['user_id'];

if (!$recipeId) { echo json_encode(['success' => false, 'message' => 'Invalid ID']); exit; }

// Only creator who owns it or admin can delete
if (isAdmin()) {
    $stmt = $pdo->prepare("UPDATE recipes SET status='deleted' WHERE id=?");
    $stmt->execute([$recipeId]);
} else {
    $stmt = $pdo->prepare("UPDATE recipes SET status='deleted' WHERE id=? AND created_by=?");
    $stmt->execute([$recipeId, $userId]);
}

echo json_encode(['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() ? 'Deleted' : 'Not found or unauthorized']);
