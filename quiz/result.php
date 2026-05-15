<?php
require_once '../middleware/auth_check.php';
require_once '../config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$already    = isset($_GET['already']);
$subject_id = (int)($_GET['subject_id'] ?? 0);

// Already certified path
if ($already && $subject_id) {
    $stmt = $pdo->prepare("
        SELECT c.*, u.username, u.course
        FROM certificates c
        JOIN users u ON u.id = c.user_id
        WHERE c.user_id = ? AND c.subject_id = ?
        ORDER BY c.issued_at DESC LIMIT 1
    ");
    $stmt->execute([$current_user_id, $subject_id]);
    $cert = $stmt->fetch();

    if (!$cert) {
        header("Location: ../subjects/index.php");
        exit;
    }

    $result = [
        'score'      => $cert['score'],
        'total'      => 10,
        'score_pct'  => $cert['score'],
        'passed'     => true,
        'subject_id' => $subject_id,
        'subject'    => $cert['subject_name'],
        'results'    => []
    ];
} else {
    // Fresh quiz result from session
    $result = $_SESSION['quiz_result'] ?? null;
    if (!$result) {
        header("Location: ../subjects/index.php");
        exit;
    }
    unset($_SESSION['quiz_result']);
}

$passed     = $result['passed'];
$score_pct  = $result['score_pct'];
$score      = $result['score'];
$total      = $result['total'];
$subject    = $result['subject'];
$subject_id = $result['subject_id'];
$reviews    = $result['results'] ?? [];

// Ring math
$circumference = 283;
$offset     = $circumference - ($score_pct / 100 * $circumference);
$ring_color = $passed ? '#22c55e' : '#DC2626';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Result — StudyHub</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
        }

        nav {
            background: #1a1a2e; padding: 16px 32px;
            display: flex; justify-content: space-between; align-items: center;
        }
        nav .logo { color: #fff; font-size: 20px; font-weight: 700; }
        nav a { color: #aaa; text-decoration: none; font-size: 14px; }
        nav a:hover { color: #fff; }

        .container {
            max-width: 620px; margin: 48px auto;
            padding: 0 24px; text-align: center;
        }

        /* Result card */
        .result-card {
            background: #fff; border-radius: 16px;
            padding: 48px 40px; margin-bottom: 24px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }

        .result-icon { font-size: 60px; margin-bottom: 16px; }

        .result-title {
            font-size: 28px; font-weight: 700; margin-bottom: 8px;
        }
        .title-pass { color: #16a34a; }
        .title-fail { color: #DC2626; }

        .result-desc {
            font-size: 15px; color: #666;
            margin-bottom: 32px; line-height: 1.65;
        }

        /* Score ring */
        .ring-wrap {
            position: relative; width: 140px; height: 140px;
            margin: 0 auto 24px;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
        }
        .ring-wrap svg {
            position: absolute; top: 0; left: 0;
            transform: rotate(-90deg);
        }
        .ring-num {
            position: relative; z-index: 1;
            font-size: 32px; font-weight: 800;
        }
        .ring-lbl {
            position: relative; z-index: 1;
            font-size: 12px; color: #888;
        }
        .ring-pass .ring-num { color: #16a34a; }
        .ring-fail .ring-num { color: #DC2626; }

        /* Stats */
        .stats-row {
            display: flex; gap: 24px;
            justify-content: center; margin-bottom: 32px;
        }
        .stat .val {
            font-size: 24px; font-weight: 700; color: #1a1a2e;
        }
        .stat .lbl { font-size: 12px; color: #888; margin-top: 2px; }

        /* Buttons */
        .btn-row { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }

        .btn {
            padding: 12px 28px; border-radius: 10px;
            font-size: 14px; font-weight: 600;
            cursor: pointer; border: none;
            font-family: inherit; text-decoration: none;
            display: inline-block; transition: all 0.2s;
        }
        .btn-cert  { background: #22c55e; color: #fff; }
        .btn-cert:hover  { background: #16a34a; transform: translateY(-2px); }
        .btn-retry { background: #4A90D9; color: #fff; }
        .btn-retry:hover { background: #357ABD; }
        .btn-back  { background: #f0f4f8; color: #555; }
        .btn-back:hover  { background: #e0e7ef; }

        /* Review section */
        .review-card {
            background: #fff; border-radius: 12px;
            padding: 24px; text-align: left;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        }
        .review-card h2 {
            font-size: 16px; color: #1a1a2e;
            margin-bottom: 16px; font-weight: 600;
        }

        .review-item {
            padding: 14px 0;
            border-bottom: 1px solid #f0f4f8;
        }
        .review-item:last-child { border-bottom: none; }

        .review-q {
            font-size: 13px; font-weight: 600;
            color: #1a1a2e; margin-bottom: 10px;
        }

        .review-opt {
            font-size: 12px; padding: 6px 12px;
            border-radius: 6px; margin-bottom: 4px;
            color: #555;
        }
        .opt-user-correct { background: #e0f7e9; color: #16a34a; font-weight: 600; }
        .opt-user-wrong   { background: #ffe0e0; color: #DC2626; font-weight: 600; }
        .opt-correct-only { background: #e0f7e9; color: #16a34a; }
    </style>
</head>
<body>

<nav>
    <div class="logo">📚 StudyHub</div>
    <a href="../subjects/index.php">← Back to Subjects</a>
</nav>

<div class="container">

    <div class="result-card">

        <div class="result-icon"><?= $passed ? '🎉' : '💪' ?></div>

        <div class="result-title <?= $passed ? 'title-pass' : 'title-fail' ?>">
            <?= $passed ? 'You passed!' : 'Not quite yet' ?>
        </div>

        <p class="result-desc">
            <?php if ($passed): ?>
                Great work on <strong><?= htmlspecialchars($subject) ?></strong>.
                You scored <?= $score_pct ?>% — your certificate is ready to download.
            <?php else: ?>
                You scored <?= $score_pct ?>% on
                <strong><?= htmlspecialchars($subject) ?></strong>.
                You need 70% to pass. Review below and try again — no limit on attempts.
            <?php endif; ?>
        </p>

        <!-- Score ring -->
        <div class="ring-wrap <?= $passed ? 'ring-pass' : 'ring-fail' ?>">
            <svg viewBox="0 0 100 100" width="140" height="140">
                <circle fill="none" stroke="#f0f4f8" stroke-width="10"
                        cx="50" cy="50" r="45"/>
                <circle fill="none" stroke="<?= $ring_color ?>"
                        stroke-width="10" stroke-linecap="round"
                        stroke-dasharray="<?= $circumference ?>"
                        stroke-dashoffset="<?= $offset ?>"
                        cx="50" cy="50" r="45"/>
            </svg>
            <div class="ring-num"><?= $score_pct ?>%</div>
            <div class="ring-lbl">Score</div>
        </div>

        <div class="stats-row">
            <div class="stat">
                <div class="val"><?= $score ?></div>
                <div class="lbl">Correct</div>
            </div>
            <div class="stat">
                <div class="val"><?= $total - $score ?></div>
                <div class="lbl">Wrong</div>
            </div>
            <div class="stat">
                <div class="val"><?= $total ?></div>
                <div class="lbl">Total</div>
            </div>
        </div>

        <div class="btn-row">
            <?php if ($passed): ?>
                <a href="../certificate/generate.php?subject_id=<?= $subject_id ?>"
                   class="btn btn-cert">🏆 Download Certificate</a>
                <a href="../subjects/index.php" class="btn btn-back">Back to Subjects</a>
            <?php else: ?>
                <a href="../quiz/index.php?subject_id=<?= $subject_id ?>"
                   class="btn btn-retry">🔄 Try Again</a>
                <a href="../subjects/index.php" class="btn btn-back">Back to Subjects</a>
            <?php endif; ?>
        </div>

    </div>

    <!-- Answer review -->
    <?php if (!empty($reviews)): ?>
    <div class="review-card">
        <h2>Answer Review</h2>
        <?php foreach ($reviews as $i => $r): ?>
        <div class="review-item">
            <div class="review-q">
                <?= $i + 1 ?>. <?= htmlspecialchars($r['question']) ?>
                <?= $r['is_correct'] ? ' ✅' : ' ❌' ?>
            </div>
            <?php foreach ($r['options'] as $key => $text): ?>
                <?php
                if ($key === $r['correct'] && $key === $r['user_answer'])
                    $cls = 'opt-user-correct';
                elseif ($key === $r['user_answer'] && !$r['is_correct'])
                    $cls = 'opt-user-wrong';
                elseif ($key === $r['correct'] && $r['user_answer'] !== $r['correct'])
                    $cls = 'opt-correct-only';
                else
                    $cls = '';

                if (!$cls) continue;
                ?>
                <div class="review-opt <?= $cls ?>">
                    <?= strtoupper($key) ?>. <?= htmlspecialchars($text) ?>
                    <?php if ($key === $r['user_answer'] && $cls === 'opt-user-wrong'): ?>
                        ← Your answer
                    <?php elseif ($cls === 'opt-correct-only'): ?>
                        ← Correct answer
                    <?php elseif ($cls === 'opt-user-correct'): ?>
                        ← Correct ✓
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>
</body>
</html>