<!-- ── FLOATING STUDY SESSION BUTTON ── -->
<style>
.fab-session {
    position: fixed;
    bottom: 32px; right: 32px;
    z-index: 200;
    display: flex; align-items: center; gap: 10px;
    padding: 14px 22px;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: #fff; border: none; border-radius: 50px;
    font-family: 'Segoe UI', sans-serif;
    font-size: 14px; font-weight: 600;
    cursor: pointer;
    box-shadow: 0 4px 24px rgba(34,197,94,0.35);
    transition: all 0.25s;
    animation: fabPulse 3s infinite;
}
.fab-session:hover {
    transform: translateY(-3px) scale(1.03);
    box-shadow: 0 8px 32px rgba(34,197,94,0.45);
    animation: none;
}
.fab-session .fab-icon { font-size: 18px; }

@keyframes fabPulse {
    0%, 100% { box-shadow: 0 4px 24px rgba(34,197,94,0.35); }
    50%       { box-shadow: 0 4px 36px rgba(34,197,94,0.55); }
}

/* Modal overlay */
.session-overlay {
    position: fixed; inset: 0; z-index: 300;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(6px);
    display: none; align-items: center; justify-content: center;
    padding: 20px;
}
.session-overlay.open { display: flex; }

.session-modal {
    background: #fff;
    border-radius: 16px;
    width: 100%; max-width: 460px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.25);
    overflow: hidden;
    animation: modalSlide 0.3s ease;
}

