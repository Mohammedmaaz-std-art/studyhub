<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - StudyHub</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f4f8;
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh;
        }
        .card {
            background: #fff; padding: 40px; border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            width: 100%; max-width: 460px;
        }
        h2 { text-align: center; margin-bottom: 6px; color: #1a1a2e; font-size: 24px; }
        .subtitle { text-align: center; font-size: 13px; color: #888; margin-bottom: 28px; }
        .form-group { margin-bottom: 16px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 0; }
        .form-row .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; font-size: 13px; color: #555; font-weight: 500; }
        input, select {
            width: 100%; padding: 10px 14px; border: 1px solid #ddd;
            border-radius: 8px; font-size: 14px; outline: none;
            transition: border 0.2s; font-family: inherit;
        }
        input:focus, select:focus { border-color: #4A90D9; }
        .course-note { font-size: 11px; color: #aaa; margin-top: 4px; }
        button {
            width: 100%; padding: 12px; background: #4A90D9; color: #fff;
            border: none; border-radius: 8px; font-size: 15px;
            cursor: pointer; margin-top: 4px; transition: background 0.2s;
            font-family: inherit;
        }
        button:hover { background: #357ABD; }
        .error {
            background: #ffe0e0; color: #c0392b; padding: 10px 14px;
            border-radius: 8px; font-size: 13px; margin-bottom: 16px;
        }
        .bottom-link { text-align: center; margin-top: 16px; font-size: 13px; color: #777; }
        .bottom-link a { color: #4A90D9; text-decoration: none; font-weight: 500; }
    </style>
</head>
<body>
<div class="card">
    <h2>📚 StudyHub</h2>
    <p class="subtitle">Create your account</p>

    <?php
    $errorMsg = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once '../config/db.php';

        $username         = trim($_POST['username']         ?? '');
        $email            = trim($_POST['email']            ?? '');
        $password         =      $_POST['password']         ?? '';
        $confirm_password =      $_POST['confirm_password'] ?? '';
        $course           = trim($_POST['course']           ?? '');

        $allowed_courses = [
            'BCA','BSc','BCom','BA','B.Tech/BE',
            'BBA','B.Sc IT','Diploma','Other'
        ];

        $errors = [];
        if (empty($username))
            $errors[] = "Username is required.";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
            $errors[] = "Valid email is required.";
        if (strlen($password) < 6)
            $errors[] = "Password must be at least 6 characters.";
        if ($password !== $confirm_password)
            $errors[] = "Passwords do not match.";
        if (!in_array($course, $allowed_courses))
            $errors[] = "Please select your course.";

        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = "Email already registered.";
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $pdo->prepare("
                    INSERT INTO users (username, email, password_hash, course)
                    VALUES (?, ?, ?, ?)
                ")->execute([$username, $email, $hash, $course]);

                header("Location: login.html?registered=1");
                exit;
            }
        }

        if (!empty($errors)) $errorMsg = implode(' ', $errors);
    }
    ?>

    <?php if ($errorMsg): ?>
        <div class="error">❌ <?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <div class="form-row">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Your name" required>
            </div>
            <div class="form-group">
                <label>Course</label>
                <select name="course" required>
                    <option value="">Select your course</option>
                    <option value="BCA">BCA</option>
                    <option value="BSc">BSc</option>
                    <option value="BCom">BCom</option>

                </select>
                <div class="course-note">Helps suggest relevant subjects</div>
            </div>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="you@email.com" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password"
                       placeholder="Min 6 characters" required>
            </div> 
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password"
                       placeholder="Repeat password" required>
            </div>
        </div>

        <button type="submit">Create Account</button>
    </form>

    <div class="bottom-link">
        Already have an account? <a href="login.html">Login</a>
    </div>
</div>
</body>
</html>