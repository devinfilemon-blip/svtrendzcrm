<?php
$quotationId = $_GET['id'] ?? ($_GET['quotation_id'] ?? 0);

if ($quotationId == 0) {
    die("Invalid quotation ID.");
}

require_once __DIR__ . '/layouts/config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tax Invoice Print</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, 'Segoe UI', sans-serif;
            background: #e5e5e5;
            color: #1b1b1b;
            font-size: 11.5px;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            background: #fff;
            margin: 10px auto;
            border: 1px solid #333;
            padding: 14px;
        }

        .doc-title-row {
            display: flex;
            justify-content: center;
            align-items: center;
            border-bottom: 1px solid #333;
            padding-bottom: 8px;
            margin-bottom: 8px;
            position: relative;
        }

        .doc-title-row .doc-title {
            font-size: 16px;
            font-weight: 700;
        }

        /* ============ HEADER ============ */
        .header-box {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 10px;
            border-bottom: 1px solid #333;
            margin-bottom: 0;
        }

        .header-box img.logo {
            height: 56px;
            max-width: 220px;
            object-fit: contain;
        }

        .header-right {
            text-align: right;
            flex: 1;
            margin-left: 14px;
        }

        .company-name {
            font-size: 16px;
            font-weight: 700;
        }

        .company-address, .company-contact {
            font-size: 10.5px;
            line-height: 1.4;
        }

        /* ============ INFO BOX ============ */
        .info-box {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .info-box td {
            border: 1px solid #333;
            border-top: none;
            vertical-align: top;
            padding: 6px 10px;
            font-size: 11px;
            line-height: 1.5;
        }

        .info-head {
            font-weight: 700;
            font-size: 11.5px;
            margin-bottom: 4px;
        }

        .info-box .cust-name { font-weight: 700; }

        /* ============ ITEMS TABLE ============ */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
            font-size: 10.5px;
        }

        .items-table th, .items-table td {
            border: 1px solid #333;
            padding: 5px 6px;
            text-align: left;
            vertical-align: top;
        }

        .items-table thead th {
            background: #f2f2f2;
            font-weight: 700;
            font-size: 10px;
            text-align: center;
        }

        .items-table thead { display: table-header-group; }
        .items-table tr { page-break-inside: avoid; }

        .items-table td.num, .items-table th.num { text-align: right; }
        .items-table td.center, .items-table th.center { text-align: center; }
        .items-table .item-name { font-weight: 700; }
        .items-table .pct { font-size: 9.5px; color: #444; }

        .items-table tfoot td {
            font-weight: 700;
            background: #f7f7f7;
        }

        /* ============ AMOUNTS / WORDS ============ */
        .bottom-row {
            display: flex;
            border: 1px solid #333;
            border-top: none;
        }

        .words-box {
            flex: 1;
            padding: 8px 10px;
            border-right: 1px solid #333;
            font-size: 11px;
        }

        .words-box .box-head { font-weight: 700; margin-bottom: 4px; }

        .amounts-box {
            width: 260px;
            border-collapse: collapse;
            font-size: 11px;
        }

        .amounts-box td {
            padding: 4px 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .amounts-box td:last-child { text-align: right; }
        .amounts-box tr.total-row td {
            font-weight: 700;
            font-size: 12.5px;
            border-bottom: none;
            border-top: 1px solid #333;
        }

        /* ============ FOOTER (Bank / Terms / Signature) ============ */
        .footer-row {
            display: flex;
            border: 1px solid #333;
            border-top: none;
            margin-bottom: 12px;
        }

        .bank-box, .terms-box, .sign-box {
            padding: 8px 10px;
            font-size: 10.5px;
            line-height: 1.5;
        }

        .bank-box { flex: 1.1; border-right: 1px solid #333; }
        .terms-box { flex: 1.3; border-right: 1px solid #333; }
        .sign-box { flex: 1; text-align: center; display: flex; flex-direction: column; justify-content: flex-end; }

        .box-head { font-weight: 700; margin-bottom: 4px; }
        .sign-space { height: 60px; }

        /* ============ PRINT ============ */
        @page { size: A4; margin: 6mm; }

        @media print {
            body { background: #fff; }
            .page {
                margin: 0;
                border: none;
                width: auto;
                min-height: 0;
            }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="page">
    <div class="doc-title-row">
        <span class="doc-title">Tax Invoice</span>
    </div>

    <div class="header-box">
        <img src="<?php echo APP_LOGO; ?>" class="logo" alt="<?php echo APP_NAME; ?>">
        <div class="header-right">
            <div class="company-name">S V TRENDZ</div>
            <div class="company-address">GAT NO.50, PLOT NO. 6A, IN SECTOR I, PHASE III (PLOT NO.463 AS PER TILR),<br>THE PARVATI CO.OPERATIVE INDUSTRIAL ESTATE LTD. YADRAV, Korochi, Kolhapur</div>
            <div class="company-contact">Phone no.: 7767000027&nbsp;&nbsp;Email: svtrendzz@gmail.com</div>
            <div class="company-contact">GSTIN: 27AVIPS4763M2ZQ, State: 27-Maharashtra</div>
        </div>
    </div>

    <table class="info-box">
        <tr>
            <td style="width:38%;">
                <div class="info-head">Quotation For</div>
                <div class="cust-name" id="qCustomer"></div>
                <div id="qCustomerAddress"></div>
                <div id="qCustomerPhone"></div>
                <div id="qCustomerGstin"></div>
            </td>
            <td style="width:30%;">
                <div class="info-head">Transportation Details</div>
                <div>Transport Name: <span id="qTransport"></span></div>
                <div>Mode of Transport: <span id="qModeTransport"></span></div>
                <div>Delivery: <span id="qDelivery"></span></div>
            </td>
            <td style="width:32%;">
                <div class="info-head">Quotation Details</div>
                <div>Quotation No. : <span id="qNo"></span></div>
                <div>Date : <span id="qDate"></span></div>
                <div>Validity : <span id="qValidity"></span></div>
                <div>Place of supply: 27-Maharashtra</div>
            </td>
        </tr>
    </table>

    <table class="items-table" id="itemsTable">
        <thead>
            <tr>
                <th style="width:3%;">#</th>
                <th style="width:19%;">Item name</th>
                <th style="width:7%;">HSN/SAC</th>
                <th style="width:6%;">Quantity</th>
                <th style="width:5%;">Unit</th>
                <th style="width:9%;">Price/ Unit</th>
                <th style="width:10%;">Discount</th>
                <th style="width:9%;">Taxable<br>Price/ Unit</th>
                <th style="width:9%;">Taxable<br>amount</th>
                <th style="width:8%;">CGST</th>
                <th style="width:8%;">SGST</th>
                <th style="width:9%;">Amount</th>
            </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
            <tr id="totalsRow"></tr>
        </tfoot>
    </table>

    <div class="bottom-row">
        <div class="words-box">
            <div class="box-head">Quotation Amount In Words</div>
            <div id="amountWords"></div>
        </div>
        <table class="amounts-box">
            <tr><td>Sub Total</td><td id="subTotal"></td></tr>
            <tr><td>Round off</td><td id="roundOff"></td></tr>
            <tr class="total-row"><td>Total</td><td id="grandTotal"></td></tr>
        </table>
    </div>

    <div class="footer-row">
        <div class="bank-box">
            <div class="box-head">Bank Details</div>
            <div>Name : SARASWAT COOPERATIVE BANK LIMITED, ICHALKARANJI, DIST. Kolhapur</div>
            <div>Account No. : 172100100003323</div>
            <div>IFSC code : SRCB0000172</div>
        </div>
        <div class="terms-box">
            <div class="box-head">Terms and Conditions</div>
            <div id="termsText">Payment Terms: 30% Advance &amp; remaining will be paid immediate after completion and Installation.<br>Installation Charges - 20%.</div>
        </div>
        <div class="sign-box">
            <div class="sign-space"></div>
            <div>For : S V TRENDZ</div>
            <div>Authorized Signatory</div>
        </div>
    </div>
</div>

<script>
function numberToWordsIndian(num) {
    num = Math.round(num);
    if (num === 0) return "Zero";
    const ones = ["", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine", "Ten",
        "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eighteen", "Nineteen"];
    const tens = ["", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety"];

    function twoDigits(n) {
        if (n < 20) return ones[n];
        return tens[Math.floor(n / 10)] + (n % 10 ? "-" + ones[n % 10] : "");
    }

    function threeDigits(n) {
        let str = "";
        if (n >= 100) {
            str += ones[Math.floor(n / 100)] + " Hundred";
            if (n % 100) str += " ";
        }
        if (n % 100) str += twoDigits(n % 100);
        return str;
    }

    let parts = [];
    const crore = Math.floor(num / 10000000); num %= 10000000;
    const lakh = Math.floor(num / 100000); num %= 100000;
    const thousand = Math.floor(num / 1000); num %= 1000;

    if (crore) parts.push(threeDigits(crore) + " Crore");
    if (lakh) parts.push(twoDigits(lakh) + " Lakh");
    if (thousand) parts.push(twoDigits(thousand) + " Thousand");
    if (num) parts.push(threeDigits(num));

    return parts.join(" ");
}

function fmtInr(n) {
    n = parseFloat(n) || 0;
    return n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

$(document).ready(function() {
    let quotationId = <?= (int)$quotationId ?>;

    $.ajax({
        url: "api.php",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({ action: "getQuotationById", id: quotationId }),
        success: function(response) {
            let res = typeof response === "string" ? JSON.parse(response) : response;
            if (res.status !== "success") return alert("Failed to fetch quotation.");

            let q = res.quotation;
            let items = res.items || [];

            $("#qNo").text(q.quotation_no || "");
            $("#qDate").text(q.quotation_date && q.quotation_date !== '0000-00-00' ? q.quotation_date : "");
            $("#qValidity").text(q.validity || "-");
            $("#qCustomer").text(q.customer_name || "");
            $("#qCustomerAddress").text(q.customer_address || "");
            $("#qCustomerPhone").text(q.customer_phone ? ("Phone: " + q.customer_phone) : "");
            $("#qCustomerGstin").text(q.sGstin ? ("GSTIN : " + q.sGstin) : "");
            $("#qTransport").text(q.sTransport || "");
            $("#qModeTransport").text(q.sModeoftransport || "");
            $("#qDelivery").text(q.delivery || "");
            if (q.payment) {
                $("#termsText").html(q.payment.replace(/\n/g, "<br>"));
            }

            let gstRate = parseFloat(q.gst) || 0;
            let halfGst = gstRate / 2;

            let subTotal = 0, taxableTotal = 0, cgstTotal = 0, sgstTotal = 0, grandTotal = 0, qtyTotal = 0;
            let tbody = "";

            items.forEach(function(row, idx) {
                let qty = parseFloat(row.quantity) || 0;
                let rate = parseFloat(row.rate) || 0;
                let grossAmt = parseFloat(row.amount);
                if (isNaN(grossAmt) || grossAmt <= 0) grossAmt = qty * rate;
                let discPct = parseFloat(row.discount) || 0;
                let taxableAmt = parseFloat(row.after_discount);
                if (isNaN(taxableAmt) || taxableAmt <= 0) {
                    taxableAmt = grossAmt - (grossAmt * discPct / 100);
                }
                let discountAmt = grossAmt - taxableAmt;
                let taxablePerUnit = qty > 0 ? taxableAmt / qty : 0;
                let cgstAmt = taxableAmt * (halfGst / 100);
                let sgstAmt = taxableAmt * (halfGst / 100);
                let lineTotal = taxableAmt + cgstAmt + sgstAmt;

                qtyTotal += qty;
                subTotal += grossAmt;
                taxableTotal += taxableAmt;
                cgstTotal += cgstAmt;
                sgstTotal += sgstAmt;
                grandTotal += lineTotal;

                let name = row.sProductname || ('Product #' + row.product_id);
                if (row.description) {
                    let desc = String(row.description).replace(/<[^>]*>/g, '').trim();
                    if (desc) name += '<br><span style="font-weight:400;font-size:9.5px;color:#555;">' + desc + '</span>';
                }

                tbody += '<tr>' +
                    '<td class="center">' + (idx + 1) + '</td>' +
                    '<td class="item-name">' + name + '</td>' +
                    '<td class="center">' + (row.item_hsn || '-') + '</td>' +
                    '<td class="num">' + qty + '</td>' +
                    '<td class="center">' + (row.item_unit || 'Nos') + '</td>' +
                    '<td class="num">&#8377; ' + fmtInr(rate) + '</td>' +
                    '<td class="num">&#8377; ' + fmtInr(discountAmt) + '<br><span class="pct">(' + discPct + '%)</span></td>' +
                    '<td class="num">&#8377; ' + fmtInr(taxablePerUnit) + '</td>' +
                    '<td class="num">&#8377; ' + fmtInr(taxableAmt) + '</td>' +
                    '<td class="num">&#8377; ' + fmtInr(cgstAmt) + '<br><span class="pct">(' + halfGst + '%)</span></td>' +
                    '<td class="num">&#8377; ' + fmtInr(sgstAmt) + '<br><span class="pct">(' + halfGst + '%)</span></td>' +
                    '<td class="num">&#8377; ' + fmtInr(lineTotal) + '</td>' +
                    '</tr>';
            });

            $("#itemsTable tbody").html(tbody);
            $("#totalsRow").html(
                '<td class="center" colspan="3">Total</td>' +
                '<td class="num">' + qtyTotal + '</td>' +
                '<td></td><td></td><td></td><td></td>' +
                '<td class="num">&#8377; ' + fmtInr(taxableTotal) + '</td>' +
                '<td class="num">&#8377; ' + fmtInr(cgstTotal) + '</td>' +
                '<td class="num">&#8377; ' + fmtInr(sgstTotal) + '</td>' +
                '<td class="num">&#8377; ' + fmtInr(grandTotal) + '</td>'
            );

            let rounded = Math.round(grandTotal);
            let roundOff = rounded - grandTotal;

            $("#subTotal").text('₹ ' + fmtInr(grandTotal));
            $("#roundOff").text((roundOff >= 0 ? '' : '- ') + '₹ ' + fmtInr(Math.abs(roundOff)));
            $("#grandTotal").text('₹ ' + rounded.toLocaleString('en-IN'));

            $("#amountWords").text(numberToWordsIndian(rounded) + " Rupees only");

            window.print();
        },
        error: function(xhr, status, error) {
            alert("Error: " + error);
        }
    });
});
</script>

</body>
</html>
