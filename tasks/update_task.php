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

// Toggle done only
if (isset($_POST['is_done']) && count($_POST) <= 2) {
    $is_done = (int)$_POST['is_done'];
    $pdo->prepare("UPDATE tasks SET is_done = ? WHERE id = ? AND user_id = ?")
        ->execute([$is_done, $id, $current_user_id]);
    echo json_encode(['success' => true]);
    exit;
}

// Full update
$title             = trim($_POST['title'] ?? '');
$subject_id        = (int)($_POST['subject_id'] ?? 0);
$description       = trim($_POST['description'] ?? '');
$due_date          = $_POST['due_date'] ?? null;
$priority          = $_POST['priority'] ?? 'medium';
$estimated_minutes = (int)($_POST['estimated_minutes'] ?? 25);
$steps             = $_POST['steps'] ?? [];

if (empty($title)) { echo json_encode(['error' => 'Title is required.']); exit; }
if (!in_array($priority, ['low', 'medium', 'high'])) $priority = 'medium';
if (empty($due_date)) $due_date = null;

$pdo->prepare("
    UPDATE tasks SET title=?, subject_id=?, description=?, due_date=?, priority=?, estimated_minutes=?
    WHERE id=? AND user_id=?
")->execute([$title, $subject_id, $description, $due_date, $priority, $estimated_minutes, $id, $current_user_id]);

// Replace steps
$pdo->prepare("DELETE FROM steps WHERE task_id = ?")->execute([$id]);
if (!empty($steps)) {
    $step_stmt = $pdo->prepare("INSERT INTO steps (task_id, step_text, step_order) VALUES (?, ?, ?)");
    foreach ($steps as $order => $step_text) {
        $step_text = trim($step_text);
        if (!empty($step_text)) {
            $step_stmt->execute([$id, $step_text, $order + 1]);
        }
    }
}

echo json_encode(['success' => true]);