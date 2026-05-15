<?php
require_once '../middleware/auth_check.php';
require_once '../config/db.php';

// Fetch user's subjects for dropdown
$stmt = $pdo->prepare("SELECT id, name, color_tag FROM subjects WHERE user_id = ? ORDER BY name");
$stmt->execute([$current_user_id]);
$subjects = $stmt->fetchAll();

// Fetch tasks with subject name
$stmt = $pdo->prepare("
    SELECT t.*, s.name as subject_name, s.color_tag
    FROM tasks t
    JOIN subjects s ON t.subject_id = s.id
    WHERE t.user_id = ?
    ORDER BY t.is_done ASC, t.due_date ASC, t.created_at DESC
");
$stmt->execute([$current_user_id]);
$tasks = $stmt->fetchAll();

// Fetch steps for all tasks
$task_ids = array_column($tasks, 'id');
$steps_map = [];
if (!empty($task_ids)) {
    $placeholders = implode(',', array_fill(0, count($task_ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM steps WHERE task_id IN ($placeholders) ORDER BY step_order");
    $stmt->execute($task_ids);
    foreach ($stmt->fetchAll() as $step) {
        $steps_map[$step['task_id']][] = $step;
    }
}

$total = count($tasks);
$done  = count(array_filter($tasks, fn($t) => $t['is_done']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks - StudyHub</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f4f8; font-size: 14px; }

        nav {
            background: #1a1a2e; padding: 16px 32px;
            display: flex; justify-content: space-between; align-items: center;
        }
        nav .logo { color: #fff; font-size: 20px; font-weight: 700; }
        nav .nav-right { display: flex; align-items: center; gap: 20px; }
        nav .nav-right a { color: #aaa; text-decoration: none; font-size: 14px; transition: color 0.2s; }
        nav .nav-right a:hover { color: #fff; }
        nav .nav-right a.active { color: #4A90D9; font-weight: 500; }
        nav .role-badge { background: #4A90D9; color: #fff; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: capitalize; }
        nav .role-badge.intermediate { background: #8e44ad; }
        nav a.logout { color: #ff6b6b !important; }

        .container { max-width: 900px; margin: 32px auto; padding: 0 24px; }

        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .page-header h1 { font-size: 22px; color: #1a1a2e; }
        .page-header p  { font-size: 13px; color: #888; margin-top: 4px; }

        .btn { background: #4A90D9; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; cursor: pointer; transition: background 0.2s; font-family: inherit; }
        .btn:hover { background: #357ABD; }

        /* Tabs row */
        .tabs-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .tabs { display: flex; gap: 4px; background: #fff; padding: 5px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .tab { padding: 7px 22px; border-radius: 7px; font-size: 13px; cursor: pointer; color: #888; border: none; background: none; font-family: inherit; transition: all 0.2s; }
        .tab.active { background: #4A90D9; color: #fff; font-weight: 500; }

        .filter-select { padding: 9px 14px; border: 1px solid #ddd; border-radius: 8px; font-size: 13px; outline: none; background: #fff; color: #555; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.06); font-family: inherit; }
        .filter-select:focus { border-color: #4A90D9; }

        /* Alert */
        .alert { padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
        .alert.success { background: #e0f7e9; color: #27ae60; }
        .alert.error   { background: #ffe0e0; color: #c0392b; }

        /* Tab content */
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .task-list { display: flex; flex-direction: column; gap: 8px; }

        /* Task card */
        .task-card { background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden; border-left: 3px solid transparent; }
        .task-card.high   { border-left-color: #e74c3c; }
        .task-card.medium { border-left-color: #f39c12; }
        .task-card.low    { border-left-color: #27ae60; }
        .task-card.done-card { opacity: 0.6; }

        .task-main { display: flex; align-items: center; padding: 13px 16px; gap: 12px; }

        .check-circle { width: 22px; height: 22px; border-radius: 50%; border: 2px solid #ddd; flex-shrink: 0; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; font-size: 12px; }
        .check-circle.done { background: #27ae60; border-color: #27ae60; color: #fff; }
        .check-circle:hover { border-color: #4A90D9; }

        .task-info { flex: 1; min-width: 0; }
        .task-title { font-size: 14px; color: #1a1a2e; font-weight: 500; }
        .task-title.done { text-decoration: line-through; color: #aaa; }
        .task-meta { font-size: 11px; color: #aaa; margin-top: 3px; display: flex; gap: 12px; flex-wrap: wrap; }

        .priority-badge { font-size: 11px; padding: 3px 10px; border-radius: 20px; font-weight: 500; flex-shrink: 0; }
        .p-high   { background: #fcebeb; color: #A32D2D; }
        .p-medium { background: #faeeda; color: #854F0B; }
        .p-low    { background: #eaf3de; color: #3B6D11; }

        .task-actions { display: flex; gap: 6px; align-items: center; flex-shrink: 0; }
        .btn-icon { background: #f0f4f8; border: none; border-radius: 6px; padding: 5px 9px; font-size: 12px; cursor: pointer; color: #555; font-family: inherit; transition: background 0.2s; }
        .btn-icon:hover { background: #e0e7ef; }
        .btn-timer { background: #e6f1fb; color: #185FA5; }
        .btn-steps { background: #eeedfe; color: #3C3489; }
        .btn-edit  { color: #4A90D9; }
        .btn-del   { color: #e74c3c; }

        /* Steps area */
        .steps-area { padding: 0 16px 12px 52px; display: none; }
        .steps-area.open { display: block; }
        .step-item { display: flex; align-items: center; gap: 8px; padding: 6px 0; font-size: 12px; color: #555; border-bottom: 1px solid #f5f5f5; }
        .step-item:last-child { border-bottom: none; }
        .step-item input[type="checkbox"] { accent-color: #4A90D9; cursor: pointer; }
        .step-item.done-step { text-decoration: line-through; color: #aaa; }

        .empty { text-align: center; padding: 60px 20px; color: #bbb; }
        .empty p { font-size: 15px; margin-bottom: 6px; }
        .empty span { font-size: 13px; }

        /* Modal */
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.45); display: none; align-items: center; justify-content: center; z-index: 100; padding: 20px; }
        .modal-overlay.open { display: flex; }
        .modal { background: #fff; border-radius: 14px; width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }

        .modal-header { padding: 20px 24px 0; display: flex; justify-content: space-between; align-items: center; }
        .modal-header h2 { font-size: 17px; color: #1a1a2e; }
        .modal-close { background: none; border: none; font-size: 24px; cursor: pointer; color: #aaa; line-height: 1; }
        .modal-close:hover { color: #555; }

        .modal-tabs { display: flex; padding: 14px 24px 0; border-bottom: 1px solid #f0f4f8; }
        .modal-tab { padding: 8px 18px; font-size: 13px; cursor: pointer; color: #888; border: none; background: none; border-bottom: 2px solid transparent; font-family: inherit; margin-bottom: -1px; transition: all 0.2s; }
        .modal-tab.active { color: #4A90D9; border-bottom-color: #4A90D9; font-weight: 500; }

        .modal-body { padding: 20px 24px 16px; }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 12px; color: #555; margin-bottom: 5px; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 9px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 13px; outline: none; font-family: inherit; transition: border 0.2s; }
        .form-group input:focus, .form-group select:focus { border-color: #4A90D9; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        .steps-input { margin-bottom: 14px; }
        .steps-input > label { font-size: 12px; color: #555; font-weight: 500; display: block; margin-bottom: 8px; }
        .step-input-row { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
        .step-input-row input { flex: 1; padding: 7px 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; outline: none; font-family: inherit; }
        .step-input-row input:focus { border-color: #4A90D9; }
        .btn-remove-step { background: none; border: none; color: #e74c3c; cursor: pointer; font-size: 20px; line-height: 1; padding: 0 4px; }
        .btn-add-step { background: none; border: 1px dashed #4A90D9; color: #4A90D9; padding: 5px 14px; border-radius: 6px; font-size: 12px; cursor: pointer; font-family: inherit; margin-top: 4px; }

        .timer-input { display: flex; align-items: center; gap: 8px; }
        .timer-btn { width: 32px; height: 32px; border: 1px solid #ddd; border-radius: 6px; background: #f0f4f8; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center; border: none; }
        .timer-val { width: 60px; text-align: center; padding: 6px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; }
        .timer-hint { font-size: 11px; color: #aaa; margin-top: 4px; }

        .modal-footer { display: flex; gap: 10px; padding: 4px 24px 24px; }
        .btn-save { flex: 1; padding: 11px; background: #4A90D9; color: #fff; border: none; border-radius: 8px; font-size: 14px; cursor: pointer; font-family: inherit; transition: background 0.2s; }
        .btn-save:hover { background: #357ABD; }
        .btn-cancel-modal { padding: 11px 20px; background: #f0f4f8; color: #555; border: none; border-radius: 8px; font-size: 14px; cursor: pointer; font-family: inherit; }
        .btn-cancel-modal:hover { background: #e0e7ef; }
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
        <a href="../tasks/index.php" class="active">Tasks</a>
        <a href="../progress/index.php">Progress</a>
        <span class="role-badge <?= $current_role ?>"><?= ucfirst($current_role) ?></span>
        <a href="../auth/logout.php" class="logout">Logout</a>
    </div>
</nav>

<div class="container">

    <div id="alert-box"></div>

    <div class="page-header">
        <div>
            <h1>My Tasks</h1>
            <p><?= $total ?> task<?= $total != 1 ? 's' : '' ?> · <?= $done ?> completed</p>
        </div>
        <button class="btn" onclick="openModal('task')">+ Add Task</button>
    </div>

    <!-- Tabs row -->
    <div class="tabs-row">
        <div class="tabs">
            <button class="tab active" onclick="switchTab('tasks', this)">📋 Tasks</button>
            <button class="tab" onclick="switchTab('exams', this)">📝 Exams</button>
        </div>
        <select class="filter-select" id="subjectFilter" onchange="filterBySubject(this.value)">
            <option value="0">All Subjects</option>
            <?php foreach ($subjects as $s): ?>
                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Tasks tab -->
    <div class="tab-content active" id="tab-tasks">
        <?php if (empty($tasks)): ?>
            <div class="empty">
                <p>No tasks yet</p>
                <span>Click "+ Add Task" to get started</span>
            </div>
        <?php else: ?>
            <div class="task-list" id="taskList">
                <?php foreach ($tasks as $t): ?>
                <div class="task-card <?= $t['priority'] ?> <?= $t['is_done'] ? 'done-card' : '' ?>"
                     id="task-<?= $t['id'] ?>"
                     data-subject="<?= $t['subject_id'] ?>">
                    <div class="task-main">
                        <div class="check-circle <?= $t['is_done'] ? 'done' : '' ?>"
                             onclick="toggleDone(<?= $t['id'] ?>, <?= $t['is_done'] ? 0 : 1 ?>)">
                            <?= $t['is_done'] ? '✓' : '' ?>
                        </div>
                        <div class="task-info">
                            <div class="task-title <?= $t['is_done'] ? 'done' : '' ?>">
                                <?= htmlspecialchars($t['title']) ?>
                            </div>
                            <div class="task-meta">
                                <?php if ($t['due_date']): ?>
                                    <span>📅 <?= date('M j', strtotime($t['due_date'])) ?></span>
                                <?php endif; ?>
                                <span>📖 <?= htmlspecialchars($t['subject_name']) ?></span>
                                <span>⏱ <?= $t['estimated_minutes'] ?> min</span>
                            </div>
                        </div>
                        <span class="priority-badge p-<?= $t['priority'] ?>"><?= $t['priority'] ?></span>
                        <div class="task-actions">
                            <?php if (!empty($steps_map[$t['id']])): ?>
                            <button class="btn-icon btn-steps" onclick="toggleSteps(this)">📋 Steps</button>
                            <?php endif; ?>
                            <button class="btn-icon btn-timer"
                                onclick="window.location.href='focustimer.html?task_id=<?= $t['id'] ?>&role=<?= $current_role ?>'">
                                ⏱ Timer
                            </button>
                            <button class="btn-icon btn-edit"
                                onclick="openEditModal(<?= $t['id'] ?>, '<?= addslashes($t['title']) ?>', '<?= addslashes($t['description'] ?? '') ?>', <?= $t['subject_id'] ?>, '<?= $t['priority'] ?>', '<?= $t['due_date'] ?? '' ?>', <?= $t['estimated_minutes'] ?>)">
                                ✏️
                            </button>
                            <button class="btn-icon btn-del" onclick="deleteTask(<?= $t['id'] ?>)">🗑</button>
                        </div>
                    </div>
                    <?php if (!empty($steps_map[$t['id']])): ?>
                    <div class="steps-area">
                        <?php foreach ($steps_map[$t['id']] as $step): ?>
                        <div class="step-item <?= $step['is_done'] ? 'done-step' : '' ?>"
                             id="step-<?= $step['id'] ?>">
                            <input type="checkbox"
                                   <?= $step['is_done'] ? 'checked' : '' ?>
                                   onchange="toggleStep(<?= $step['id'] ?>, this.checked)">
                            <?= htmlspecialchars($step['step_text']) ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Exams tab -->
    <div class="tab-content" id="tab-exams">
        <div class="empty">
            <p>📝 No exams added yet</p>
            <span>Click "+ Add Task" and switch to Exams tab</span>
        </div>
    </div>

</div>

<!-- Modal -->
<div class="modal-overlay" id="modalOverlay" onclick="closeOnOverlay(event)">
  <div class="modal">
    <div class="modal-header">
        <h2 id="modalTitle">Add New Task</h2>
        <button class="modal-close" onclick="closeModal()">×</button>
    </div>

    <div class="modal-tabs">
        <button class="modal-tab active" id="mtab-task" onclick="switchModalTab('task', this)">📋 Tasks</button>
        <button class="modal-tab" id="mtab-exam" onclick="switchModalTab('exam', this)">📝 Exams</button>
    </div>

    <!-- Task form -->
    <div id="form-task">
        <div class="modal-body">
            <input type="hidden" id="editTaskId" value="">

            <div class="form-group">
                <label>Task Title *</label>
                <input type="text" id="taskTitle" placeholder="e.g. Solve 10 practice problems">
            </div>

            <div class="steps-input" id="stepsSection">
                <label>Steps / Checklist</label>
                <div id="stepsContainer">
                    <div class="step-input-row">
                        <input type="text" placeholder="Step 1">
                        <button class="btn-remove-step" onclick="removeStep(this)">×</button>
                    </div>
                </div>
                <button class="btn-add-step" onclick="addStep()">+ Add Step</button>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Subject *</label>
                    <select id="taskSubject">
                        <option value="">Select subject</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Priority</label>
                    <select id="taskPriority">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Due Date</label>
                    <input type="date" id="taskDueDate">
                </div>
                <div class="form-group">
                    <label>Estimated Time (min)</label>
                    <div class="timer-input">
                        <button class="timer-btn" onclick="changeTime(-5)">−</button>
                        <input class="timer-val" type="number" id="timerVal" value="25" min="5">
                        <button class="timer-btn" onclick="changeTime(5)">+</button>
                    </div>
                    <div class="timer-hint">Links to focus timer</div>
                </div>
            </div>

            <div class="form-group">
                <label>Description (optional)</label>
                
                <textarea name="" id="taskDesc" cols="90" rows="3" placeholder="Any notes about this task"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel-modal" onclick="closeModal()">Cancel</button>
            <button class="btn-save" onclick="saveTask()">Save Task</button>
        </div>
    </div>

    <!-- Exam form -->
    <div id="form-exam" style="display:none;">
        <div class="modal-body">
            <div class="form-group">
                <label>Exam Name *</label>
                <input type="text" id="examName" placeholder="e.g. Mathematics Mid-Term">
            </div>
            <div class="form-group">
                <label>Subject</label>
                <select id="examSubject">
                    <option value="">Select subject</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="steps-input">
                <label>Chapters / Topics</label>
                <div id="chaptersContainer">
                    <div class="step-input-row">
                        <input type="text" placeholder="Chapter / Topic">
                        <button class="btn-remove-step" onclick="this.parentElement.remove()">×</button>
                    </div>
                </div>
                <button class="btn-add-step" onclick="addChapter()">+ Add Chapter</button>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Exam Date</label>
                    <input type="date" id="examDate">
                </div>
                <div class="form-group">
                    <label>Study Time (min/day)</label>
                    <div class="timer-input">
                        <button class="timer-btn" onclick="changeExamTime(-5)">−</button>
                        <input class="timer-val" type="number" id="examTimerVal" value="60" min="5">
                        <button class="timer-btn" onclick="changeExamTime(5)">+</button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Total Exam Duration (hours)</label>
                <input type="number" id="examDuration" placeholder="e.g. 3">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel-modal" onclick="closeModal()">Cancel</button>
            <button class="btn-save" onclick="saveExam()">Save Exam</button>
        </div>
    </div>
  </div>
</div>

<script>
const USER_ROLE = '<?= $current_role ?>';

function showAlert(msg, type) {
    const box = document.getElementById('alert-box');
    box.innerHTML = `<div class="alert ${type}">${msg}</div>`;
    setTimeout(() => box.innerHTML = '', 3000);
}

// Tab switching
function switchTab(name, btn) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
}

// Subject filter
function filterBySubject(subjectId) {
    document.querySelectorAll('.task-card').forEach(card => {
        if (subjectId === '0' || card.dataset.subject === subjectId) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Steps toggle
function toggleSteps(btn) {
    const area = btn.closest('.task-card').querySelector('.steps-area');
    area.classList.toggle('open');
}

// Toggle task done
function toggleDone(id, isDone) {
    fetch('update_task.php', {
        method: 'POST',
        body: new URLSearchParams({ id, is_done: isDone })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const card   = document.getElementById('task-' + id);
            const circle = card.querySelector('.check-circle');
            const title  = card.querySelector('.task-title');
            if (isDone) {
                circle.classList.add('done');
                circle.textContent = '✓';
                title.classList.add('done');
                card.classList.add('done-card');
            } else {
                circle.classList.remove('done');
                circle.textContent = '';
                title.classList.remove('done');
                card.classList.remove('done-card');
            }
            circle.setAttribute('onclick', `toggleDone(${id}, ${isDone ? 0 : 1})`);
        }
    });
}

// Toggle step done
function toggleStep(stepId, isDone) {
    fetch('update_step.php', {
        method: 'POST',
        body: new URLSearchParams({ id: stepId, is_done: isDone ? 1 : 0 })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const stepEl = document.getElementById('step-' + stepId);
            if (isDone) stepEl.classList.add('done-step');
            else stepEl.classList.remove('done-step');
        }
    });
}

// Modal
function openModal(type = 'task') {
    document.getElementById('editTaskId').value = '';
    document.getElementById('modalTitle').textContent = 'Add New Task';
    resetTaskForm();
    switchModalTab(type, document.getElementById('mtab-' + type));
    document.getElementById('modalOverlay').classList.add('open');
}

function openEditModal(id, title, desc, subjectId, priority, dueDate, minutes) {
    document.getElementById('editTaskId').value = id;
    document.getElementById('modalTitle').textContent = 'Edit Task';
    document.getElementById('taskTitle').value = title;
    document.getElementById('taskDesc').value = desc;
    document.getElementById('taskSubject').value = subjectId;
    document.getElementById('taskPriority').value = priority;
    document.getElementById('taskDueDate').value = dueDate;
    document.getElementById('timerVal').value = minutes;
    switchModalTab('task', document.getElementById('mtab-task'));
    document.getElementById('modalOverlay').classList.add('open');
}

function closeModal() {
    document.getElementById('modalOverlay').classList.remove('open');
}

function closeOnOverlay(e) {
    if (e.target === document.getElementById('modalOverlay')) closeModal();
}

function resetTaskForm() {
    document.getElementById('taskTitle').value = '';
    document.getElementById('taskDesc').value = '';
    document.getElementById('taskSubject').value = '';
    document.getElementById('taskPriority').value = 'medium';
    document.getElementById('taskDueDate').value = '';
    document.getElementById('timerVal').value = 25;
    document.getElementById('stepsContainer').innerHTML = `
        <div class="step-input-row">
            <input type="text" placeholder="Step 1">
            <button class="btn-remove-step" onclick="removeStep(this)">×</button>
        </div>`;
}

function switchModalTab(name, btn) {
    document.querySelectorAll('.modal-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('form-task').style.display = name === 'task' ? 'block' : 'none';
    document.getElementById('form-exam').style.display = name === 'exam' ? 'block' : 'none';
}

// Steps
function addStep() {
    const c = document.getElementById('stepsContainer');
    const n = c.children.length + 1;
    const row = document.createElement('div');
    row.className = 'step-input-row';
    row.innerHTML = `<input type="text" placeholder="Step ${n}"><button class="btn-remove-step" onclick="removeStep(this)">×</button>`;
    c.appendChild(row);
}

function removeStep(btn) {
    const container = document.getElementById('stepsContainer');
    if (container.children.length > 1) btn.parentElement.remove();
}

function addChapter() {
    const c = document.getElementById('chaptersContainer');
    const n = c.children.length + 1;
    const row = document.createElement('div');
    row.className = 'step-input-row';
    row.innerHTML = `<input type="text" placeholder="Chapter / Topic ${n}"><button class="btn-remove-step" onclick="this.parentElement.remove()">×</button>`;
    c.appendChild(row);
}

// Timer
function changeTime(d) { const i = document.getElementById('timerVal'); const v = parseInt(i.value)+d; if(v>=5) i.value=v; }
function changeExamTime(d) { const i = document.getElementById('examTimerVal'); const v = parseInt(i.value)+d; if(v>=5) i.value=v; }

// Save task
function saveTask() {
    const id       = document.getElementById('editTaskId').value;
    const title    = document.getElementById('taskTitle').value.trim();
    const subject  = document.getElementById('taskSubject').value;
    const priority = document.getElementById('taskPriority').value;
    const dueDate  = document.getElementById('taskDueDate').value;
    const minutes  = document.getElementById('timerVal').value;
    const desc     = document.getElementById('taskDesc').value.trim();

    if (!title) { showAlert('Task title is required.', 'error'); return; }
    if (!subject) { showAlert('Please select a subject.', 'error'); return; }

    // Collect steps
    const stepInputs = document.querySelectorAll('#stepsContainer .step-input-row input');
    const steps = Array.from(stepInputs).map(i => i.value.trim()).filter(v => v !== '');

    const url  = id ? 'update_task.php' : 'add_task.php';
    const body = new URLSearchParams({ id, title, subject_id: subject, priority, due_date: dueDate, estimated_minutes: minutes, description: desc });
    steps.forEach((s, i) => body.append(`steps[${i}]`, s));

    fetch(url, { method: 'POST', body })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showAlert(id ? 'Task updated!' : 'Task added!', 'success');
                closeModal();
                setTimeout(() => location.reload(), 800);
            } else {
                showAlert(data.error || 'Something went wrong.', 'error');
            }
        });
}

// Save exam (stored as a task with high priority, description = exam info)
function saveExam() {
    const name     = document.getElementById('examName').value.trim();
    const subject  = document.getElementById('examSubject').value;
    const date     = document.getElementById('examDate').value;
    const duration = document.getElementById('examDuration').value;
    const minutes  = document.getElementById('examTimerVal').value;

    if (!name) { showAlert('Exam name is required.', 'error'); return; }
    if (!subject) { showAlert('Please select a subject.', 'error'); return; }

    const chapters = Array.from(document.querySelectorAll('#chaptersContainer .step-input-row input'))
                         .map(i => i.value.trim()).filter(v => v !== '');

    const body = new URLSearchParams({
        title: name,
        subject_id: subject,
        priority: 'high',
        due_date: date,
        estimated_minutes: minutes,
        description: `Exam | Duration: ${duration}h`,
        is_exam: 1
    });
    chapters.forEach((c, i) => body.append(`steps[${i}]`, c));

    fetch('add_task.php', { method: 'POST', body })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showAlert('Exam added!', 'success');
                closeModal();
                setTimeout(() => location.reload(), 800);
            } else {
                showAlert(data.error || 'Something went wrong.', 'error');
            }
        });
}

// Delete task
function deleteTask(id) {
    if (!confirm('Delete this task and all its steps?')) return;
    fetch('delete_task.php', { method: 'POST', body: new URLSearchParams({ id }) })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('task-' + id).remove();
                showAlert('Task deleted.', 'success');
            } else {
                showAlert(data.error || 'Delete failed.', 'error');
            }
        });
}
</script>
<?php include '../includes/study_session_btn.php'; ?>
</body>
</html>