<?php
require_once '../middleware/auth_check.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$title             = trim($_POST['title'] ?? '');
$subject_id        = (int)($_POST['subject_id'] ?? 0);
$description       = trim($_POST['description'] ?? '');
$due_date          = $_POST['due_date'] ?? null;
$priority          = $_POST['priority'] ?? 'medium';
$estimated_minutes = (int)($_POST['estimated_minutes'] ?? 25);
$steps             = $_POST['steps'] ?? [];

if (empty($title)) {
    echo json_encode(['error' => 'Title is required.']);
    exit;
}

if (!in_array($priority, ['low', 'medium', 'high'])) $priority = 'medium';
if ($estimated_minutes < 1) $estimated_minutes = 25;
if (empty($due_date)) $due_date = null;

// Verify subject belongs to user
$check = $pdo->prepare("SELECT id FROM subjects WHERE id = ? AND user_id = ?");
$check->execute([$subject_id, $current_user_id]);
if (!$check->fetch()) {
    echo json_encode(['error' => 'Invalid subject.']);
    exit;
}

// Insert task
$stmt = $pdo->prepare("
    INSERT INTO tasks (user_id, subject_id, title, description, due_date, priority, estimated_minutes)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$stmt->execute([$current_user_id, $subject_id, $title, $description, $due_date, $priority, $estimated_minutes]);
$task_id = $pdo->lastInsertId();

// Insert steps
if (!empty($steps)) {
    $step_stmt = $pdo->prepare("INSERT INTO steps (task_id, step_text, step_order) VALUES (?, ?, ?)");
    foreach ($steps as $order => $step_text) {
        $step_text = trim($step_text);
        if (!empty($step_text)) {
            $step_stmt->execute([$task_id, $step_text, $order + 1]);
        }
    }
}

echo json_encode(['success' => true, 'id' => $task_id]);