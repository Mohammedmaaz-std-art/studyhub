<?php
require_once '../middleware/auth_check.php';
require_once '../config/db.php';

// Fetch basic stats for this user
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM subjects WHERE user_id = ?");
$stmt->execute([$current_user_id]);
$subject_count = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tasks WHERE user_id = ?");
$stmt->execute([$current_user_id]);
$task_count = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tasks WHERE user_id = ? AND is_done = 1");
$stmt->execute([$current_user_id]);
$done_count = $stmt->fetch()['total'];

$completion_pct = $task_count > 0 ? round(($done_count / $task_count) * 100) : 0;

// Subjects with quiz unlocked but no certificate yet
$stmt = $pdo->prepare("
    SELECT s.id, s.name, s.color_tag,
           SUM(CASE WHEN t.is_done = 1 THEN 1 ELSE 0 END) as done_count
    FROM subjects s
    LEFT JOIN tasks t ON t.subject_id = s.id
    WHERE s.user_id = ?
    GROUP BY s.id
    HAVING done_count >= 5
       AND s.id NOT IN (
           SELECT subject_id FROM certificates WHERE user_id = ?
       )
    ORDER BY done_count DESC
");
$stmt->execute([$current_user_id, $current_user_id]);
$quiz_unlocked = $stmt->fetchAll();

// Earned certificates
$stmt = $pdo->prepare("SELECT * FROM certificates WHERE user_id = ? ORDER BY issued_at DESC");
$stmt->execute([$current_user_id]);
$certificates = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - StudyHub</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
        }

        /* Navbar */
        nav {
            background: #1a1a2e;
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        nav .logo {
            color: #fff;
            font-size: 20px;
            font-weight: 700;
        }

        nav .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        nav .nav-right span {
            color: #aaa;
            font-size: 14px;
        }

        nav .role-badge {
            background: #4A90D9;
            color: #fff;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }

        nav .role-badge.intermediate {
            background: #8e44ad;
        }

        nav a.logout {
            color: #ff6b6b;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        nav a.logout:hover { color: #ff4444; }

        /* Main content */
        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 24px;
        }

        .welcome {
            margin-bottom: 32px;
        }

        .welcome h1 {
            font-size: 26px;
            color: #1a1a2e;
        }

        .welcome p {
            color: #777;
            margin-top: 6px;
            font-size: 14px;
        }

        /* Stat cards */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            text-align: center;
        }

        .card .number {
            font-size: 42px;
            font-weight: 700;
            color: #4A90D9;
        }

        .card .label {
            font-size: 13px;
            color: #888;
            margin-top: 6px;
        }

        .card.green .number  { color: #27ae60; }
        .card.purple .number { color: #8e44ad; }

        /* Progress bar */
        .progress-section {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            margin-bottom: 32px;
        }

        .progress-section h3 {
            font-size: 16px;
            color: #1a1a2e;
            margin-bottom: 16px;
        }

        .progress-bar-bg {
            background: #f0f4f8;
            border-radius: 30px;
            height: 16px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 30px;
            background: linear-gradient(90deg, #4A90D9, #27ae60);
            transition: width 1s ease;
        }

        .progress-label {
            text-align: right;
            font-size: 13px;
            color: #555;
            margin-top: 8px;
        }

        /* Quick links */
        .quick-links h3 {
            font-size: 16px;
            color: #1a1a2e;
            margin-bottom: 16px;
        }

        .links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
        }

        .link-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            text-decoration: none;
            color: #1a1a2e;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            transition: transform 0.2s, box-shadow 0.2s;
            font-size: 14px;
            font-weight: 500;
        }

        .link-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        }

        .link-card .icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        /* Intermediate-only section */
        .intermediate-only {
            display: none;
            margin-top: 32px;
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        }

        .intermediate-only h3 {
            font-size: 16px;
            color: #8e44ad;
            margin-bottom: 8px;
        }

        .intermediate-only p {
            font-size: 13px;
            color: #777;
        }
        a{
            text-decoration:none;
            color:white;
            font-weight:inherit;

        }
    </style>
</head>
<body>

<nav>
    <div class="logo">📚 <a href=".././index.html">StudyHub</a></div>
    <div class="nav-right">
        <span>Hello, <?= htmlspecialchars($current_username) ?></span>
        <span class="role-badge <?= $current_role ?>">
            <?= ucfirst($current_role) ?>
        </span>
        <a href="../auth/logout.php" class="logout">Logout</a>
    </div>
</nav>

<div class="container">

    <div class="welcome">
        <h1>Welcome back, <?= htmlspecialchars($current_username) ?> 👋</h1>
        <p>Here's your study overview for today.</p>
    </div>

    <!-- Stat Cards -->
    <div class="cards">
        <div class="card">
            <div class="number"><?= $subject_count ?></div>
            <div class="label">Subjects</div>
        </div>
        <div class="card green">
            <div class="number"><?= $task_count ?></div>
            <div class="label">Total Tasks</div>
        </div>
        <div class="card purple">
            <div class="number"><?= $done_count ?></div>
            <div class="label">Completed</div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="progress-section">
        <h3>Overall Completion</h3>
        <div class="progress-bar-bg">
            <div class="progress-bar-fill" style="width: <?= $completion_pct ?>%"></div>
        </div>
        <div class="progress-label"><?= $completion_pct ?>% complete</div>
    </div>

    <!-- Quick Links -->
    <div class="quick-links">
        <h3>Quick Access</h3>
        <div class="links-grid">
            <a href="../subjects/index.php" class="link-card">
                <div class="icon">📖</div>
                Subjects
            </a>
            <a href="../tasks/index.php" class="link-card">
                <div class="icon">✅</div>
                Tasks
            </a>
            <a href="../progress/index.php" class="link-card">
                <div class="icon">📈</div>
                Progress
            </a>
            <a href="../tasks/focustimer.html" class="link-card">
                <div class="icon">⏱️</div>
                Focus Timer
            </a>
        </div>
    </div>

    <!-- Intermediate only section -->
    <div class="intermediate-only" id="intermediatePanel">
        <h3>🔓 Intermediate Features</h3>
        <p>You have access to task steps, custom timer durations, and detailed weekly analytics. These will appear as you build your subjects and tasks.</p>
    </div>

    <!-- Quiz Unlocked Notifications -->
    <?php if (!empty($quiz_unlocked)): ?>
    <div style="margin-top:32px;">
        <h3 style="font-size:16px;color:#1a1a2e;margin-bottom:14px;">
            📝 Quizzes Available
        </h3>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <?php foreach ($quiz_unlocked as $q): ?>
            <div style="background:#fff;border-radius:12px;padding:16px 20px;
                        box-shadow:0 2px 12px rgba(0,0,0,0.07);
                        display:flex;justify-content:space-between;align-items:center;
                        border-left:4px solid #f59e0b;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:10px;height:10px;border-radius:50%;
                                background:<?= htmlspecialchars($q['color_tag']) ?>;"></div>
                    <div>
                        <div style="font-size:14px;font-weight:600;color:#1a1a2e;">
                            <?= htmlspecialchars($q['name']) ?>
                        </div>
                        <div style="font-size:12px;color:#888;margin-top:2px;">
                            <?= (int)$q['done_count'] ?> tasks completed — quiz unlocked!
                        </div>
                    </div>
                </div>
                <a href="../quiz/index.php?subject_id=<?= $q['id'] ?>"
                   style="background:#f59e0b;color:#fff;padding:8px 18px;
                          border-radius:8px;font-size:13px;font-weight:600;
                          text-decoration:none;white-space:nowrap;transition:background 0.2s;"
                   onmouseover="this.style.background='#d97706'"
                   onmouseout="this.style.background='#f59e0b'">
                    Take Quiz →
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Earned Certificates -->
    <?php if (!empty($certificates)): ?>
    <div style="margin-top:32px;margin-bottom:32px;">
        <h3 style="font-size:16px;color:#1a1a2e;margin-bottom:14px;">
            🏆 Your Certificates
        </h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px;">
            <?php foreach ($certificates as $c): ?>
            <div style="background:linear-gradient(135deg,#0f2010,#1a2e1a);
                        border:1px solid rgba(34,197,94,0.3);
                        border-radius:12px;padding:20px;
                        box-shadow:0 2px 12px rgba(34,197,94,0.1);">
                <div style="font-size:28px;margin-bottom:8px;">🏆</div>
                <div style="font-size:14px;font-weight:700;color:#22c55e;margin-bottom:4px;">
                    <?= htmlspecialchars($c['subject_name']) ?>
                </div>
                <div style="font-size:12px;color:rgba(255,255,255,0.4);margin-bottom:4px;">
                    Score: <?= $c['score'] ?>%
                </div>
                <div style="font-size:11px;color:rgba(255,255,255,0.3);margin-bottom:14px;">
                    <?= date('d M Y', strtotime($c['issued_at'])) ?>
                </div>
                <a href="../certificate/generate.php?subject_id=<?= $c['subject_id'] ?>"
                   style="display:block;text-align:center;background:rgba(34,197,94,0.15);
                          color:#22c55e;padding:7px;border-radius:7px;
                          font-size:12px;font-weight:600;text-decoration:none;
                          border:1px solid rgba(34,197,94,0.2);transition:background 0.2s;"
                   onmouseover="this.style.background='rgba(34,197,94,0.25)'"
                   onmouseout="this.style.background='rgba(34,197,94,0.15)'">
                    Download Certificate
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Inject role into JS -->
<script>
    const USER_ROLE = '<?= $current_role ?>';

    if (USER_ROLE === 'intermediate') {
        document.getElementById('intermediatePanel').style.display = 'block';
    }
</script>
 <?php include '../includes/study_session_btn.php'; ?>
</body>
</html>