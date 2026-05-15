<?php
require_once '../middleware/auth_check.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$task_id          = (int)($_POST['task_id'] ?? 0);
$duration_minutes = (int)($_POST['duration_minutes'] ?? 0);
$mark_done        = (int)($_POST['mark_done'] ?? 0);

if (!$task_id) {
    echo json_encode(['error' => 'Invalid task ID.']);
    exit;
}

// Verify task belongs to user
$check = $pdo->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ?");
$check->execute([$task_id, $current_user_id]);
if (!$check->fetch()) {
    echo json_encode(['error' => 'Task not found.']);
    exit;
}

// Log session
$pdo->prepare("
    INSERT INTO progress_logs (user_id, task_id, duration_minutes)
    VALUES (?, ?, ?)
")->execute([$current_user_id, $task_id, $duration_minutes]);

// Update actual minutes
$pdo->prepare("
    UPDATE tasks SET actual_minutes = actual_minutes + ?
    WHERE id = ? AND user_id = ?
")->execute([$duration_minutes, $task_id, $current_user_id]);

// Mark task done if requested
if ($mark_done) {
    $pdo->prepare("
        UPDATE tasks SET is_done = 1 WHERE id = ? AND user_id = ?
    ")->execute([$task_id, $current_user_id]);
}

echo json_encode(['success' => true]);