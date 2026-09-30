<?php
// ajax/delete-user.php
header('Content-Type: application/json');
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) { http_response_code(403); die(); }
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireAdmin();

$data   = json_decode(file_get_contents('php://input'), true);
$userId = (int)($data['user_id'] ?? 0);

if (!$userId) { echo json_encode(['success' => false, 'message' => 'Invalid ID']); exit; }

// Prevent deleting own account
if ($userId === (int)$_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
$stmt->execute([$userId]);
echo json_encode(['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() ? 'Deleted' : 'Not found']);
