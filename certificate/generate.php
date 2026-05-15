<?php
/**
 * Certificate Generator
 * Uses FPDF (pure PHP, no extensions needed)
 * Place fpdf/ folder inside certificate/ directory
 * Download from: http://www.fpdf.org/
 *
 * If FPDF not available — falls back to styled HTML printable page
 */
require_once '../middleware/auth_check.php';
require_once '../config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$subject_id = (int)($_GET['subject_id'] ?? 0);
if (!$subject_id) {
    header("Location: ../subjects/index.php");
    exit;
}

// Verify certificate exists for this user
$stmt = $pdo->prepare("
    SELECT c.*, u.username, u.course
    FROM certificates c
    JOIN users u ON u.id = c.user_id
    WHERE c.user_id = ? AND c.subject_id = ?
    ORDER BY c.issued_at DESC
    LIMIT 1
");
$stmt->execute([$current_user_id, $subject_id]);
$cert = $stmt->fetch();

if (!$cert) {
    header("Location: ../subjects/index.php");
    exit;
}

$name         = $cert['username'];
$subject_name = $cert['subject_name'];
$score        = $cert['score'];
$course       = $cert['course'] ?? 'BCA';
$issued_date  = date('d F Y', strtotime($cert['issued_at']));
$cert_id      = 'SH-' . strtoupper(substr(md5($cert['user_id'] . $cert['subject_id'] . $cert['issued_at']), 0, 8));

// Try FPDF first
$fpdf_path = __DIR__ . '/fpdf/fpdf.php';

if (file_exists($fpdf_path)) {
    // ── FPDF PDF Generation ───────────────────────────────────────────────
    require($fpdf_path);

    class CertPDF extends FPDF {
        function Header() {}
        function Footer() {}
    }

    $pdf = new CertPDF('L', 'mm', 'A4'); // Landscape A4
    $pdf->AddPage();
    $pdf->SetAutoPageBreak(false);

    $W = 297; $H = 210; // A4 landscape dimensions

    // Background gradient simulation — dark rectangle
    $pdf->SetFillColor(6, 5, 8);
    $pdf->Rect(0, 0, $W, $H, 'F');

    // Decorative border — outer gold
    $pdf->SetDrawColor(194, 161, 77);
    $pdf->SetLineWidth(1.5);
    $pdf->Rect(8, 8, $W-16, $H-16);

    // Inner border
    $pdf->SetDrawColor(34, 197, 94);
    $pdf->SetLineWidth(0.5);
    $pdf->Rect(12, 12, $W-24, $H-24);

    // Corner decorations
    foreach ([[12,12],[285,12],[12,198],[285,198]] as [$cx,$cy]) {
        $pdf->SetFillColor(194, 161, 77);
        $pdf->Circle($cx, $cy, 3, 'F');
    }

    // StudyHub branding top
    $pdf->SetFont('Helvetica', 'B', 11);
    $pdf->SetTextColor(194, 161, 77);
    $pdf->SetXY(0, 18);
    $pdf->Cell($W, 8, 'STUDYHUB', 0, 0, 'C');

    // Subtitle
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->SetXY(0, 26);
    $pdf->Cell($W, 6, 'Role-Based Study Management System', 0, 0, 'C');

    // Horizontal line
    $pdf->SetDrawColor(194, 161, 77);
    $pdf->SetLineWidth(0.3);
    $pdf->Line(40, 34, $W-40, 34);

    // "Certificate of Completion"
    $pdf->SetFont('Times', 'BI', 28);
    $pdf->SetTextColor(240, 234, 248);
    $pdf->SetXY(0, 42);
    $pdf->Cell($W, 16, 'Certificate of Completion', 0, 0, 'C');

    // "This is to certify that"
    $pdf->SetFont('Helvetica', '', 11);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->SetXY(0, 62);
    $pdf->Cell($W, 8, 'This is to certify that', 0, 0, 'C');

    // Student name — large gold
    $pdf->SetFont('Times', 'B', 36);
    $pdf->SetTextColor(194, 161, 77);
    $pdf->SetXY(0, 72);
    $pdf->Cell($W, 20, $name, 0, 0, 'C');

    // Underline for name
    $name_width = $pdf->GetStringWidth($name) * 1.2;
    $name_x = ($W - $name_width) / 2;
    $pdf->SetDrawColor(194, 161, 77);
    $pdf->SetLineWidth(0.4);
    $pdf->Line($name_x, 92, $name_x + $name_width, 92);

    // "has successfully completed the quiz for"
    $pdf->SetFont('Helvetica', '', 11);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->SetXY(0, 96);
    $pdf->Cell($W, 8, 'has successfully completed the subject quiz for', 0, 0, 'C');

    // Subject name — green
    $pdf->SetFont('Helvetica', 'B', 22);
    $pdf->SetTextColor(34, 197, 94);
    $pdf->SetXY(0, 106);
    $pdf->Cell($W, 12, $subject_name, 0, 0, 'C');

    // Course
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetTextColor(120, 120, 120);
    $pdf->SetXY(0, 120);
    $pdf->Cell($W, 7, 'Programme: ' . $course, 0, 0, 'C');

    // Score badge area
    $pdf->SetFillColor(20, 40, 20);
    $pdf->RoundedRect(($W/2)-35, 130, 70, 22, 5, 'F');
    $pdf->SetFont('Helvetica', 'B', 14);
    $pdf->SetTextColor(34, 197, 94);
    $pdf->SetXY(0, 133);
    $pdf->Cell($W, 8, 'Score: ' . $score . '%', 0, 0, 'C');
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->SetTextColor(100, 180, 100);
    $pdf->SetXY(0, 141);
    $pdf->Cell($W, 5, 'Pass threshold: 70%', 0, 0, 'C');

    // Divider line
    $pdf->SetDrawColor(60, 60, 60);
    $pdf->SetLineWidth(0.2);
    $pdf->Line(40, 158, $W-40, 158);

    // Bottom metadata
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->SetTextColor(100, 100, 100);

    // Left — date
    $pdf->SetXY(40, 162);
    $pdf->Cell(60, 6, 'Date of Issue', 0, 0, 'L');
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->SetXY(40, 168);
    $pdf->Cell(60, 6, $issued_date, 0, 0, 'L');

    // Center — seal text
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetTextColor(194, 161, 77);
    $pdf->SetXY(0, 163);
    $pdf->Cell($W, 8, '* STUDYHUB VERIFIED *', 0, 0, 'C');
    $pdf->SetFont('Helvetica', '', 7);
    $pdf->SetTextColor(80, 80, 80);
    $pdf->SetXY(0, 171);
    $pdf->Cell($W, 5, 'Certificate ID: ' . $cert_id, 0, 0, 'C');

    // Right — certificate ID area
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->SetXY($W-120, 162);
    $pdf->Cell(80, 6, 'Issued by StudyHub', 0, 0, 'R');
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->SetXY($W-120, 168);
    $pdf->Cell(80, 6, 'study-hub.local', 0, 0, 'R');

    // Output as download
    $filename = 'StudyHub_Certificate_' . str_replace(' ', '_', $subject_name) . '.pdf';
    $pdf->Output('D', $filename);
    exit;
}

// ── FALLBACK: Styled HTML printable page ─────────────────────────────────────
// If FPDF is not installed, show a beautiful HTML certificate they can print
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate — <?= htmlspecialchars($subject_name) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: #1a1a2e;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            min-height: 100vh;
            padding: 24px;
            font-family: 'Segoe UI', sans-serif;
        }

        .actions {
            display: flex; gap: 12px; margin-bottom: 24px;
        }

        .btn {
            padding: 10px 24px; border-radius: 8px;
            font-size: 14px; font-weight: 600;
            cursor: pointer; border: none; font-family: inherit;
            transition: all 0.2s; text-decoration: none;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-print  { background: #22c55e; color: #fff; }
        .btn-print:hover  { background: #16a34a; }
        .btn-back   { background: rgba(255,255,255,0.1); color: #f0eaf8; }
        .btn-back:hover   { background: rgba(255,255,255,0.15); }

        /* Certificate */
        .cert {
            width: 900px; height: 620px;
            background: #060508;
            border: 2px solid #c2a14d;
            border-radius: 4px;
            position: relative;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 40px;
            text-align: center;
            box-shadow: 0 0 80px rgba(194,161,77,0.15),
                        inset 0 0 80px rgba(0,0,0,0.3);
        }

        /* Inner border */
        .cert::before {
            content: '';
            position: absolute; inset: 10px;
            border: 1px solid rgba(34,197,94,0.3);
            border-radius: 2px;
            pointer-events: none;
        }

        /* Corner dots */
        .corner {
            position: absolute; width: 8px; height: 8px;
            border-radius: 50%; background: #c2a14d;
        }
        .corner.tl { top: 10px; left: 10px; }
        .corner.tr { top: 10px; right: 10px; }
        .corner.bl { bottom: 10px; left: 10px; }
        .corner.br { bottom: 10px; right: 10px; }

        .cert-brand {
            font-size: 13px; font-weight: 700; letter-spacing: 4px;
            color: #c2a14d; text-transform: uppercase;
            margin-bottom: 4px;
        }
        .cert-brand-sub {
            font-size: 10px; color: rgba(255,255,255,0.3);
            letter-spacing: 1px; margin-bottom: 20px;
        }

        .cert-line {
            width: 200px; height: 1px;
            background: linear-gradient(90deg, transparent, #c2a14d, transparent);
            margin: 0 auto 20px;
        }

        .cert-title {
            font-family: 'Times New Roman', serif;
            font-size: 36px; font-style: italic;
            color: #f0eaf8; margin-bottom: 20px;
            font-weight: 400;
        }

        .cert-certify {
            font-size: 13px; color: rgba(240,234,248,0.45);
            margin-bottom: 12px; letter-spacing: 0.5px;
        }

        .cert-name {
            font-family: 'Times New Roman', serif;
            font-size: 44px; color: #c2a14d; font-weight: 700;
            margin-bottom: 4px; line-height: 1.1;
        }

        .cert-name-line {
            width: 60%; height: 1px; margin: 8px auto 20px;
            background: rgba(194,161,77,0.4);
        }

        .cert-completed {
            font-size: 13px; color: rgba(240,234,248,0.45);
            margin-bottom: 8px;
        }

        .cert-subject {
            font-size: 24px; font-weight: 700;
            color: #22c55e; margin-bottom: 6px;
        }

        .cert-course {
            font-size: 12px; color: rgba(240,234,248,0.3);
            margin-bottom: 20px;
        }

        .cert-score-badge {
            background: rgba(34,197,94,0.1);
            border: 1px solid rgba(34,197,94,0.3);
            border-radius: 30px; padding: 8px 24px;
            display: inline-block; margin-bottom: 28px;
        }
        .cert-score-badge span {
            font-size: 16px; font-weight: 700; color: #22c55e;
        }
        .cert-score-badge small {
            font-size: 11px; color: rgba(34,197,94,0.6); margin-left: 6px;
        }

        .cert-footer {
            display: flex; justify-content: space-between;
            width: 100%; padding: 0 20px;
            border-top: 1px solid rgba(255,255,255,0.05);
            padding-top: 16px; margin-top: 4px;
        }

        .cert-footer-item { text-align: center; }
        .cert-footer-item .label {
            font-size: 10px; color: rgba(240,234,248,0.25);
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;
        }
        .cert-footer-item .value {
            font-size: 12px; color: rgba(240,234,248,0.5); font-weight: 500;
        }

        .cert-seal {
            position: absolute; top: 20px; right: 24px;
            width: 64px; height: 64px; border-radius: 50%;
            border: 2px solid rgba(194,161,77,0.4);
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            background: rgba(194,161,77,0.05);
        }
        .cert-seal .seal-icon { font-size: 20px; }
        .cert-seal .seal-text {
            font-size: 7px; color: #c2a14d; letter-spacing: 1px;
            text-transform: uppercase; margin-top: 2px;
        }

        /* Print styles */
        @media print {
            body { background: #fff; padding: 0; }
            .actions { display: none; }
            .cert {
                width: 100%; height: auto;
                min-height: 90vh;
                box-shadow: none;
                border-color: #c2a14d;
            }
        }

        .install-note {
            margin-top: 16px;
            background: rgba(194,161,77,0.1);
            border: 1px solid rgba(194,161,77,0.2);
            border-radius: 8px; padding: 12px 20px;
            font-size: 12px; color: rgba(194,161,77,0.8);
            text-align: center; max-width: 900px;
        }
        .install-note a { color: #c2a14d; }
    </style>
</head>
<body>

<div class="actions">
    <button class="btn btn-print" onclick="window.print()">🖨️ Print / Save as PDF</button>
    <a href="../quiz/result.php?subject_id=<?= $subject_id ?>&already=1"
       class="btn btn-back">← Back to Result</a>
</div>

<!-- Certificate -->
<div class="cert">
    <div class="corner tl"></div>
    <div class="corner tr"></div>
    <div class="corner bl"></div>
    <div class="corner br"></div>

    <div class="cert-seal">
        <div class="seal-icon">🏆</div>
        <div class="seal-text">Verified</div>
    </div>

    <div class="cert-brand">StudyHub</div>
    <div class="cert-brand-sub">Role-Based Study Management System</div>

    <div class="cert-line"></div>

    <div class="cert-title">Certificate of Completion</div>

    <div class="cert-certify">This is to certify that</div>

    <div class="cert-name"><?= htmlspecialchars($name) ?></div>
    <div class="cert-name-line"></div>

    <div class="cert-completed">has successfully completed the subject quiz for</div>
    <div class="cert-subject"><?= htmlspecialchars($subject_name) ?></div>
    <div class="cert-course">Programme: <?= htmlspecialchars($course) ?></div>

    <div class="cert-score-badge">
        <span>Score: <?= $score ?>%</span>
        <small>Pass threshold: 70%</small>
    </div>

    <div class="cert-footer">
        <div class="cert-footer-item">
            <div class="label">Date of Issue</div>
            <div class="value"><?= $issued_date ?></div>
        </div>
        <div class="cert-footer-item">
            <div class="label">Certificate ID</div>
            <div class="value"><?= $cert_id ?></div>
        </div>
        <div class="cert-footer-item">
            <div class="label">Issued by</div>
            <div class="value">StudyHub Platform</div>
        </div>
    </div>
</div>

<div class="install-note">
    💡 To download as PDF: click <strong>Print / Save as PDF</strong> above →
    choose <strong>"Save as PDF"</strong> as the destination in your browser's print dialog.
    For auto-PDF generation, install
    <a href="http://www.fpdf.org/" target="_blank">FPDF</a>
    in <code>certificate/fpdf/</code>.
</div>

</body>
</html>