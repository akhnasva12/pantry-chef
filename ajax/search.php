<?php
// ajax/search.php — Live search
header('Content-Type: application/json');
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    http_response_code(403); die(json_encode(['error' => 'Forbidden']));
}
require_once '../includes/db.php';

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) { echo json_encode(['results' => []]); exit; }

$stmt = $pdo->prepare("
    SELECT r.id, r.title, r.image, r.cooking_time, r.budget
    FROM recipes r
    WHERE r.status = 'active' AND (r.title LIKE ? OR r.description LIKE ?)
    ORDER BY r.created_at DESC
    LIMIT 8
");
$like = '%' . $q . '%';
$stmt->execute([$like, $like]);
$results = $stmt->fetchAll();

echo json_encode(['results' => $results]);
