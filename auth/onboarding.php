<?php
session_start();

require_once '../config/db.php';
require_once '../middleware/auth_check.php';
 
// Guard — must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.html");
    exit;
}
 
// If already done — skip straight to dashboard
$check = $pdo->prepare("SELECT onboarding_done FROM users WHERE id = ?");
$check->execute([$_SESSION['user_id']]);
$row = $check->fetch();
if ($row && $row['onboarding_done']) {
    header("Location: ../dashboard/index.php");
    exit;
}
 
$error = '';
 
// Handle POST — role selected
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? '';
 
    if (!in_array($role, ['beginner', 'intermediate'])) {
        $error = "Please select one of the options below.";
    } else {
        // Save role and mark onboarding complete
        $pdo->prepare("
            UPDATE users SET role = ?, onboarding_done = 1 WHERE id = ?
        ")->execute([$role, $_SESSION['user_id']]);
 
        $_SESSION['role'] = $role;
 
        header("Location: ../dashboard/index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>One Quick Question — StudyHub</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #060508;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 24px;
        }

        .container {
            width: 100%; max-width: 560px;
            text-align: center;
        }

        .logo {
            font-size: 15px; font-weight: 700;
            color: rgba(240,234,248,0.5);
            margin-bottom: 48px;
            letter-spacing: 0.5px;
        }

        .step-label {
            font-size: 11px; font-weight: 600;
            letter-spacing: 3px; text-transform: uppercase;
            color: #22c55e; margin-bottom: 16px;
        }

        h1 {
            font-size: 30px; font-weight: 700;
            color: #f0eaf8; line-height: 1.2;
            margin-bottom: 12px;
        }

        .sub {
            font-size: 14px; color: rgba(240,234,248,0.45);
            margin-bottom: 48px; line-height: 1.7;
        }

        .choices {
            display: flex; flex-direction: column; gap: 14px;
            margin-bottom: 32px;
        }

        .choice-btn {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px; padding: 24px 28px;
            text-align: left; cursor: pointer;
            transition: all 0.2s; position: relative;
            width: 100%;
            font-family: 'Segoe UI', sans-serif;
        }

        .choice-btn:hover {
            border-color: rgba(34,197,94,0.4);
            background: rgba(34,197,94,0.05);
            transform: translateY(-2px);
        }

        .choice-btn.selected {
            border-color: #22c55e;
            background: rgba(34,197,94,0.08);
        }

        .choice-btn input[type="radio"] {
            position: absolute; opacity: 0; pointer-events: none;
        }

        .choice-icon {
            font-size: 24px; margin-bottom: 10px; display: block;
        }

        .choice-title {
            font-size: 16px; font-weight: 600;
            color: #f0eaf8; margin-bottom: 6px;
        }

        .choice-desc {
            font-size: 13px; color: rgba(240,234,248,0.45);
            line-height: 1.6;
        }

        .choice-tag {
            position: absolute; top: 16px; right: 16px;
            font-size: 10px; font-weight: 700; letter-spacing: 1.5px;
            text-transform: uppercase; padding: 3px 10px;
            border-radius: 20px;
        }

        .tag-beginner     { background: rgba(34,197,94,0.12);  color: #22c55e; }
        .tag-intermediate { background: rgba(167,139,250,0.12); color: #a78bfa; }

        .error-msg {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.2);
            color: #fca5a5; padding: 10px 16px;
            border-radius: 8px; font-size: 13px;
            margin-bottom: 16px;
        }

        .submit-btn {
            width: 100%; padding: 14px;
            background: #22c55e; color: #fff;
            border: none; border-radius: 10px;
            font-size: 15px; font-weight: 600;
            cursor: pointer; font-family: inherit;
            transition: all 0.2s;
            opacity: 0.5;
            pointer-events: none;
        }

        .submit-btn.active {
            opacity: 1;
            pointer-events: all;
        }

        .submit-btn.active:hover {
            background: #16a34a;
            transform: translateY(-2px);
        }

        .note {
            font-size: 12px; color: rgba(240,234,248,0.25);
            margin-top: 20px; line-height: 1.6;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="logo">📚 StudyHub</div>

    <div class="step-label">One question before we begin</div>

    <h1>What's your relationship<br>with studying?</h1>
    <p class="sub">
        Be honest. There is no wrong answer.<br>
        This shapes how the system supports you.
    </p>

    <?php if (!empty($error)): ?>
        <div class="error-msg">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/auth/onboarding.php" id="onboardForm">
        <div class="choices">

            <label class="choice-btn" id="btn-beginner"
                   onclick="selectRole('beginner')">
                <input type="radio" name="role" value="beginner" id="r-beginner">
                <span class="choice-tag tag-beginner">Beginner</span>
                <span class="choice-icon">🌱</span>
                <div class="choice-title">I struggle to start and stay consistent</div>
                <div class="choice-desc">
                    I know I should study but getting myself to actually sit down
                    is the hard part. Distractions, procrastination, or just not
                    knowing where to begin — that's me.
                </div>
            </label>

            <label class="choice-btn" id="btn-intermediate"
                   onclick="selectRole('intermediate')">
                <input type="radio" name="role" value="intermediate" id="r-intermediate">
                <span class="choice-tag tag-intermediate">Intermediate</span>
                <span class="choice-icon">🔥</span>
                <div class="choice-title">I study but feel overwhelmed and scattered</div>
                <div class="choice-desc">
                    I put in effort but there's always too much to do, too much
                    pressure, and I never feel like I'm making real progress.
                    I study hard but feel like I'm spinning in circles.
                </div>
            </label>

        </div>

        <button type="submit" class="submit-btn" id="submitBtn">
            Set my role and start →
        </button>
    </form>

    <p class="note">
        This is shown only once. Your role shapes which tools
        and prompts you see inside StudyHub.
    </p>
</div>

<script>
function selectRole(role) {
    document.getElementById('btn-beginner')
        .classList.toggle('selected', role === 'beginner');
    document.getElementById('btn-intermediate')
        .classList.toggle('selected', role === 'intermediate');

    document.getElementById('r-' + role).checked = true;

    document.getElementById('submitBtn').classList.add('active');
}
</script>

</body>
</html>