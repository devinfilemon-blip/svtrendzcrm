<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';
include_once 'layouts/crm-access.php';

if (!crmCanListQuotation($link)) {
    header('Location: index.php');
    exit;
}

$error = '';
$uploadDir = __DIR__ . '/uploads/letterhead-pdfs';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['pdf_file'] ?? null;
    if (!$file || !is_array($file)) {
        $error = 'Please choose a PDF file.';
    } elseif (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        $error = 'Please choose a PDF file.';
    } elseif (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        $error = 'Upload failed. Please try again.';
    } else {
        $tmp = $file['tmp_name'] ?? '';
        $origName = (string)($file['name'] ?? 'document.pdf');
        $size = (int)($file['size'] ?? 0);

        if ($tmp === '' || !is_uploaded_file($tmp)) {
            $error = 'Invalid upload.';
        } elseif ($size <= 0 || $size > 20 * 1024 * 1024) {
            $error = 'PDF must be between 1 byte and 20 MB.';
        } else {
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($tmp) ?: '';
            $allowedMimes = ['application/pdf', 'application/x-pdf', 'application/octet-stream'];
            $head = @file_get_contents($tmp, false, null, 0, 5);
            $isPdfMagic = is_string($head) && strncmp($head, '%PDF-', 5) === 0;

            if ($ext !== 'pdf' || !in_array($mime, $allowedMimes, true) || !$isPdfMagic) {
                $error = 'Only PDF files are allowed.';
            } else {
                $token = bin2hex(random_bytes(16));
                $destName = $token . '.pdf';
                $dest = $uploadDir . DIRECTORY_SEPARATOR . $destName;
                if (!@move_uploaded_file($tmp, $dest)) {
                    $error = 'Could not save the uploaded file.';
                } else {
                    $_SESSION['letterhead_pdf_' . $token] = [
                        'file' => $destName,
                        'name' => $origName,
                        'time' => time(),
                    ];
                    header('Location: print-upload-pdf.php?f=' . urlencode($token));
                    exit;
                }
            }
        }
    }
}
?>
<head>
    <title>Upload PDF</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .btn-primary { color: #fff; background-color: #005aa5; border-color: #005aa5; }
        .upload-hint { font-size: 13px; color: #6c757d; }
    </style>
</head>
<?php include 'layouts/body.php'; ?>
<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Upload PDF</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="quotation-list.php">Quotation</a></li>
                                    <li class="breadcrumb-item active">Upload PDF</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-8 col-lg-10">
                        <div class="card">
                            <div class="card-body">
                                <p class="upload-hint mb-3">
                                    Upload any PDF. Each page will open with the same Infilemon header and footer used on quotation print. Then print or Save as PDF from the browser.
                                </p>
                                <?php if ($error !== '') : ?>
                                    <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                                <?php endif; ?>
                                <form method="post" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label class="form-label" for="pdf_file">PDF file</label>
                                        <input type="file" class="form-control" id="pdf_file" name="pdf_file" accept="application/pdf,.pdf" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Upload &amp; Preview</button>
                                    <a href="quotation-list.php" class="btn btn-secondary">Cancel</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'layouts/footer.php'; ?>
    </div>
</div>
<?php include 'layouts/vendor-scripts.php'; ?>
</body>
</html>
