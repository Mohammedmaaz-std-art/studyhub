<?php
require_once '../middleware/auth_check.php';
require_once '../config/db.php';

header('Content-Type: application/json');

// Find the best pending task to suggest
// Priority order: high → medium → low, then by due date
$stmt = $pdo->prepare("
    SELECT t.id, t.title, t.priority, t.estimated_minutes, t.due_date,
           s.name as subject_name, s.color_tag, s.id as subject_id
    FROM tasks t
    JOIN subjects s ON t.subject_id = s.id
    WHERE t.user_id = ? AND t.is_done = 0
    ORDER BY
        FIELD(t.priority, 'high', 'medium', 'low'),
        t.due_date ASC,
        t.created_at ASC
    LIMIT 1
");
$stmt->execute([$current_user_id]);
$task = $stmt->fetch();

if ($task) {
    // Fetch steps for this task
    $stmt = $pdo->prepare("SELECT * FROM steps WHERE task_id = ? ORDER BY step_order");
    $stmt->execute([$task['id']]);
    $task['steps'] = $stmt->fetchAll();

    echo json_encode(['has_task' => true, 'task' => $task]);
} else {
    // No pending tasks — fetch subjects for the quick form
    $stmt = $pdo->prepare("SELECT id, name, color_tag FROM subjects WHERE user_id = ? ORDER BY name");
    $stmt->execute([$current_user_id]);
    $subjects = $stmt->fetchAll();

    echo json_encode(['has_task' => false, 'subjects' => $subjects]);
}