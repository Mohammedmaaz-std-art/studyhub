<?php
require_once '../middleware/auth_check.php';
require_once '../config/db.php';
require_once '../config/question_bank.php';

$subject_id = (int)($_GET['subject_id'] ?? 0);
if (!$subject_id) {
    header("Location: ../subjects/index.php");
    exit;
}

// Verify subject belongs to user and get details
$stmt = $pdo->prepare("
    SELECT s.*, u.course
    FROM subjects s
    JOIN users u ON u.id = s.user_id
    WHERE s.id = ? AND s.user_id = ?
");
$stmt->execute([$subject_id, $current_user_id]);
$subject = $stmt->fetch();

if (!$subject) {
    header("Location: ../subjects/index.php");
    exit;
}

// Check eligibility — 5 done tasks in this subject
$stmt = $pdo->prepare("
    SELECT COUNT(*) as done
    FROM tasks
    WHERE subject_id = ? AND user_id = ? AND is_done = 1
");
$stmt->execute([$subject_id, $current_user_id]);
$done_count = $stmt->fetch()['done'];

if ($done_count < 5) {
    header("Location: ../subjects/index.php?quiz_locked=1");
    exit;
}

// Check if already passed
$stmt = $pdo->prepare("
    SELECT id FROM certificates
    WHERE user_id = ? AND subject_id = ?
");
$stmt->execute([$current_user_id, $subject_id]);
if ($stmt->fetch()) {
    header("Location: ../quiz/result.php?subject_id={$subject_id}&already=1");
    exit;
}

// Get questions
$questions = getQuestions($subject['course'] ?? 'general', $subject['name']);

// Store in session so submit.php can validate
session_start();
$_SESSION['quiz_questions'] = $questions;
$_SESSION['quiz_subject_id'] = $subject_id;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz — <?= htmlspecialchars($subject['name']) ?> — StudyHub</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f4f8; min-height: 100vh; }

        nav {
            background: #1a1a2e; padding: 16px 32px;
            display: flex; justify-content: space-between; align-items: center;
        }
        nav .logo { color: #fff; font-size: 20px; font-weight: 700; }
        nav a { color: #aaa; text-decoration: none; font-size: 14px; }
        nav a:hover { color: #fff; }

        .container { max-width: 720px; margin: 40px auto; padding: 0 24px; }

        .quiz-header {
            background: #fff; border-radius: 12px;
            padding: 24px 28px; margin-bottom: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            display: flex; justify-content: space-between; align-items: center;
        }
        .quiz-header h1 { font-size: 20px; color: #1a1a2e; }
        .quiz-header p  { font-size: 13px; color: #888; margin-top: 4px; }
        .quiz-meta { text-align: right; font-size: 13px; color: #888; }
        .quiz-meta strong { color: #4A90D9; font-size: 16px; display: block; }

        .progress-bar-bg {
            background: #f0f4f8; border-radius: 30px; height: 6px;
            margin-bottom: 28px; overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%; border-radius: 30px;
            background: linear-gradient(90deg, #4A90D9, #22c55e);
            transition: width 0.4s ease;
        }

        .question-card {
            background: #fff; border-radius: 12px;
            padding: 28px; margin-bottom: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            display: none;
        }
        .question-card.active { display: block; }

        .q-num {
            font-size: 11px; font-weight: 700; letter-spacing: 2px;
            text-transform: uppercase; color: #4A90D9;
            margin-bottom: 12px;
        }
        .q-text {
            font-size: 16px; font-weight: 600; color: #1a1a2e;
            margin-bottom: 24px; line-height: 1.5;
        }

        .options { display: flex; flex-direction: column; gap: 10px; }

        .option-label {
            display: flex; align-items: center; gap: 14px;
            padding: 14px 18px; border-radius: 10px;
            border: 1px solid #e5e7eb; cursor: pointer;
            transition: all 0.2s; font-size: 14px; color: #374151;
        }
        .option-label:hover { border-color: #4A90D9; background: #EFF6FF; }

        .option-label input[type="radio"] { display: none; }

        .option-label.selected {
            border-color: #4A90D9;
            background: #EFF6FF;
            color: #1D4ED8;
            font-weight: 500;
        }

        .option-key {
            width: 28px; height: 28px; border-radius: 50%;
            background: #f0f4f8; border: 1px solid #ddd;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; flex-shrink: 0;
            color: #555; transition: all 0.2s;
        }
        .option-label.selected .option-key {
            background: #4A90D9; border-color: #4A90D9; color: #fff;
        }

        .nav-buttons {
            display: flex; justify-content: space-between;
            align-items: center; margin-top: 8px;
        }
        .btn {
            padding: 11px 24px; border-radius: 8px;
            font-size: 14px; font-weight: 600;
            cursor: pointer; border: none; font-family: inherit;
            transition: all 0.2s;
        }
        .btn-prev { background: #f0f4f8; color: #555; }
        .btn-prev:hover { background: #e0e7ef; }
        .btn-next { background: #4A90D9; color: #fff; }
        .btn-next:hover { background: #357ABD; }
        .btn-submit { background: #22c55e; color: #fff; display: none; }
        .btn-submit:hover { background: #16a34a; }

        .q-counter { font-size: 13px; color: #888; }

        .warning {
            background: #FEF3C7; color: #92400E;
            padding: 10px 16px; border-radius: 8px;
            font-size: 13px; margin-bottom: 12px;
            display: none;
        }
    </style>
</head>
<body>

<nav>
    <div class="logo">📚 StudyHub</div>
    <a href="../subjects/index.php">← Back to Subjects</a>
</nav>

<div class="container">

    <div class="quiz-header">
        <div>
            <h1>📝 <?= htmlspecialchars($subject['name']) ?> Quiz</h1>
            <p>Complete all questions. You need 70% to pass and earn your certificate.</p>
        </div>
        <div class="quiz-meta">
            <strong><?= count($questions) ?></strong>
            questions
        </div>
    </div>

    <div class="progress-bar-bg">
        <div class="progress-bar-fill" id="progressFill" style="width:10%;"></div>
    </div>

    <div class="warning" id="warningMsg">
        ⚠️ Please select an answer before continuing.
    </div>

    <form method="POST" action="../quiz/submit.php" id="quizForm">

        <?php foreach ($questions as $i => $q): ?>
        <div class="question-card <?= $i === 0 ? 'active' : '' ?>"
             id="card-<?= $i ?>">

            <div class="q-num">Question <?= $i + 1 ?> of <?= count($questions) ?></div>
            <div class="q-text"><?= htmlspecialchars($q['question']) ?></div>

            <div class="options">
                <?php foreach ($q['options'] as $key => $text): ?>
                <label class="option-label" id="opt-<?= $i ?>-<?= $key ?>"
                       onclick="selectOption(<?= $i ?>, '<?= $key ?>')">
                    <input type="radio" name="q<?= $i ?>" value="<?= $key ?>">
                    <div class="option-key"><?= strtoupper($key) ?></div>
                    <?= htmlspecialchars($text) ?>
                </label>
                <?php endforeach; ?>
            </div>

        </div>
        <?php endforeach; ?>

        <div class="nav-buttons">
            <button type="button" class="btn btn-prev" id="prevBtn"
                    onclick="navigate(-1)" style="visibility:hidden;">← Previous</button>
            <span class="q-counter" id="qCounter">1 / <?= count($questions) ?></span>
            <div>
                <button type="button" class="btn btn-next" id="nextBtn"
                        onclick="navigate(1)">Next →</button>
                <button type="submit" class="btn btn-submit" id="submitBtn">
                    Submit Quiz ✓
                </button>
            </div>
        </div>

    </form>
</div>

<script>
const total    = <?= count($questions) ?>;
let current    = 0;
let answered   = new Array(total).fill(null);

function selectOption(qIndex, key) {
    answered[qIndex] = key;
    // Clear selection visuals for this question
    document.querySelectorAll(`[id^="opt-${qIndex}-"]`)
        .forEach(el => el.classList.remove('selected'));
    // Mark selected
    document.getElementById(`opt-${qIndex}-${key}`)
        .classList.add('selected');
    // Check the radio
    const radio = document.querySelector(
        `#card-${qIndex} input[value="${key}"]`);
    if (radio) radio.checked = true;

    document.getElementById('warningMsg').style.display = 'none';
}

function navigate(dir) {
    if (dir === 1 && answered[current] === null) {
        document.getElementById('warningMsg').style.display = 'block';
        return;
    }

    document.getElementById('card-' + current).classList.remove('active');
    current += dir;
    document.getElementById('card-' + current).classList.add('active');

    // Update UI
    document.getElementById('prevBtn').style.visibility =
        current === 0 ? 'hidden' : 'visible';
    document.getElementById('nextBtn').style.display =
        current === total - 1 ? 'none' : 'inline-block';
    document.getElementById('submitBtn').style.display =
        current === total - 1 ? 'inline-block' : 'none';
    document.getElementById('qCounter').textContent =
        `${current + 1} / ${total}`;

    // Progress bar
    const pct = ((current + 1) / total) * 100;
    document.getElementById('progressFill').style.width = pct + '%';

    document.getElementById('warningMsg').style.display = 'none';
}

// Validate all answered before submit
document.getElementById('quizForm').addEventListener('submit', function(e) {
    const unanswered = answered.filter(a => a === null).length;
    if (unanswered > 0) {
        e.preventDefault();
        document.getElementById('warningMsg').textContent =
            `⚠️ Please answer all questions. ${unanswered} unanswered remaining.`;
        document.getElementById('warningMsg').style.display = 'block';
    }
});
</script>

</body>
</html>