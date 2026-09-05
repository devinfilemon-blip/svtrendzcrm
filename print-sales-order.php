<?php
$salesOrderId = $_GET['id'] ?? ($_GET['sales_order_id'] ?? 0);

if ($salesOrderId == 0) {
    die("Invalid sales order ID.");
}

require_once __DIR__ . '/layouts/config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sales Order Print</title>
    <style>
        body { font-family: Calibri, Arial, sans-serif; margin: 0; padding: 0; }
        table { border-collapse: collapse; width: 100%; font-size: 15px; }
        th, td { border: 1px solid #000; padding: 5px; vertical-align: top; }
        .no-border td, .no-border th { border: none; }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        .header-title { font-size: 18px; font-weight: bold; text-align: center; text-decoration: underline; }
        .outer-border { border: 2px solid #000; padding: 5px; margin: 10px; }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="outer-border">

    <!-- HEADER -->
    <table>
        <tr>
            <td>
                <center><img src="<?php echo APP_LOGO; ?>" alt="<?php echo APP_NAME; ?>" style="height: 48px; max-width: 220px; object-fit: contain;"></center>
            </td>
        </tr>
    </table>
    <div class="header-title">SALES ORDER</div>

    <!-- CUSTOMER & SALES ORDER DETAILS -->
    <table>
        <tr>
            <!-- Left Side: Customer -->
            <td width="40%" valign="top">
                <b>Customer:-</b><br><br>
                <b>To,</b><br>
                <span id="soCustomer"></span><br>
                <span id="soCustomerDetails"></span>
            </td>

            <!-- Right Side: Company -->
            <td width="40%" valign="top" align="center">
                <b><?php echo APP_NAME; ?></b><br>
                Plot No. 120, Sector - C, Phase - II, Yadrav Co-Op<br>
                Industrial Estate, Kolhapur, MH<br>
                Email: accounts@enhancew.com<br>
                Contact: 09881382626, 08408817111<br><br>
            </td>
            <td width="25%" >
             <center><img src="<?php echo APP_LOGO; ?>" alt="<?php echo APP_NAME; ?>" style="height: 56px; max-width: 100%; object-fit: contain;"></center>  
            </td>
        </tr>
    </table>

    <!-- Sales Order Details Row -->
    <table>
        <tr>
            <td width="25%"><b>Kind Attn:</b></td>
            <td width="25%"><span id="soKindAttn"></span></td>
            <td width="25%"><b>GSTIN:</b></td>
            <td width="25%"><span id="companyGstin"></span></td>
        </tr>
        <tr>
            <td><b>Sales Order No:</b></td>
            <td><span id="soNo"></span></td>
            <td><b>Ref No:</b></td>
            <td><span id="soRef"></span></td>
        </tr>
        <tr>
            <td><b>Sales Order Date:</b></td>
            <td><span id="soDate"></span></td>
            <td><b>Date:</b></td>
            <td><span id="soRefDate"></span></td>
        </tr>
        <tr>
            <td><b>Mode of Transport:</b></td>
            <td colspan="3"><span id="soTransport"></span></td>
        </tr>
    </table>

    <!-- ITEMS -->
  <table style="margin-top:15px; width:100%;" id="itemsTable" border="1" cellspacing="0" cellpadding="6">
    <thead>
        <tr class="bold center">
            <th>SR.NO</th>
            <th colspan="2">Item Description</th>
            <th>QTY</th>
            <th>NET per Unit Price</th>
            <th>Discount</th>
            <th>Final / Piece</th>
            <th>Total Amount</th>
        </tr>
    </thead>
    <tbody>
        <!-- Rows from API will be appended here -->
    </tbody>
    <tfoot>
        <tr>
            <td colspan="7" class="right bold">Sub Total</td>
            <td class="right" id="baseAmount"></td>
        </tr>
        <tr>
            <td colspan="7" class="right bold">GST</td>
            <td class="right" id="gstAmount"></td>
        </tr>
        <tr>
            <td colspan="7" class="right bold">Net Total</td>
            <td class="right bold" id="netTotal"></td>
        </tr>
    </tfoot>
</table>

<p>Subject to Ichalkaranji Jurisdiction.<br>
Interest @ 25% will be charged if Bill is not paid on or before due date.
</p>
    <!-- TERMS -->
   <table style="margin-top:5px; width:100%; border-collapse:collapse;" border="1">
    <tr>
        <td width="15%"><b>Net Payable</b></td>
        <td width="35%"></td>
      
    </tr>
    <tr>
         <td><b>Delivery:</b></td>
        <td><span id="soDelivery"></span></td>
       <td width="15%"><b>Transport:</b></td>
        <td width="35%"><span id="soTransport2"></span></td>
    </tr>
    <tr>
        <td><b>Payments:</b></td>
        <td><span id="soPayments"></span></td>
          <td><b>Transit Insurance:</b></td>
        <td><span id="soInsurance"></span></td>
       
    </tr>
    <tr>
     
       
         <td><b>Validity:</b></td>
        <td><span id="soValidity"></span></td>
          <td><b>Special Note:</b></td>
        <td><span id="soNote"></span></td>
    </tr>

        <tr>
    
          <td width="15%"><b>GST :</b></td>
        <td width="35%"><span id="sogst"></span>%</td>
        </tr>

        <!-- <tr>
    
          <td width="15%"><b>Tungaloy E-Catalog</b></td>
        <td width="35%">Tungaloy Cutting Tools - Metal Working Tools</span></td>
         
        </tr> -->
        
</table>


</div> <!-- end outer-border -->

<script>
$(document).ready(function() {
    let salesOrderId = <?= $salesOrderId ?>;

    $.ajax({
        url: "api.php",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({ action: "getSalesOrderById", id: salesOrderId }),
        success: function(response) {
            let res = typeof response === "string" ? JSON.parse(response) : response;
            if (res.status !== "success") return alert("Failed to fetch sales order.");

            let so = res.salesOrder;
            let items = res.items;

            // Header
            $("#companyGstin").text(so.sGstin || "");
            $("#soCustomer").text(so.customer_name);
            $("#soCustomerDetails").html(so.customer_address || "");
            $("#soKindAttn").text(so.sKindattn || "");
            $("#soNo").text(so.sales_no);
            $("#soDate").text(so.sales_date);
            $("#soTransport").text(so.sModeoftransport || "");
            $("#soRef").text(so.sRefno || "");
            $("#soRefDate").text(so.sDate || "");
            $("#soDelivery").text(so.delivery || "");
            $("#soPayments").text(so.payment || "");
            $("#soNote").text(so.specialnote || "");
            $("#soValidity").text(so.validity || "");
            $("#soTransport2").text(so.sTransport || "");
            $("#soInsurance").text(so.sTransportInsurance || "");
            $("#sogst").text(so.gst || "");

            // Check if any discount > 0
            let hasDiscount = items.some(row => row.discount && row.discount > 0);

            // Table header
            let thead = `
                <tr class="bold center">
                    <th>SR.NO</th>
                    <th colspan='2'>Item Description</th>
                  
                    <th>QTY</th>
                    <th>NET per Unit Price</th>
                    ${hasDiscount ? '<th>Discount</th><th>Final / Piece</th>' : ''}
                    <th>Total Amount</th>
                </tr>`;
            $("#itemsTable thead").html(thead);

            // Table rows
            let total = 0, tbody = "";
            items.forEach((row, i) => {
                let amount = row.quantity * row.rate;
                let discount = row.discount ? (amount * row.discount / 100) : 0;
                let final = amount - discount;
                total += final;

                tbody += `
                    <tr>
                        <td>${i+1}</td>
                        <td>${row.sProductname || ''}</td>
                        <td>${row.description || ''}</td>
                        <td class="center">${row.quantity}</td>
                        <td class="right">${parseFloat(row.rate).toFixed(2)}</td>
                        ${hasDiscount ? `<td class="center">${row.discount || 0}%</td>
                                         <td class="right">${(final/row.quantity).toFixed(2)}</td>` : ''}
                        <td class="right">${final.toFixed(2)}</td>
                    </tr>`;
            });
            $("#itemsTable tbody").html(tbody);

            // Totals
            let gstRate = so.gst ? parseFloat(so.gst) : 0;
            let gstAmt = total * gstRate / 100;
            let net = total + gstAmt;

        // Determine colspan based on discount columns
let colSpanTotal = hasDiscount ? 7 : 5;

let tfoot = `
<tr>
    <td colspan="${colSpanTotal}" class="right bold">Sub Total</td>
    <td class="right" id="baseAmount">${total.toFixed(2)}</td>
</tr>
<tr>
    <td colspan="${colSpanTotal}" class="right bold">GST</td>
    <td class="right" id="gstAmount">${gstRate}% = ${gstAmt.toFixed(2)}</td>
</tr>
<tr>
    <td colspan="${colSpanTotal}" class="right bold">Net Total</td>
    <td class="right bold" id="netTotal">${net.toFixed(2)}</td>
</tr>
`;

$("#itemsTable tfoot").html(tfoot);


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