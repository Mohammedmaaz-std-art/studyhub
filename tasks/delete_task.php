<?php
require_once '../middleware/auth_check.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if (!$id) { echo json_encode(['error' => 'Invalid ID.']); exit; }

// Ownership check
$check = $pdo->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ?");
$check->execute([$id, $current_user_id]);
if (!$check->fetch()) { echo json_encode(['error' => 'Task not found.']); exit; }

// Delete steps first then task
$pdo->prepare("DELETE FROM steps WHERE task_id = ?")->execute([$id]);
$pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?")->execute([$id, $current_user_id]);

echo json_encode(['success' => true]);