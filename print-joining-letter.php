<?php
include 'layouts/session.php';
include 'layouts/config.php';
include 'layouts/crm-access.php';
crmRequireAdmin($link);
require_once __DIR__ . '/layouts/letterhead.php';

$letterId = (int)(letterRequest('id', 0));
$row = null;

if ($letterId > 0) {
    // Ensure table exists
    mysqli_query($link, "CREATE TABLE IF NOT EXISTS tblhr_letter (
      iLetterid INT AUTO_INCREMENT PRIMARY KEY,
      sLetterType ENUM('offer','joining') NOT NULL,
      sRefNo VARCHAR(50) NOT NULL DEFAULT '',
      sLetterDate DATE NULL,
      sEmployeeTitle VARCHAR(20) NOT NULL DEFAULT 'Ms.',
      sEmployeeName VARCHAR(150) NOT NULL DEFAULT '',
      sDesignation VARCHAR(150) NOT NULL DEFAULT '',
      sEmployeeAddress TEXT NULL,
      sJoiningDate DATE NULL,
      sJoiningTime VARCHAR(50) NULL,
      sOfficeLocation VARCHAR(150) NULL,
      sReportingManager VARCHAR(150) NULL,
      sSalary VARCHAR(120) NULL,
      sProbation VARCHAR(100) NULL,
      iValidityDays INT NULL DEFAULT 7,
      sCompanyPhone VARCHAR(50) NULL,
      sCompanyAddress TEXT NULL,
      sSignaturePath VARCHAR(255) NULL,
      iCreatedBy INT NULL,
      sCreatedTimeStamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      sModifiedTimestamp DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $stmt = $link->prepare("SELECT * FROM tblhr_letter WHERE iLetterid = ? AND sLetterType = 'joining' LIMIT 1");
    $stmt->bind_param('i', $letterId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        die('Joining letter not found.');
    }
}

if ($row) {
    $refNo = trim($row['sRefNo'] ?? '02');
    $letterDate = letterFmtDate($row['sLetterDate'] ?? date('Y-m-d'));
    $title = trim($row['sEmployeeTitle'] ?? 'Ms.');
    $name = trim($row['sEmployeeName'] ?? '');
    $designation = trim($row['sDesignation'] ?? '');
    $address = trim($row['sEmployeeAddress'] ?? '');
    $joinDateRaw = $row['sJoiningDate'] ?? date('Y-m-d');
    $joinTime = trim($row['sJoiningTime'] ?? '10 AM');
    $office = trim($row['sOfficeLocation'] ?? 'Ichalkaranji');
    $manager = trim($row['sReportingManager'] ?? '');
    $companyPhone = trim($row['sCompanyPhone'] ?? '9579801138');
    $companyAddress = trim($row['sCompanyAddress'] ?? '21/1945, beside National High School Road, Jawaharnagar, Ichalkaranji, Jawaharnagar, Maharashtra 416115');
    $signatureSrc = trim($row['sSignaturePath'] ?? '');
    if ($signatureSrc === '') {
        $signatureSrc = 'assets/images/director-signature.png';
    }
} else {
    $refNo = trim(letterRequest('ref_no', '02'));
    $letterDate = letterFmtDate(letterRequest('letter_date', date('Y-m-d')));
    $title = trim(letterRequest('employee_title', 'Ms.'));
    $name = trim(letterRequest('employee_name', ''));
    $designation = trim(letterRequest('designation', ''));
    $address = trim(letterRequest('employee_address', ''));
    $joinDateRaw = letterRequest('joining_date', date('Y-m-d'));
    $joinTime = trim(letterRequest('joining_time', '10 AM'));
    $office = trim(letterRequest('office_location', 'Ichalkaranji'));
    $manager = trim(letterRequest('reporting_manager', ''));
    $companyPhone = trim(letterRequest('company_phone', '9579801138'));
    $companyAddress = trim(letterRequest('company_address', '21/1945, beside National High School Road, Jawaharnagar, Ichalkaranji, Jawaharnagar, Maharashtra 416115'));
    $signatureSrc = letterResolveSignature('signature');
}

$joinLong = letterFmtLongDate($joinDateRaw);
$joinShort = letterFmtDate($joinDateRaw);
$displayName = trim($title . ' ' . $name);
$dearName = $name !== '' ? $name : 'Candidate';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Joining Letter - <?php echo htmlspecialchars($displayName); ?></title>
    <style>
        <?php echo letterheadCss(); ?>

        .meta-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 14px;
        }
        .to-block {
            font-size: 14px;
            line-height: 1.45;
            margin-bottom: 12px;
        }
        .to-block .to-label { font-weight: 700; }
        .letter-title-wrap { text-align: center; margin: 8px 0 16px 0; }
        .letter-title {
            display: inline-block;
            background: #f7c50e;
            font-size: 18px;
            font-weight: 700;
            padding: 6px 28px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .letter-body {
            font-size: 13.5px;
            line-height: 1.55;
            text-align: justify;
        }
        .letter-body p { margin-bottom: 12px; }
        .letter-body ul {
            margin: 6px 0 14px 22px;
        }
        .letter-body ul li { margin-bottom: 4px; }
        .sign-company {
            margin-top: 22px;
            margin-left: auto;
            text-align: right;
            font-size: 13.5px;
            line-height: 1.4;
            padding-right: 10px;
        }
        .sign-company img {
            width: 150px;
            height: auto;
            display: block;
            margin: 8px 0 4px auto;
        }
        .accept-box {
            margin-top: 28px;
            border-top: 1px dashed #999;
            padding-top: 14px;
            font-size: 13px;
            line-height: 1.55;
        }
        .accept-box .sig-line {
            display: flex;
            justify-content: space-between;
            margin-top: 18px;
            gap: 24px;
        }
        .accept-box .sig-item { flex: 1; }

        /* ── Company Policy pages ── */
        .page + .page {
            margin-top: 24px;
        }
        @media print {
            .page + .page {
                margin-top: 0 !important;
                page-break-before: always;
            }
        }
        .policy-content {
            flex: 1;
            padding: 18px 36px 12px 36px;
            font-family: 'Segoe UI', Calibri, Arial, sans-serif;
            font-size: 13px;
            color: #1b1b1b;
        }
        .policy-title {
            text-align: center;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 14px;
            letter-spacing: 0.5px;
        }
        .policy-section {
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 8px;
        }
        .policy-section:last-child { border-bottom: none; }
        .policy-section h4 {
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .policy-section ul {
            margin: 0 0 0 20px;
            padding: 0;
        }
        .policy-section ul li {
            margin-bottom: 3px;
            line-height: 1.45;
        }
        .policy-section p {
            margin: 0;
            line-height: 1.5;
        }
        .policy-section p.highlight { color: #1155cc; }
        .policy-declaration {
            margin-top: 10px;
            padding-top: 8px;
            font-size: 13px;
        }
        .policy-declaration .decl-text { color: #1155cc; margin-bottom: 10px; }
        .policy-sig-block { margin-top: 14px; line-height: 2; font-weight: 600; }
        .policy-auth {
            margin-top: 16px;
            text-align: right;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.5;
        }
    </style>
</head>
<body>
<div class="no-print">
    <button type="button" class="btn" onclick="window.print()">Print</button>
    <a class="btn btn-secondary" href="<?php echo $letterId > 0 ? 'generate-joining-letter.php?id=' . (int)$letterId : 'generate-joining-letter.php'; ?>">Edit Details</a>
</div>

<div class="page">
    <?php echo letterheadHeaderHtml(); ?>

    <div class="content">
        <div class="meta-row">
            <div>Ref No.: <?php echo htmlspecialchars($refNo); ?></div>
            <div>Date: <?php echo htmlspecialchars($letterDate); ?></div>
        </div>

        <div class="to-block">
            <div class="to-label">To,</div>
            <div><?php echo htmlspecialchars($displayName); ?><?php echo $displayName !== '' ? ',' : ''; ?></div>
            <?php if ($address !== '') : ?>
                <div><?php echo nl2br(htmlspecialchars($address)); ?></div>
            <?php endif; ?>
        </div>

        <div class="letter-title-wrap">
            <span class="letter-title">Joining Letter</span>
        </div>

        <div class="letter-body">
            <p>Dear <?php echo htmlspecialchars($dearName); ?>,</p>

            <p>
                We are pleased to inform you that you have been appointed as
                <strong><?php echo htmlspecialchars($designation); ?></strong>
                in Infilemon Technologies Pvt Ltd.
            </p>

            <p>
                You are required to report for duty on
                <strong><?php echo htmlspecialchars($joinLong); ?></strong>
                at <strong><?php echo htmlspecialchars($joinTime); ?></strong>
                at our office located at <strong><?php echo htmlspecialchars($office); ?></strong>.
            </p>

            <p>
                Your reporting manager will be
                <strong><?php echo htmlspecialchars($manager); ?></strong>.
                During your employment, you are expected to comply with the company's rules,
                regulations, policies, and code of conduct.
            </p>

            <p>Please carry the following documents at the time of joining:</p>
            <ul>
                <li>Original and photocopies of educational certificates</li>
                <li>Government Photo ID (Aadhaar/PAN/Passport, etc.)</li>
                <li>Passport-size photographs</li>
                <li>Bank account details</li>
                <li>Previous employment documents (if applicable)</li>
            </ul>

            <p>
                This letter serves as your official joining/posting confirmation.
                We are confident that your skills and dedication will contribute significantly
                to our organization's success.
            </p>

            <p>
                We warmly welcome you to our team and wish you a successful career with
                Infilemon Technologies Pvt Ltd. Thank you.
            </p>
        </div>

        <div class="sign-company">
            <div>For Infilemon Technologies Pvt Ltd.</div>
            <img src="<?php echo htmlspecialchars($signatureSrc); ?>" alt="Authorized Signatory">
            <div><strong>Authorized Signatory</strong></div>
        </div>

        <div class="accept-box">
            <p>
                I _______________ accept the above joining/posting instructions and agree to abide
                by the rules and regulations of the company.
            </p>
            <div class="sig-line">
                <div class="sig-item">Employee Signature: _______________</div>
                <div class="sig-item">Date: <?php echo htmlspecialchars($joinShort); ?></div>
            </div>
        </div>
    </div>

    <?php echo letterheadFooterHtml($companyPhone, $companyAddress); ?>
</div>

<!-- Company Policy Document – Page 1 -->
<div class="page">
    <?php echo letterheadHeaderHtml(); ?>

    <div class="policy-content">
        <div class="policy-title">Company Policy Document</div>

        <div class="policy-section">
            <h4>1. Introduction</h4>
            <p class="highlight">This document outlines the policies, rules, and guidelines of Infilemon Technologies Pvt Ltd. All employees are expected to follow these policies to maintain professionalism and work efficiency.</p>
        </div>

        <div class="policy-section">
            <h4>2. Working Hours</h4>
            <ul>
                <li>Standard working hours: <strong>10:00 AM to 6:00 PM</strong></li>
                <li>Weekly off: <strong>Sunday</strong></li>
                <li>Employees must be punctual and maintain attendance discipline.</li>
            </ul>
        </div>

        <div class="policy-section">
            <h4>3. Attendance &amp; Leave Policy</h4>
            <ul>
                <li>Prior approval is required for any leave.</li>
                <li>Emergency leave must be informed on the same day.</li>
                <li>Continuous absence without notice may lead to disciplinary action.</li>
            </ul>
        </div>

        <div class="policy-section">
            <h4>4. Work Responsibility</h4>
            <ul>
                <li>Employees must complete assigned tasks within deadlines.</li>
                <li>Maintain quality in all work (development, design, marketing).</li>
                <li>Follow instructions from reporting manager.</li>
            </ul>
        </div>

        <div class="policy-section">
            <h4>5. Code of Conduct</h4>
            <ul>
                <li>Maintain professional behaviour in office and with clients.</li>
                <li>No misuse of company resources.</li>
                <li>Respect all team members.</li>
                <li>No harassment or inappropriate behaviour will be tolerated.</li>
            </ul>
        </div>

        <div class="policy-section">
            <h4>6. Confidentiality Policy</h4>
            <ul>
                <li>Company data, client details, and project information must remain confidential.</li>
                <li>Sharing internal data without permission is strictly prohibited.</li>
            </ul>
        </div>
    </div>

    <?php echo letterheadFooterHtml($companyPhone, $companyAddress); ?>
</div>

<!-- Company Policy Document – Page 2 -->
<div class="page">
    <?php echo letterheadHeaderHtml(); ?>

    <div class="policy-content">

        <div class="policy-section">
            <h4>7. Salary &amp; Payments</h4>
            <ul>
                <li>Salary will be credited on a fixed date every month.</li>
                <li>Any deductions or bonuses will be as per company rules.</li>
            </ul>
        </div>

        <div class="policy-section">
            <h4>8. Probation Period</h4>
            <ul>
                <li>Initial probation period: <strong>3 months</strong></li>
                <li>Performance will be reviewed before confirmation.</li>
            </ul>
        </div>

        <div class="policy-section">
            <h4>9. Termination Policy</h4>
            <ul>
                <li>Either party can terminate employment with prior notice.</li>
                <li>Immediate termination may occur in case of misconduct.</li>
            </ul>
        </div>

        <div class="policy-section">
            <h4>10. Use of Company Assets</h4>
            <ul>
                <li>Company devices, software, and accounts must be used only for official purposes.</li>
                <li>Any damage or misuse will be the employee's responsibility.</li>
            </ul>
        </div>

        <div class="policy-section">
            <h4>11. Performance Review</h4>
            <ul>
                <li>Regular performance reviews will be conducted.</li>
                <li>Promotions and salary increments depend on performance.</li>
            </ul>
        </div>

        <div class="policy-section">
            <h4>12. Amendments</h4>
            <ul>
                <li>Company reserves the right to update policies at any time.</li>
            </ul>
        </div>

        <div class="policy-declaration">
            <h4>Declaration</h4>
            <p class="decl-text highlight">I hereby acknowledge that I have read and understood the company policies and agree to abide by them.</p>
            <div class="policy-sig-block">
                Employee Name: <?php echo htmlspecialchars($displayName); ?><br>
                Signature: ____________________<br>
                Date: ____________________
            </div>
        </div>

        <div class="policy-auth">
            <div>For Infilemon Technologies Pvt Ltd.</div>
            <img src="<?php echo htmlspecialchars($signatureSrc); ?>" alt="Authorized Signatory" style="width:150px;height:auto;display:block;margin:6px 0 4px auto;">
            <strong>Authorized Signatory</strong>
        </div>
    </div>

    <?php echo letterheadFooterHtml($companyPhone, $companyAddress); ?>
</div>
</body>
</html>
