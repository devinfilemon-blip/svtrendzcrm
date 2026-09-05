<?php
include 'layouts/session.php';
include 'layouts/config.php';
include 'layouts/crm-access.php';
crmRequireAdmin($link);
require_once __DIR__ . '/layouts/letterhead.php';

$letterId = (int)(letterRequest('id', 0));
$row = null;

if ($letterId > 0) {
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

    $stmt = $link->prepare("SELECT * FROM tblhr_letter WHERE iLetterid = ? AND sLetterType = 'offer' LIMIT 1");
    $stmt->bind_param('i', $letterId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        die('Offer letter not found.');
    }
}

if ($row) {
    $refNo = trim($row['sRefNo'] ?? '01');
    $letterDate = letterFmtDate($row['sLetterDate'] ?? date('Y-m-d'));
    $title = trim($row['sEmployeeTitle'] ?? 'Ms.');
    $name = trim($row['sEmployeeName'] ?? '');
    $designation = trim($row['sDesignation'] ?? '');
    $address = trim($row['sEmployeeAddress'] ?? '');
    $joinDateRaw = $row['sJoiningDate'] ?? date('Y-m-d');
    $salary = trim($row['sSalary'] ?? '');
    $probation = trim($row['sProbation'] ?? '3 months');
    $office = trim($row['sOfficeLocation'] ?? 'Ichalkaranji');
    $manager = trim($row['sReportingManager'] ?? '');
    $validity = (int)($row['iValidityDays'] ?? 7);
    $companyPhone = trim($row['sCompanyPhone'] ?? '9579801138');
    $companyAddress = trim($row['sCompanyAddress'] ?? '21/1945, beside National High School Road, Jawaharnagar, Ichalkaranji, Jawaharnagar, Maharashtra 416115');
    $signatureSrc = trim($row['sSignaturePath'] ?? '');
    if ($signatureSrc === '') {
        $signatureSrc = 'assets/images/director-signature.png';
    }
} else {
    $refNo = trim(letterRequest('ref_no', '01'));
    $letterDate = letterFmtDate(letterRequest('letter_date', date('Y-m-d')));
    $title = trim(letterRequest('employee_title', 'Ms.'));
    $name = trim(letterRequest('employee_name', ''));
    $designation = trim(letterRequest('designation', ''));
    $address = trim(letterRequest('employee_address', ''));
    $joinDateRaw = letterRequest('joining_date', date('Y-m-d'));
    $salary = trim(letterRequest('salary', ''));
    $probation = trim(letterRequest('probation', '3 months'));
    $office = trim(letterRequest('office_location', 'Ichalkaranji'));
    $manager = trim(letterRequest('reporting_manager', ''));
    $validity = (int)letterRequest('validity_days', 7);
    $companyPhone = trim(letterRequest('company_phone', '9579801138'));
    $companyAddress = trim(letterRequest('company_address', '21/1945, beside National High School Road, Jawaharnagar, Ichalkaranji, Jawaharnagar, Maharashtra 416115'));
    $signatureSrc = letterResolveSignature('signature');
}

if ($validity < 1) {
    $validity = 7;
}
$joinLong = letterFmtLongDate($joinDateRaw);
$displayName = trim($title . ' ' . $name);
$dearName = $name !== '' ? $name : 'Candidate';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Offer Letter - <?php echo htmlspecialchars($displayName); ?></title>
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
        .letter-title-wrap { text-align: center; margin: 6px 0 12px 0; }
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
            font-size: 13px;
            line-height: 1.45;
            text-align: justify;
        }
        .letter-body p { margin-bottom: 8px; }
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 10px 0;
            font-size: 13px;
        }
        .detail-table th, .detail-table td {
            border: 1px solid #d9d9d9;
            padding: 6px 10px;
            text-align: left;
        }
        .detail-table th {
            width: 38%;
            background: #f2f2f2;
            font-weight: 700;
        }
        .sign-company {
            margin-top: 14px;
            margin-left: auto;
            text-align: right;
            font-size: 13px;
            line-height: 1.4;
            padding-right: 10px;
        }
        .sign-company img {
            width: 140px;
            height: auto;
            display: block;
            margin: 6px 0 2px auto;
        }
        .accept-box {
            margin-top: 16px;
            border-top: 1px dashed #999;
            padding-top: 10px;
            font-size: 12.5px;
            line-height: 1.45;
        }
        .accept-box .sig-line {
            display: flex;
            justify-content: space-between;
            margin-top: 12px;
            gap: 24px;
        }
        .accept-box .sig-item { flex: 1; }
    </style>
</head>
<body>
<div class="no-print">
    <button type="button" class="btn" onclick="window.print()">Print</button>
    <a class="btn btn-secondary" href="<?php echo $letterId > 0 ? 'generate-offer-letter.php?id=' . (int)$letterId : 'generate-offer-letter.php'; ?>">Edit Details</a>
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
            <span class="letter-title">Offer Letter</span>
        </div>

        <div class="letter-body">
            <p>Dear <?php echo htmlspecialchars($dearName); ?>,</p>

            <p>
                We are pleased to offer you the position of
                <strong><?php echo htmlspecialchars($designation); ?></strong>
                at <strong>Infilemon Technologies Pvt Ltd</strong>.
                We believe your skills and experience will be a valuable addition to our team.
            </p>

            <p>Please find the key terms of this offer below:</p>

            <table class="detail-table">
                <tr>
                    <th>Position</th>
                    <td><?php echo htmlspecialchars($designation); ?></td>
                </tr>
                <tr>
                    <th>Expected Date of Joining</th>
                    <td><?php echo htmlspecialchars($joinLong); ?></td>
                </tr>
                <tr>
                    <th>Compensation (CTC)</th>
                    <td>&#8377; <?php echo htmlspecialchars($salary); ?></td>
                </tr>
                <tr>
                    <th>Work Location</th>
                    <td><?php echo htmlspecialchars($office); ?></td>
                </tr>
                <tr>
                    <th>Reporting Manager</th>
                    <td><?php echo htmlspecialchars($manager); ?></td>
                </tr>
                <tr>
                    <th>Probation Period</th>
                    <td><?php echo htmlspecialchars($probation); ?></td>
                </tr>
            </table>

            <p>
                During your employment, you will be required to abide by the company's rules,
                regulations, policies, and code of conduct. A detailed appointment/joining letter
                will be issued upon your joining and completion of required formalities.
            </p>

            <p>
                This offer is valid for <strong><?php echo (int)$validity; ?> days</strong> from the date of this letter.
                Kindly confirm your acceptance by signing the duplicate copy of this letter and
                returning it to us within the validity period.
            </p>

            <p>
                We look forward to welcoming you to Infilemon Technologies Pvt Ltd and wish you
                a successful career with us.
            </p>
        </div>

        <div class="sign-company">
            <div>For Infilemon Technologies Pvt Ltd.</div>
            <img src="<?php echo htmlspecialchars($signatureSrc); ?>" alt="Authorized Signatory">
            <div><strong>Authorized Signatory</strong></div>
        </div>

        <div class="accept-box">
            <p>
                I _______________ accept the above offer of employment and agree to join on the
                stated date, subject to the terms and conditions of the company.
            </p>
            <div class="sig-line">
                <div class="sig-item">Candidate Signature: _______________</div>
                <div class="sig-item">Date: _______________</div>
            </div>
        </div>
    </div>

    <?php echo letterheadFooterHtml($companyPhone, $companyAddress); ?>
</div>
</body>
</html>
