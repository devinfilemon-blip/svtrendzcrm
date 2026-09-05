<?php
include 'layouts/session.php';
include 'layouts/config.php';
include_once 'layouts/crm-access.php';
require_once __DIR__ . '/layouts/letterhead.php';

if (!crmCanListQuotation($link)) {
    header('Location: index.php');
    exit;
}

$token = preg_replace('/[^a-f0-9]/', '', strtolower((string)($_GET['f'] ?? '')));
if ($token === '' || strlen($token) !== 32) {
    die('Invalid PDF reference.');
}

$sessionKey = 'letterhead_pdf_' . $token;
$meta = $_SESSION[$sessionKey] ?? null;
$fileName = is_array($meta) ? (string)($meta['file'] ?? '') : '';
$origName = is_array($meta) ? (string)($meta['name'] ?? 'document.pdf') : 'document.pdf';

if ($fileName === '' || !preg_match('/^[a-f0-9]{32}\.pdf$/', $fileName)) {
    die('PDF session expired. Please upload again.');
}

$absPath = __DIR__ . '/uploads/letterhead-pdfs/' . $fileName;
if (!is_file($absPath)) {
    die('Uploaded PDF not found. Please upload again.');
}

// Stream the PDF bytes for PDF.js (same session, no public directory listing needed)
if (isset($_GET['stream']) && $_GET['stream'] === '1') {
    header('Content-Type: application/pdf');
    header('Content-Length: ' . filesize($absPath));
    header('Content-Disposition: inline; filename="' . rawurlencode($origName) . '"');
    header('Cache-Control: private, max-age=0, no-cache');
    readfile($absPath);
    exit;
}

$pdfUrl = 'print-upload-pdf.php?f=' . urlencode($token) . '&stream=1';
$titleSafe = htmlspecialchars($origName, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Print PDF — <?php echo $titleSafe; ?></title>
    <style>
<?php echo letterheadCss(); ?>

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 10px 34px 12px 34px;
        }
        .brand { display: flex; align-items: center; gap: 14px; }
        .brand-tag {
            font-size: 9px; letter-spacing: 0.5px; color: #444;
            margin-top: 4px; line-height: 1.25; font-weight: 600;
        }
        .badges { display: flex; align-items: flex-start; gap: 22px; padding-top: 6px; }
        .iso-badge, .startup-badge { text-align: center; }
        .iso-badge img { height: 49px; width: auto; display: block; margin: 0 auto; }
        .startup-badge img {
            height: 45px; width: auto; display: block; margin: 2px auto 0 auto;
            border: 1.5px solid #b5b5b5; padding: 2px 6px;
        }
        .iso-caption, .startup-caption {
            font-size: 8.5px; font-weight: 700; margin-top: 4px; line-height: 1.3;
        }
        .startup-caption { font-weight: 600; margin-top: 6px; color: #333; }

        .content.pdf-content {
            flex: 1;
            padding: 10px 28px 8px 28px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            overflow: hidden;
            min-height: 0;
        }
        .content.pdf-content canvas {
            max-width: 100%;
            max-height: 100%;
            width: auto !important;
            height: auto !important;
            object-fit: contain;
            display: block;
        }
        .status-box {
            max-width: 210mm;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 6px;
            text-align: center;
            font-size: 15px;
        }
        .status-box.error { color: #b00020; }

        @media print {
            .page {
                page-break-after: always !important;
                break-after: page !important;
            }
            .page:last-child {
                page-break-after: auto !important;
                break-after: auto !important;
            }
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
</head>
<body>
<div class="no-print">
    <a class="btn btn-secondary" href="upload-pdf.php">Upload another</a>
    <button type="button" class="btn" id="btnPrint" onclick="window.print()">Print / Save as PDF</button>
</div>

<div id="status" class="status-box no-print">Loading PDF…</div>
<div id="pages"></div>

<script>
(function () {
    var pdfUrl = <?php echo json_encode($pdfUrl); ?>;
    var statusEl = document.getElementById('status');
    var pagesEl = document.getElementById('pages');

    var headerHtml = `
    <div class="top-bar"><div class="bar-green"></div><div class="bar-yellow"></div></div>
    <div class="header">
        <div class="brand">
            <img src="assets/images/infilemon-logo.png" class="brand-logo" alt="Infilemon">
            <div class="brand-text">
                <div class="brand-name">INFILEMON</div>
                <div class="brand-sub">TECHNOLOGIES PVT. LTD.</div>
                <div class="brand-tag">WEB APP SOFTWARE DEVELOPMENT | AI | DATA <br>| DIGITAL MARKETING COMPANY |</div>
            </div>
        </div>
        <div class="badges">
            <div class="iso-badge">
                <img src="assets/images/iso-certified.png" alt="ISO 9001:2015 Certified">
                <div class="iso-caption">AN ISO 9001:2015<br>CERTIFIED COMPANY</div>
            </div>
            <div class="startup-badge">
                <img src="assets/images/startupindia.png" alt="#startupindia">
                <div class="startup-caption">GOV. OF INDIA DPIIT RECOGNISED<br>STARTUP</div>
            </div>
        </div>
    </div>
    <div class="navy-bar"></div>`;

    var footerHtml = <?php echo json_encode(letterheadFooterHtml()); ?>;

    if (typeof pdfjsLib === 'undefined') {
        statusEl.className = 'status-box error no-print';
        statusEl.textContent = 'Could not load PDF viewer library. Check your internet connection.';
        return;
    }

    pdfjsLib.GlobalWorkerOptions.workerSrc =
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    function fitCanvas(canvas, maxW, maxH) {
        var scale = Math.min(maxW / canvas.width, maxH / canvas.height, 1);
        canvas.style.width = Math.floor(canvas.width * scale) + 'px';
        canvas.style.height = Math.floor(canvas.height * scale) + 'px';
    }

    pdfjsLib.getDocument({ url: pdfUrl }).promise.then(function (pdf) {
        statusEl.textContent = 'Preparing ' + pdf.numPages + ' page(s)…';

        var chain = Promise.resolve();
        for (var i = 1; i <= pdf.numPages; i++) {
            (function (pageNum) {
                chain = chain.then(function () {
                    return pdf.getPage(pageNum).then(function (page) {
                        var viewport = page.getViewport({ scale: 1.6 });
                        var canvas = document.createElement('canvas');
                        canvas.width = viewport.width;
                        canvas.height = viewport.height;
                        return page.render({ canvasContext: canvas.getContext('2d'), viewport: viewport }).promise
                            .then(function () {
                                var pageDiv = document.createElement('div');
                                pageDiv.className = 'page';
                                pageDiv.innerHTML = headerHtml
                                    + '<div class="content pdf-content"></div>'
                                    + footerHtml;
                                var content = pageDiv.querySelector('.content');
                                content.appendChild(canvas);
                                pagesEl.appendChild(pageDiv);

                                // Fit into content band after layout
                                requestAnimationFrame(function () {
                                    var maxW = content.clientWidth || 700;
                                    var maxH = content.clientHeight || 900;
                                    fitCanvas(canvas, maxW, maxH);
                                });
                            });
                    });
                });
            })(i);
        }

        return chain.then(function () {
            statusEl.style.display = 'none';
        });
    }).catch(function (err) {
        statusEl.className = 'status-box error no-print';
        statusEl.textContent = 'Failed to load PDF: ' + (err && err.message ? err.message : 'unknown error');
    });
})();
</script>
</body>
</html>
