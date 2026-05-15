<?php
require_once '../middleware/auth_check.php';
require_once '../config/db.php';

header('Content-Type: application/json');

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    echo json_encode(['error' => 'Invalid task ID.']);
    exit;
}

// Fetch task with subject info
$stmt = $pdo->prepare("
    SELECT t.*, s.name as subject_name, s.color_tag
    FROM tasks t
    JOIN subjects s ON t.subject_id = s.id
    WHERE t.id = ? AND t.user_id = ?
");
$stmt->execute([$id, $current_user_id]);
$task = $stmt->fetch();

if (!$task) {
    echo json_encode(['error' => 'Task not found.']);
    exit;
}

// Fetch steps
$stmt = $pdo->prepare("SELECT * FROM steps WHERE task_id = ? ORDER BY step_order");
$stmt->execute([$id]);
$task['steps'] = $stmt->fetchAll();

echo json_encode($task);