@keyframes modalSlide {
    from { opacity: 0; transform: translateY(20px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

.session-header {
    padding: 20px 24px 16px;
    border-bottom: 1px solid #f0f4f8;
    display: flex; justify-content: space-between; align-items: flex-start;
}
.session-header h2 {
    font-size: 17px; color: #1a1a2e; font-weight: 700;
    font-family: 'Segoe UI', sans-serif;
}
.session-header p {
    font-size: 12px; color: #888; margin-top: 3px;
}
.session-close {
    background: none; border: none; font-size: 22px;
    color: #aaa; cursor: pointer; line-height: 1;
    padding: 0 0 0 12px;
}
.session-close:hover { color: #555; }

.session-body { padding: 20px 24px; }

/* Task preview card */
.task-preview {
    background: #f8fafc;
    border-radius: 12px;
    padding: 18px 20px;
    margin-bottom: 16px;
    border-left: 4px solid #22c55e;
}
.task-preview .tp-subject {
    font-size: 11px; font-weight: 600; letter-spacing: 1px;
    text-transform: uppercase; color: #888; margin-bottom: 8px;
    display: flex; align-items: center; gap: 6px;
}
.tp-dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
}
.task-preview .tp-title {
    font-size: 16px; font-weight: 700; color: #1a1a2e; margin-bottom: 6px;
    font-family: 'Segoe UI', sans-serif;
}
.task-preview .tp-meta {
    display: flex; gap: 14px; font-size: 12px; color: #888;
    flex-wrap: wrap;
}
.task-preview .tp-priority {
    padding: 2px 9px; border-radius: 20px; font-size: 11px; font-weight: 600;
}
.p-high   { background: #fcebeb; color: #A32D2D; }
.p-medium { background: #faeeda; color: #854F0B; }
.p-low    { background: #eaf3de; color: #3B6D11; }

/* Steps preview */
.steps-preview {
    margin-top: 12px; padding-top: 12px;
    border-top: 1px solid #e8edf3;
}
.steps-preview .sp-label {
    font-size: 11px; font-weight: 600; letter-spacing: 1px;
    text-transform: uppercase; color: #888; margin-bottom: 8px;
}
.step-preview-item {
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; color: #555; padding: 4px 0;
}
.step-check {
    width: 14px; height: 14px; border-radius: 50%;
    border: 2px solid #ddd; flex-shrink: 0;
}
.step-check.done { background: #22c55e; border-color: #22c55e; }
.step-more {
    font-size: 12px; color: #aaa; margin-top: 4px;
    font-style: italic;
}

/* Quick form */
.quick-form { }
.qf-group { margin-bottom: 14px; }
.qf-group label {
    display: block; font-size: 12px; font-weight: 600;
    color: #555; margin-bottom: 6px;
    font-family: 'Segoe UI', sans-serif;
}
.qf-group select, .qf-group input {
    width: 100%; padding: 10px 14px;
    border: 1px solid #ddd; border-radius: 8px;
    font-size: 14px; outline: none;
    font-family: 'Segoe UI', sans-serif;
    transition: border 0.2s;
}
.qf-group select:focus, .qf-group input:focus { border-color: #22c55e; }

.no-subjects-msg {
    text-align: center; padding: 20px;
    font-size: 13px; color: #888; line-height: 1.6;
}
.no-subjects-msg a {
    color: #4A90D9; font-weight: 600; text-decoration: none;
}

/* Motivation line */
.motivation-line {
    text-align: center; margin-bottom: 16px;
    font-size: 13px; color: #888; font-style: italic;
}

/* Footer buttons */
.session-footer {
    padding: 0 24px 24px;
    display: flex; gap: 10px;
}
.btn-start-now {
    flex: 1; padding: 12px;
    background: #22c55e; color: #fff;
    border: none; border-radius: 10px;
    font-size: 14px; font-weight: 700;
    cursor: pointer; font-family: 'Segoe UI', sans-serif;
    transition: background 0.2s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
}
.btn-start-now:hover { background: #16a34a; }
.btn-skip {
    padding: 12px 18px;
    background: #f0f4f8; color: #777;
    border: none; border-radius: 10px;
    font-size: 13px; cursor: pointer;
    font-family: 'Segoe UI', sans-serif;
    transition: background 0.2s;
}
.btn-skip:hover { background: #e0e7ef; }

/* Loading state */
.session-loading {
    padding: 40px 24px;
    text-align: center; color: #aaa; font-size: 13px;
}
</style>

<!-- Floating button -->
<button class="fab-session" onclick="openStudySession()">
    <span class="fab-icon">⚡</span>
    Start Study Session
</button>

<!-- Session modal -->
<div class="session-overlay" id="sessionOverlay" onclick="closeOnSessionOverlay(event)">
    <div class="session-modal">
        <div class="session-header">
            <div>
                <h2 id="sessionTitle">Loading...</h2>
                <p id="sessionSubtitle"></p>
            </div>
            <button class="session-close" onclick="closeStudySession()">×</button>
        </div>
        <div class="session-body" id="sessionBody">
            <div class="session-loading">⏳ Finding your next task...</div>
        </div>
        <div class="session-footer" id="sessionFooter" style="display:none;">
            <button class="btn-skip" onclick="closeStudySession()">Cancel</button>
            <button class="btn-start-now" id="startNowBtn" onclick="launchTimer()">
                ▶ Start Timer Now
            </button>
        </div>
    </div>
</div>

<script>
let sessionTaskId  = null;
let sessionRole    = '<?= $current_role ?>';

const motivations = [
    "You showed up. That's already more than most.",
    "The best time to start was yesterday. Second best is now.",
    "Five minutes of focus beats five hours of planning.",
    "Your future self is counting on this moment.",
    "Momentum starts with one task. Pick it."
];

function openStudySession() {
    // Reset state
    sessionTaskId = null;
    document.getElementById('sessionTitle').textContent    = 'Start Study Session';
    document.getElementById('sessionSubtitle').textContent = '';
    document.getElementById('sessionBody').innerHTML       = '<div class="session-loading">⏳ Finding your next task...</div>';
    document.getElementById('sessionFooter').style.display = 'none';
    document.getElementById('sessionOverlay').classList.add('open');

    // Fetch smart suggestion — absolute path works from any module
    fetch('/includes/session_start.php')
        .then(r => r.json())
        .then(data => renderSessionModal(data))
        .catch(err => {
            document.getElementById('sessionBody').innerHTML =
                '<div class="session-loading" style="color:#e74c3c;">⚠️ Could not load session data. Refresh and try again.</div>';
        });
}

function renderSessionModal(data) {
    const body    = document.getElementById('sessionBody');
    const footer  = document.getElementById('sessionFooter');
    const title   = document.getElementById('sessionTitle');
    const subtitle = document.getElementById('sessionSubtitle');

    const motivMsg = motivations[Math.floor(Math.random() * motivations.length)];

    if (data.has_task) {
        const t = data.task;
        sessionTaskId = t.id;

        title.textContent    = 'Ready to go?';
        subtitle.textContent = 'Here\'s your next pending task.';

        const due = t.due_date
            ? new Date(t.due_date).toLocaleDateString('en-IN', {day:'numeric', month:'short'})
            : 'No due date';

        // Build steps preview (max 3)
        let stepsHtml = '';
        if (t.steps && t.steps.length > 0) {
            const shown = t.steps.slice(0, 3);
            const extra = t.steps.length - 3;
            stepsHtml = `
                <div class="steps-preview">
                    <div class="sp-label">Steps</div>
                    ${shown.map(s => `
                        <div class="step-preview-item">
                            <div class="step-check ${s.is_done ? 'done' : ''}"></div>
                            <span style="${s.is_done ? 'text-decoration:line-through;color:#aaa;' : ''}">${s.step_text}</span>
                        </div>
                    `).join('')}
                    ${extra > 0 ? `<div class="step-more">+${extra} more steps</div>` : ''}
                </div>`;
        }

        body.innerHTML = `
            <div class="motivation-line">"${motivMsg}"</div>
            <div class="task-preview">
                <div class="tp-subject">
                    <div class="tp-dot" style="background:${t.color_tag};"></div>
                    ${t.subject_name}
                </div>
                <div class="tp-title">${t.title}</div>
                <div class="tp-meta">
                    <span class="tp-priority p-${t.priority}">${t.priority}</span>
                    <span>📅 ${due}</span>
                    <span>⏱ ${t.estimated_minutes} min</span>
                </div>
                ${stepsHtml}
            </div>
            <p style="font-size:12px;color:#aaa;text-align:center;">Not this one?
               <a href="/tasks/index.php" style="color:#4A90D9;font-weight:600;text-decoration:none;">Pick from tasks →</a>
            </p>`;

        footer.style.display = 'flex';

    } else {
        // No pending tasks — show quick form
        title.textContent    = 'Start a session';
        subtitle.textContent = 'No pending tasks found. Create one quickly.';

        if (!data.subjects || data.subjects.length === 0) {
            body.innerHTML = `
                <div class="no-subjects-msg">
                    <div style="font-size:32px;margin-bottom:12px;">📚</div>
                    <p>You don't have any subjects yet.<br>
                    <a href="/subjects/index.php">Add a subject first →</a></p>
                </div>`;
            return;
        }

        const subjectOptions = data.subjects.map(s =>
            `<option value="${s.id}">${s.name}</option>`
        ).join('');

        body.innerHTML = `
            <div class="motivation-line">"${motivMsg}"</div>
            <div class="quick-form">
                <div class="qf-group">
                    <label>Subject</label>
                    <select id="quickSubject">
                        <option value="">Select a subject</option>
                        ${subjectOptions}
                    </select>
                </div>
                <div class="qf-group">
                    <label>What will you work on?</label>
                    <input type="text" id="quickTask" placeholder="e.g. Read chapter 4, Solve problems 1–10">
                </div>
            </div>`;

        document.getElementById('startNowBtn').textContent = '▶ Create & Start';
        footer.style.display = 'flex';
    }
}

async function launchTimer() {
    if (sessionTaskId) {
        window.location.href = `/tasks/focustimer.html?task_id=${sessionTaskId}&role=${sessionRole}`;
        return;
    }

    const subjectId = document.getElementById('quickSubject')?.value;
    const taskTitle = document.getElementById('quickTask')?.value.trim();

    if (!subjectId) { alert('Please select a subject.'); return; }
    if (!taskTitle) { alert('Please enter what you will work on.'); return; }

    const btn = document.getElementById('startNowBtn');
    btn.textContent = '⏳ Creating...';
    btn.disabled    = true;

    const body = new URLSearchParams({
        title:             taskTitle,
        subject_id:        subjectId,
        priority:          'medium',
        estimated_minutes: 25,
        description:       'Quick session task'
    });

    const res  = await fetch('/tasks/add_task.php', { method: 'POST', body });
    const data = await res.json();

    if (data.success) {
        window.location.href = `/tasks/focustimer.html?task_id=${data.id}&role=${sessionRole}`;
    } else {
        btn.textContent = '▶ Create & Start';
        btn.disabled    = false;
        alert(data.error || 'Something went wrong. Try again.');
    }
}

function closeStudySession() {
    document.getElementById('sessionOverlay').classList.remove('open');
}

function closeOnSessionOverlay(e) {
    if (e.target === document.getElementById('sessionOverlay')) closeStudySession();
}
</script>