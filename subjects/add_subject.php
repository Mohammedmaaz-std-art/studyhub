<?php
require_once '../middleware/auth_check.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$name        = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$difficulty  = $_POST['difficulty'] ?? 'basic';
$color_tag   = $_POST['color_tag'] ?? '#4A90D9';

if (empty($name)) {
    echo json_encode(['error' => 'Subject name is required.']);
    exit;
}

if (!in_array($difficulty, ['basic', 'intermediate', 'advanced'])) {
    $difficulty = 'basic';
}

$stmt = $pdo->prepare("
    INSERT INTO subjects (user_id, name, description, difficulty, color_tag)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->execute([$current_user_id, $name, $description, $difficulty, $color_tag]);

echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);