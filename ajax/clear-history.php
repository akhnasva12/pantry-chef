<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

header('Content-Type: application/json');

$user = currentUser();
$userId = (int)$user['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM history WHERE user_id = ?");
    $stmt->execute([$userId]);

    echo json_encode([
        'success' => true
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Something went wrong'
    ]);
}