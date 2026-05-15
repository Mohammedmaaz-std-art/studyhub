<?php
require_once '../middleware/auth_check.php';
require_once '../config/db.php';

// Total subjects
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM subjects WHERE user_id = ?");
$stmt->execute([$current_user_id]);
$subject_count = $stmt->fetch()['total'];

// Total tasks
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tasks WHERE user_id = ?");
$stmt->execute([$current_user_id]);
$task_count = $stmt->fetch()['total'];

// Done tasks
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tasks WHERE user_id = ? AND is_done = 1");
$stmt->execute([$current_user_id]);
$done_count = $stmt->fetch()['total'];

$pending_count  = $task_count - $done_count;
$completion_pct = $task_count > 0 ? round(($done_count / $task_count) * 100) : 0;

// Subject breakdown
$stmt = $pdo->prepare("
    SELECT s.name, s.color_tag,
           COUNT(t.id) as total,
           SUM(t.is_done) as done
    FROM subjects s
    LEFT JOIN tasks t ON t.subject_id = s.id
    WHERE s.user_id = ?
    GROUP BY s.id
    ORDER BY s.name
");
$stmt->execute([$current_user_id]);
$subject_breakdown = $stmt->fetchAll();

// Weekly tasks completed (last 7 days)
$stmt = $pdo->prepare("
    SELECT DAYNAME(completed_at) as day_name,
           DAYOFWEEK(completed_at) as day_num,
           COUNT(*) as count
    FROM progress_logs
    WHERE user_id = ?
      AND completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DAYOFWEEK(completed_at), DAYNAME(completed_at)
    ORDER BY DAYOFWEEK(completed_at)
");
$stmt->execute([$current_user_id]);
$weekly_raw = $stmt->fetchAll();

// Build full week array (Mon–Sun)
$days = ['Mon' => 0, 'Tue' => 0, 'Wed' => 0, 'Thu' => 0, 'Fri' => 0, 'Sat' => 0, 'Sun' => 0];
foreach ($weekly_raw as $row) {
    $short = substr($row['day_name'], 0, 3);
    if (isset($days[$short])) $days[$short] = (int)$row['count'];
}
$max_day = max($days) ?: 1;

// Today's activity log
$stmt = $pdo->prepare("
    SELECT pl.duration_minutes, pl.completed_at,
           t.title, s.name as subject_name, s.color_tag
    FROM progress_logs pl
    JOIN tasks t ON pl.task_id = t.id
    JOIN subjects s ON t.subject_id = s.id
    WHERE pl.user_id = ? AND DATE(pl.completed_at) = CURDATE()
    ORDER BY pl.completed_at DESC
");
$stmt->execute([$current_user_id]);
$today_logs = $stmt->fetchAll();
$today_minutes = array_sum(array_column($today_logs, 'duration_minutes'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress - StudyHub</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f4f8; font-size: 14px; }

        nav { background: #1a1a2e; padding: 16px 32px; display: flex; justify-content: space-between; align-items: center; }
        nav .logo { color: #fff; font-size: 20px; font-weight: 700; }
        nav .nav-right { display: flex; align-items: center; gap: 20px; }
        nav .nav-right a { color: #aaa; text-decoration: none; font-size: 14px; transition: color 0.2s; }
        nav .nav-right a:hover { color: #fff; }
        nav .nav-right a.active { color: #4A90D9; font-weight: 500; }
        nav .role-badge { background: #4A90D9; color: #fff; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: capitalize; }
        nav .role-badge.intermediate { background: #8e44ad; }
        nav a.logout { color: #ff6b6b !important; }

        .container { max-width: 900px; margin: 32px auto; padding: 0 24px; }

        .page-header { margin-bottom: 24px; }
        .page-header h1 { font-size: 22px; color: #1a1a2e; }
        .page-header p  { font-size: 13px; color: #888; margin-top: 4px; }

        /* Stat cards */
        .stat-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 24px; }
        .stat-card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); text-align: center; }
        .stat-card .num { font-size: 34px; font-weight: 700; }
        .stat-card .lbl { font-size: 12px; color: #888; margin-top: 4px; }
        .blue   .num { color: #4A90D9; }
        .green  .num { color: #27ae60; }
        .orange .num { color: #f39c12; }
        .purple .num { color: #8e44ad; }

        /* Two column */
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }

        .card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .card h3 { font-size: 14px; color: #1a1a2e; margin-bottom: 16px; font-weight: 600; }

        /* Completion ring */
        .ring-wrap { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 10px 0; }
        .ring { position: relative; width: 130px; height: 130px; }
        .ring svg { transform: rotate(-90deg); }
        .ring-bg   { fill: none; stroke: #f0f4f8; stroke-width: 10; }
        .ring-fill { fill: none; stroke: #4A90D9; stroke-width: 10; stroke-linecap: round; stroke-dasharray: 283; transition: stroke-dashoffset 1.2s ease; }
        .ring-text { position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); font-size: 24px; font-weight: 700; color: #1a1a2e; }
        .ring-sub  { font-size: 12px; color: #888; margin-top: 10px; }

        /* Subject breakdown */
        .subject-row { margin-bottom: 14px; }
        .subject-row:last-child { margin-bottom: 0; }
        .subject-row .name { font-size: 12px; color: #555; margin-bottom: 5px; display: flex; justify-content: space-between; }
        .bar-bg   { background: #f0f4f8; border-radius: 30px; height: 8px; overflow: hidden; }
        .bar-fill { height: 100%; border-radius: 30px; transition: width 1s ease; }

        /* Weekly bar chart */
        .bar-chart { display: flex; align-items: flex-end; gap: 8px; height: 90px; }
        .day-bar  { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 3px; }
        .day-fill { width: 100%; border-radius: 4px 4px 0 0; background: #4A90D9; min-height: 4px; transition: height 1s ease; }
        .day-label { font-size: 10px; color: #aaa; }
        .day-val   { font-size: 10px; color: #555; font-weight: 500; }
        .day-bar.today .day-fill  { background: #27ae60; }
        .day-bar.today .day-label { color: #27ae60; font-weight: 600; }

        /* Today's log */
        .log-item { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f5f5f5; }
        .log-item:last-child { border-bottom: none; }
        .log-dot  { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
        .log-info { flex: 1; font-size: 12px; color: #555; }
        .log-info span { color: #aaa; }
        .log-time { font-size: 11px; color: #888; font-weight: 500; }
        .log-total { margin-top: 12px; padding-top: 12px; border-top: 1px solid #f0f4f8; font-size: 13px; color: #555; }

        .empty-log { font-size: 13px; color: #bbb; text-align: center; padding: 20px 0; }
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
        <a href="../dashboard/index.php">Dashboard</a>
        <a href="../subjects/index.php">Subjects</a>
        <a href="../tasks/index.php">Tasks</a>
        <a href="../progress/index.php" class="active">Progress</a>
        <span class="role-badge <?= $current_role ?>"><?= ucfirst($current_role) ?></span>
        <a href="../auth/logout.php" class="logout">Logout</a>
    </div>
</nav>

<div class="container">

    <div class="page-header">
        <h1>My Progress</h1>
        <p>Track your study activity and completion</p>
    </div>

    <!-- Stat Cards -->
    <div class="stat-cards">
        <div class="stat-card blue">
            <div class="num"><?= $subject_count ?></div>
            <div class="lbl">Subjects</div>
        </div>
        <div class="stat-card green">
            <div class="num"><?= $done_count ?></div>
            <div class="lbl">Tasks Done</div>
        </div>
        <div class="stat-card orange">
            <div class="num"><?= $pending_count ?></div>
            <div class="lbl">Pending</div>
        </div>
        <div class="stat-card purple">
            <div class="num"><?= $completion_pct ?>%</div>
            <div class="lbl">Completion</div>
        </div>
    </div>

    <!-- Completion Ring + Subject Breakdown -->
    <div class="two-col">

        <div class="card">
            <h3>Overall Completion</h3>
            <div class="ring-wrap">
                <div class="ring">
                    <?php
                    $circumference = 283;
                    $offset = $circumference - ($completion_pct / 100 * $circumference);
                    ?>
                    <svg viewBox="0 0 100 100" width="130" height="130">
                        <circle class="ring-bg" cx="50" cy="50" r="45"/>
                        <circle class="ring-fill" cx="50" cy="50" r="45"
                            style="stroke-dashoffset: <?= $offset ?>"/>
                    </svg>
                    <div class="ring-text"><?= $completion_pct ?>%</div>
                </div>
                <div class="ring-sub"><?= $done_count ?> of <?= $task_count ?> tasks completed</div>
            </div>
        </div>

        <div class="card">
            <h3>By Subject</h3>
            <?php if (empty($subject_breakdown)): ?>
                <div class="empty-log">No subjects yet</div>
            <?php else: ?>
                <?php foreach ($subject_breakdown as $sb):
                    $pct = $sb['total'] > 0 ? round(($sb['done'] / $sb['total']) * 100) : 0;
                ?>
                <div class="subject-row">
                    <div class="name">
                        <span><?= htmlspecialchars($sb['name']) ?></span>
                        <span><?= (int)$sb['done'] ?>/<?= (int)$sb['total'] ?></span>
                    </div>
                    <div class="bar-bg">
                        <div class="bar-fill"
                             style="width:<?= $pct ?>%; background:<?= htmlspecialchars($sb['color_tag']) ?>;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

    <!-- Weekly Chart + Today's Log -->
    <div class="two-col">

        <div class="card">
            <h3>Tasks Completed — This Week</h3>
            <div class="bar-chart">
                <?php
                $today_short = date('D'); // e.g. 'Mon'
                foreach ($days as $day => $count):
                    $height = $count > 0 ? round(($count / $max_day) * 70) : 4;
                    $is_today = ($day === $today_short);
                ?>
                <div class="day-bar <?= $is_today ? 'today' : '' ?>">
                    <div class="day-val"><?= $count ?></div>
                    <div class="day-fill" style="height:<?= $height ?>px;"></div>
                    <div class="day-label"><?= $day ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <h3>Today's Activity</h3>
            <?php if (empty($today_logs)): ?>
                <div class="empty-log">No activity logged today</div>
            <?php else: ?>
                <?php foreach ($today_logs as $log): ?>
                <div class="log-item">
                    <div class="log-dot" style="background:<?= htmlspecialchars($log['color_tag']) ?>;"></div>
                    <div class="log-info">
                        <?= htmlspecialchars($log['title']) ?>
                        <span>· <?= htmlspecialchars($log['subject_name']) ?></span>
                    </div>
                    <div class="log-time"><?= $log['duration_minutes'] ?> min</div>
                </div>
                <?php endforeach; ?>
                <div class="log-total">
                    Total today: <strong><?= $today_minutes ?> min</strong>
                </div>
            <?php endif; ?>
        </div>

    </div>

</div>
<?php include '../includes/study_session_btn.php'; ?>
</body>
</html>