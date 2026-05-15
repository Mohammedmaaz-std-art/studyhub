<?php
require_once '../middleware/auth_check.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$id      = (int)($_POST['id'] ?? 0);
$is_done = (int)($_POST['is_done'] ?? 0);

if (!$id) { echo json_encode(['error' => 'Invalid step ID.']); exit; }

// Verify step belongs to a task owned by this user
$check = $pdo->prepare("
    SELECT s.id FROM steps s
    JOIN tasks t ON s.task_id = t.id
    WHERE s.id = ? AND t.user_id = ?
");
$check->execute([$id, $current_user_id]);
if (!$check->fetch()) { echo json_encode(['error' => 'Step not found.']); exit; }

$pdo->prepare("UPDATE steps SET is_done = ? WHERE id = ?")->execute([$is_done, $id]);

echo json_encode(['success' => true]);