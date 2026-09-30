<?php
// ajax/update-profile.php — Update user name
header('Content-Type: application/json');
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    http_response_code(403); die(json_encode(['error' => 'Forbidden']));
}
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$data   = json_decode(file_get_contents('php://input'), true);
$name   = trim($data['name'] ?? '');
$userId = (int)$_SESSION['user_id'];

if (!$name || strlen($name) < 2) {
    echo json_encode(['success' => false, 'message' => 'Name must be at least 2 characters']);
    exit;
}

$stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
$stmt->execute([$name, $userId]);
$_SESSION['user_name'] = $name;

echo json_encode(['success' => true, 'name' => htmlspecialchars($name)]);
