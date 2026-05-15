<?php
require_once '../middleware/auth_check.php';
require_once '../config/db.php';

// Fetch user's subjects with task count and done count + certificate status
$stmt = $pdo->prepare("
    SELECT s.*,
           COUNT(t.id) as task_count,
           SUM(CASE WHEN t.is_done = 1 THEN 1 ELSE 0 END) as done_count,
           (SELECT id FROM certificates
            WHERE user_id = s.user_id AND subject_id = s.id
            LIMIT 1) as has_cert
    FROM subjects s
    LEFT JOIN tasks t ON t.subject_id = s.id
    WHERE s.user_id = ?
    GROUP BY s.id
    ORDER BY s.created_at DESC
");
$stmt->execute([$current_user_id]);
$subjects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subjects - StudyHub</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
        }

        nav {
            background: #1a1a2e;
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        nav .logo { color: #fff; font-size: 20px; font-weight: 700; }

        nav .nav-right { display: flex; align-items: center; gap: 20px; }

        nav .nav-right a {
            color: #aaa;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s;
        }

        nav .nav-right a:hover { color: #fff; }
        nav .nav-right a.active { color: #4A90D9; font-weight: 500; }

        nav .role-badge {
            background: #4A90D9;
            color: #fff;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }

        nav .role-badge.intermediate { background: #8e44ad; }

        nav a.logout { color: #ff6b6b !important; }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 24px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }

        .page-header h1 { font-size: 24px; color: #1a1a2e; }
        .page-header p  { font-size: 13px; color: #888; margin-top: 4px; }

        .btn {
            padding: 10px 20px;
            background: #4A90D9;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn:hover { background: #357ABD; }

        /* Subject Cards */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .subject-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            overflow: hidden;
        }

        .card-top {
            height: 6px;
        }

        .card-body { padding: 16px 20px 20px; }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }

        .card-header h3 {
            font-size: 15px;
            color: #1a1a2e;
            font-weight: 600;
        }

        .card-actions { display: flex; gap: 8px; }

        .card-actions button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 13px;
            padding: 2px 6px;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .btn-edit  { color: #4A90D9; }
        .btn-edit:hover  { background: #e6f1fb; }
        .btn-delete { color: #e74c3c; }
        .btn-delete:hover { background: #ffe0e0; }

        .card-desc {
            font-size: 13px;
            color: #888;
            margin-bottom: 14px;
            min-height: 20px;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .difficulty-badge {
            font-size: 11px;
            padding: 3px 10px;
            border-radius: 20px;
            font-weight: 500;
        }

        .diff-basic        { background: #e6f1fb; color: #185FA5; }
        .diff-intermediate { background: #eaf3de; color: #3B6D11; }
        .diff-advanced     { background: #eeedfe; color: #3C3489; }

        .task-count { font-size: 12px; color: #aaa; }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #aaa;
        }

        .empty-state p { font-size: 15px; margin-bottom: 8px; }
        .empty-state span { font-size: 13px; }

        /* Add / Edit Form */
        .form-section {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 28px;
            margin-bottom: 40px;
        }

        .form-section h2 {
            font-size: 17px;
            color: #1a1a2e;
            margin-bottom: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .form-group { margin-bottom: 16px; }

        label {
            display: block;
            font-size: 13px;
            color: #555;
            margin-bottom: 6px;
            font-weight: 500;
        }

        input[type="text"], select, textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            font-family: inherit;
            transition: border 0.2s;
        }

        input[type="text"]:focus,
        select:focus,
        textarea:focus { border-color: #4A90D9; }

        .color-row {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        input[type="color"] {
            width: 40px;
            height: 36px;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            padding: 2px;
        }

        .form-actions { display: flex; gap: 12px; margin-top: 4px; }

        .btn-cancel {
            padding: 10px 20px;
            background: #f0f4f8;
            color: #555;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
        }

        .btn-cancel:hover { background: #e0e7ef; }

        /* Alert */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .alert.success { background: #e0f7e9; color: #27ae60; }
        .alert.error   { background: #ffe0e0; color: #c0392b; }
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
        <a href="../subjects/index.php" class="active">Subjects</a>
        <a href="../tasks/index.php">Tasks</a>
        <a href="../progress/index.php">Progress</a>
        <span class="role-badge <?= $current_role ?>"><?= ucfirst($current_role) ?></span>
        <a href="../auth/logout.php" class="logout">Logout</a>
    </div>
</nav>

<div class="container">

    <div id="alert-box"></div>

    <div class="page-header">
        <div>
            <h1>My Subjects</h1>
            <p><?= count($subjects) ?> subject<?= count($subjects) !== 1 ? 's' : '' ?> added</p>
        </div>
        <button class="btn" onclick="showAddForm()">+ Add Subject</button>
    </div>

    <!-- Add / Edit Form (hidden by default) -->
    <div class="form-section" id="formSection" style="display:none;">
        <h2 id="formTitle">Add New Subject</h2>
        <input type="hidden" id="editId" value="">

        <div class="form-row">
            <div class="form-group">
                <label>Subject Name *</label>
                <input type="text" id="subjectName" placeholder="e.g. Mathematics">
            </div>
            <div class="form-group">
                <label>Difficulty</label>
                <select id="subjectDifficulty">
                    <option value="basic">Basic</option>
                    <option value="intermediate">Intermediate</option>
                    <option value="advanced">Advanced</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <input type="text" id="subjectDesc" placeholder="Short description (optional)">
        </div>

        <div class="form-group">
            <label>Card Color</label>
            <div class="color-row">
                <input type="color" id="subjectColor" value="#4A90D9">
                <span style="font-size:13px;color:#888;">Pick a color for this subject card</span>
            </div>
        </div>

        <div class="form-actions">
            <button class="btn" onclick="saveSubject()">Save Subject</button>
            <button class="btn-cancel" onclick="hideForm()">Cancel</button>
        </div>
    </div>

    <!-- Subject Cards -->
    <div class="cards-grid" id="cardsGrid">
        <?php if (empty($subjects)): ?>
            <div class="empty-state" style="grid-column: 1/-1;">
                <p>No subjects yet</p>
                <span>Click "Add Subject" to get started</span>
            </div>
        <?php else: ?>
            <?php foreach ($subjects as $s): ?>
            <div class="subject-card" id="card-<?= $s['id'] ?>">
                <div class="card-top" style="background: <?= htmlspecialchars($s['color_tag']) ?>;"></div>
                <div class="card-body">
                    <div class="card-header">
                        <h3><?= htmlspecialchars($s['name']) ?></h3>
                        <div class="card-actions">
                            <button class="btn-edit" onclick="editSubject(<?= $s['id'] ?>, '<?= addslashes($s['name']) ?>', '<?= addslashes($s['description']) ?>', '<?= $s['difficulty'] ?>', '<?= $s['color_tag'] ?>')">Edit</button>
                            <button class="btn-delete" onclick="deleteSubject(<?= $s['id'] ?>)">Delete</button>
                        </div>
                    </div>
                    <p class="card-desc"><?= htmlspecialchars($s['description'] ?? '') ?></p>

                    <!-- Task progress bar -->
                    <?php
                    $done = (int)$s['done_count'];
                    $total = (int)$s['task_count'];
                    $pct = $total > 0 ? round(($done / $total) * 100) : 0;
                    $quiz_eligible = $done >= 5;
                    $has_cert = !empty($s['has_cert']);
                    ?>
                    <?php if ($total > 0): ?>
                    <div style="margin-bottom:12px;">
                        <div style="display:flex;justify-content:space-between;font-size:11px;color:#aaa;margin-bottom:4px;">
                            <span><?= $done ?>/<?= $total ?> tasks done</span>
                            <span><?= $pct ?>%</span>
                        </div>
                        <div style="background:#f0f4f8;border-radius:30px;height:5px;overflow:hidden;">
                            <div style="width:<?= $pct ?>%;height:100%;border-radius:30px;background:<?= $pct >= 100 ? '#22c55e' : '#4A90D9' ?>;"></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="card-footer">
                        <span class="difficulty-badge diff-<?= $s['difficulty'] ?>"><?= ucfirst($s['difficulty']) ?></span>
                        <div style="display:flex;gap:6px;align-items:center;">
                            <?php if ($has_cert): ?>
                                <a href="../certificate/generate.php?subject_id=<?= $s['id'] ?>"
                                   style="font-size:11px;background:#e0f7e9;color:#16a34a;padding:3px 10px;border-radius:20px;text-decoration:none;font-weight:600;">
                                    🏆 Certificate
                                </a>
                            <?php elseif ($quiz_eligible): ?>
                                <a href="../quiz/index.php?subject_id=<?= $s['id'] ?>"
                                   style="font-size:11px;background:#faeeda;color:#854F0B;padding:3px 10px;border-radius:20px;text-decoration:none;font-weight:600;">
                                    📝 Take Quiz
                                </a>
                            <?php else: ?>
                                <span style="font-size:11px;color:#bbb;"
                                      title="Complete 5 tasks to unlock quiz">
                                    🔒 <?= max(0, 5 - $done) ?> more to quiz
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<script>
const USER_ROLE = '<?= $current_role ?>';

function showAlert(msg, type) {
    const box = document.getElementById('alert-box');
    box.innerHTML = `<div class="alert ${type}">${msg}</div>`;
    setTimeout(() => box.innerHTML = '', 3000);
}

function showAddForm() {
    document.getElementById('formTitle').textContent = 'Add New Subject';
    document.getElementById('editId').value = '';
    document.getElementById('subjectName').value = '';
    document.getElementById('subjectDesc').value = '';
    document.getElementById('subjectDifficulty').value = 'basic';
    document.getElementById('subjectColor').value = '#4A90D9';
    document.getElementById('formSection').style.display = 'block';
    document.getElementById('subjectName').focus();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function hideForm() {
    document.getElementById('formSection').style.display = 'none';
}

function editSubject(id, name, desc, difficulty, color) {
    document.getElementById('formTitle').textContent = 'Edit Subject';
    document.getElementById('editId').value = id;
    document.getElementById('subjectName').value = name;
    document.getElementById('subjectDesc').value = desc;
    document.getElementById('subjectDifficulty').value = difficulty;
    document.getElementById('subjectColor').value = color;
    document.getElementById('formSection').style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function saveSubject() {
    const id         = document.getElementById('editId').value;
    const name       = document.getElementById('subjectName').value.trim();
    const desc       = document.getElementById('subjectDesc').value.trim();
    const difficulty = document.getElementById('subjectDifficulty').value;
    const color      = document.getElementById('subjectColor').value;

    if (!name) { showAlert('Subject name is required.', 'error'); return; }

    const url  = id ? 'update_subject.php' : 'add_subject.php';
    const body = new URLSearchParams({ id, name, description: desc, difficulty, color_tag: color });

    fetch(url, { method: 'POST', body })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showAlert(id ? 'Subject updated!' : 'Subject added!', 'success');
                hideForm();
                setTimeout(() => location.reload(), 800);
            } else {
                showAlert(data.error || 'Something went wrong.', 'error');
            }
        });
}

function deleteSubject(id) {
    if (!confirm('Delete this subject? All linked tasks will also be deleted.')) return;

    fetch('delete_subject.php', {
        method: 'POST',
        body: new URLSearchParams({ id })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('card-' + id).remove();
            showAlert('Subject deleted.', 'success');
        } else {
            showAlert(data.error || 'Delete failed.', 'error');
        }
    });
}
</script>
<?php include '../includes/study_session_btn.php'; ?>
</body>
</html>