<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.html");
    exit;
}

$email    = trim($_POST['email']    ?? '');
$password =      $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    header("Location: login.html?error=1");
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, username, password_hash, role, onboarding_done
    FROM users WHERE email = ?
");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];

    // First-time login → go to onboarding
    // if (!$user['onboarding_done']) {
    //     header("Location: ../auth/onboarding.php");
    // }
    // exit;
    

    if ($user['onboarding_done']) {
    header("Location: ../dashboard/index.php");
} else {
    header("Location: ../auth/onboarding.php");
}
exit;
}
else {
    header("Location: login.html?error=1");
    exit;
}