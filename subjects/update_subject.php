<?php
require_once '../middleware/auth_check.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$id          = (int)($_POST['id'] ?? 0);
$name        = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$difficulty  = $_POST['difficulty'] ?? 'basic';
$color_tag   = $_POST['color_tag'] ?? '#4A90D9';

if (!$id || empty($name)) {
    echo json_encode(['error' => 'Missing required fields.']);
    exit;
}

// Ownership check — user can only edit their own subjects
$check = $pdo->prepare("SELECT id FROM subjects WHERE id = ? AND user_id = ?");
$check->execute([$id, $current_user_id]);

if (!$check->fetch()) {
    echo json_encode(['error' => 'Subject not found.']);
    exit;
}

$stmt = $pdo->prepare("
    UPDATE subjects
    SET name = ?, description = ?, difficulty = ?, color_tag = ?
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$name, $description, $difficulty, $color_tag, $id, $current_user_id]);

echo json_encode(['success' => true]);