<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


 if (!isset($_SESSION['user_id'])) {
     header("Location: /rbsms/auth/login.html");
    exit;
}

$current_user_id = $_SESSION['user_id'];
$current_username = $_SESSION['username'];
$current_role = $_SESSION['role'];
?>