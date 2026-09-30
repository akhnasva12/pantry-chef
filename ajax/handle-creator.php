<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireAdmin();

$data = json_decode(file_get_contents("php://input"), true);

$userId = (int)$data['user_id'];
$action = $data['action'];

if ($action === 'approve') {

    $pdo->beginTransaction();

    $pdo->prepare("UPDATE creators SET status='approved' WHERE user_id=?")
        ->execute([$userId]);

    $pdo->prepare("UPDATE users SET role='creator' WHERE id=?")
        ->execute([$userId]);

    $pdo->commit();

    echo json_encode(['success' => true]);

} elseif ($action === 'reject') {

    $pdo->prepare("UPDATE creators SET status='rejected' WHERE user_id=?")
        ->execute([$userId]);

    echo json_encode(['success' => true]);

} else {
    echo json_encode(['success' => false]);
}