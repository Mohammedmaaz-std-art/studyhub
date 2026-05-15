<?php
require_once '../middleware/auth_check.php';
require_once '../config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../subjects/index.php");
    exit;
}

// Get questions from session
$questions  = $_SESSION['quiz_questions']  ?? [];
$subject_id = $_SESSION['quiz_subject_id'] ?? 0;

if (empty($questions) || !$subject_id) {
    header("Location: ../subjects/index.php");
    exit;
}

// Verify subject
$stmt = $pdo->prepare("SELECT * FROM subjects WHERE id = ? AND user_id = ?");
$stmt->execute([$subject_id, $current_user_id]);
$subject = $stmt->fetch();

if (!$subject) {
    header("Location: ../subjects/index.php");
    exit;
}

// Calculate score
$score = 0;
$total = count($questions);
$results = [];

foreach ($questions as $i => $q) {
    $user_answer    = $_POST['q' . $i] ?? '';
    $correct        = $q['correct'];
    $is_correct     = ($user_answer === $correct);
    if ($is_correct) $score++;
    $results[] = [
        'question'    => $q['question'],
        'options'     => $q['options'],
        'user_answer' => $user_answer,
        'correct'     => $correct,
        'is_correct'  => $is_correct
    ];
}

$score_pct = round(($score / $total) * 100);
$passed    = $score_pct >= 70;

// Save attempt
$pdo->prepare("
    INSERT INTO quiz_attempts
    (user_id, subject_id, score, total, passed)
    VALUES (?, ?, ?, ?, ?)
")->execute([$current_user_id, $subject_id, $score, $total, $passed ? 1 : 0]);

// If passed — issue certificate (only if not already issued)
if ($passed) {
    $check = $pdo->prepare("
        SELECT id FROM certificates
        WHERE user_id = ? AND subject_id = ?
    ");
    $check->execute([$current_user_id, $subject_id]);

    if (!$check->fetch()) {
        $pdo->prepare("
            INSERT INTO certificates
            (user_id, subject_id, subject_name, score)
            VALUES (?, ?, ?, ?)
        ")->execute([
            $current_user_id,
            $subject_id,
            $subject['name'],
            $score_pct
        ]);
    }
}

// Store result in session for result page
$_SESSION['quiz_result'] = [
    'score'      => $score,
    'total'      => $total,
    'score_pct'  => $score_pct,
    'passed'     => $passed,
    'subject_id' => $subject_id,
    'subject'    => $subject['name'],
    'results'    => $results
];

// Clear quiz questions
unset($_SESSION['quiz_questions']);

header("Location: ../quiz/result.php");
exit;