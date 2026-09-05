<?php
/**
 * Shared Infilemon letterhead (header/footer) matching print-quotation.php
 */
if (!function_exists('letterheadCss')) {
    function letterheadCss() {
        return <<<'CSS'
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Segoe UI', Calibri, Arial, sans-serif;
    background: #e5e5e5;
    color: #1b1b1b;
}

.page {
    width: 210mm;
    height: 297mm;
    min-height: 297mm;
    max-height: 297mm;
    background: #fff;
    margin: 0 auto 24px auto;
    position: relative;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.top-bar { position: relative; height: 16px; }
.top-bar .bar-green {
    position: absolute; left: 0; top: 0; width: 54%; height: 16px;
    background: #a7ca48; border-bottom-right-radius: 16px; z-index: 2;
}
.top-bar .bar-yellow {
    position: absolute; right: 0; top: 0; width: 49%; height: 9px;
    background: #f5d737; z-index: 1;
}

.header {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 10px 34px 12px 34px;
}

.brand { display: flex; align-items: center; justify-content: center; gap: 14px; }
.brand-logo { width: 92px; height: 92px; object-fit: contain; }
.brand-text { text-align: center; }
.brand-name {
    font-size: 30px; font-weight: 800; letter-spacing: 1.5px;
    color: #1b1b1b; line-height: 0.5;
}
.brand-sub {
    font-size: 12px; letter-spacing: 3px; color: #333;
    margin-top: 6px; padding-bottom: 4px; border-bottom: 2.5px solid #e8622a;
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

.navy-bar { height: 8px; background: #16243d; width: 100%; }

.content {
    flex: 1;
    padding: 18px 36px 12px 36px;
    display: flex;
    flex-direction: column;
}

.letter-footer {
    margin-top: auto;
    flex-shrink: 0;
    background: #fff;
    position: relative;
    z-index: 2;
}
.footer-navy { height: 8px; background: #16243d; width: 100%; }
.footer {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 8px 28px 6px 28px;
    font-size: 11px;
}
.footer .f-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 18px;
}
.footer .f-left { flex: 0 0 auto; min-width: 160px; }
.footer .f-left div { margin-bottom: 1px; white-space: nowrap; }
.footer .f-left b { display: inline-block; width: 42px; }
.footer .f-cin {
    flex: 0 0 auto;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
    padding-top: 1px;
}
.footer .f-right {
    display: flex;
    align-items: flex-start;
    gap: 6px;
    width: 100%;
    max-width: none;
    font-weight: 600;
    line-height: 1.35;
}
.footer .f-right .f-address {
    flex: 1;
    word-break: break-word;
    overflow-wrap: anywhere;
}
.footer .pin {
    color: #f5a623;
    font-size: 13px;
    flex-shrink: 0;
    line-height: 1.3;
}

.bottom-bar { position: relative; height: 18px; }
.bottom-bar .bar-green {
    position: absolute; left: 0; bottom: 0; width: 53%; height: 9px;
    background: #a7ca48; z-index: 1;
}
.bottom-bar .bar-yellow {
    position: absolute; right: 0; bottom: 0; width: 49%; height: 20px;
    background: #f5d737; border-top-left-radius: 20px; z-index: 2;
}

.no-print {
    max-width: 210mm;
    margin: 16px auto;
    text-align: right;
}
.no-print .btn {
    display: inline-block;
    padding: 10px 22px;
    background: #005aa5;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    margin-left: 8px;
}
.no-print .btn-secondary { background: #6c757d; }

@page { size: A4; margin: 0; }

@media print {
    html, body { background: #fff !important; margin: 0 !important; padding: 0 !important; }
    .no-print { display: none !important; }
    .page {
        margin: 0 !important;
        width: 210mm;
        height: 297mm;
        min-height: 297mm;
        max-height: 297mm;
        overflow: hidden;
        page-break-after: avoid;
        page-break-inside: avoid;
    }
    .letter-footer { page-break-inside: avoid; }
    .top-bar .bar-green, .top-bar .bar-yellow,
    .bottom-bar .bar-green, .bottom-bar .bar-yellow,
    .navy-bar, .footer-navy, .letter-title {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
CSS;
    }
}

if (!function_exists('letterheadHeaderHtml')) {
    function letterheadHeaderHtml() {
        return <<<'HTML'
    <div class="top-bar"><div class="bar-green"></div><div class="bar-yellow"></div></div>

    <div class="header">
        <div class="brand">
            <img src="assets/images/infilemon-logo.png" class="brand-logo" alt="Infilemon">
            <div class="brand-text">
                <div class="brand-name">INFILEMON</div>
                <div class="brand-sub">TECHNOLOGIES PVT. LTD.</div>
            </div>
        </div>
    </div>

    <div class="navy-bar"></div>
HTML;
    }
}

if (!function_exists('letterheadFooterHtml')) {
    function letterheadFooterHtml($phone = '', $address = '', $web = '', $mail = '', $cin = '') {
        $phone = trim((string)$phone);
        $address = trim((string)$address);
        $web = trim((string)$web);
        $mail = trim((string)$mail);
        $cin = trim((string)$cin);

        if ($phone === '') {
            $phone = '9579801138';
        }
        if ($address === '') {
            $address = '21/1945, beside National High School Road, Jawaharnagar, Ichalkaranji, Jawaharnagar, Maharashtra 416115';
        }
        if ($web === '') {
            $web = 'www.infilemon.com';
        }
        if ($mail === '') {
            $mail = 'contact@infilemon.com';
        }
        if ($cin === '') {
            $cin = 'U62099PN2025PTC236921';
        }

        $phone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars($address, ENT_QUOTES, 'UTF-8');
        $web = htmlspecialchars($web, ENT_QUOTES, 'UTF-8');
        $mail = htmlspecialchars($mail, ENT_QUOTES, 'UTF-8');
        $cin = htmlspecialchars($cin, ENT_QUOTES, 'UTF-8');

        return <<<HTML
    <div class="letter-footer">
        <div class="footer-navy"></div>
        <div class="footer">
            <div class="f-row">
                <div class="f-left">
                    <div><b>Phone</b> : {$phone}</div>
                    <div><b>Web</b> : {$web}</div>
                    <div><b>Mail</b> : {$mail}</div>
                </div>
                <div class="f-cin">CIN : {$cin}</div>
            </div>
            <div class="f-right">
                <span class="pin">&#9906;</span>
                <span class="f-address">{$address}</span>
            </div>
        </div>
        <div class="bottom-bar"><div class="bar-green"></div><div class="bar-yellow"></div></div>
    </div>
HTML;
    }
}

if (!function_exists('letterFmtDate')) {
    function letterFmtDate($value) {
        $value = trim((string)$value);
        if ($value === '') {
            return date('d/m/Y');
        }
        $ts = strtotime($value);
        return $ts ? date('d/m/Y', $ts) : $value;
    }
}

if (!function_exists('letterFmtLongDate')) {
    function letterFmtLongDate($value) {
        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }
        $ts = strtotime($value);
        return $ts ? date('l j F, Y', $ts) : $value;
    }
}

if (!function_exists('letterRequest')) {
    function letterRequest($key, $default = '') {
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }
        if (isset($_GET[$key])) {
            return $_GET[$key];
        }
        return $default;
    }
}

if (!function_exists('letterResolveSignature')) {
    /**
     * Save uploaded signature (if any) and return web-relative image path.
     * Falls back to default director signature.
     */
    function letterResolveSignature($fileKey = 'signature') {
        $default = 'assets/images/director-signature.png';

        if (empty($_FILES[$fileKey]) || !is_array($_FILES[$fileKey])) {
            return $default;
        }

        $file = $_FILES[$fileKey];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $default;
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return $default;
        }

        $tmp = $file['tmp_name'] ?? '';
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return $default;
        }

        $finfo = @getimagesize($tmp);
        if ($finfo === false) {
            return $default;
        }

        $mime = $finfo['mime'] ?? '';
        $extMap = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];
        if (!isset($extMap[$mime])) {
            return $default;
        }

        $dir = __DIR__ . '/../uploads/hr-signatures';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $name = 'sig_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extMap[$mime];
        $dest = $dir . DIRECTORY_SEPARATOR . $name;
        if (!@move_uploaded_file($tmp, $dest)) {
            return $default;
        }

        return 'uploads/hr-signatures/' . $name;
    }
}
