<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';
?>
<head>
    <title>Assigned Leads</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
</head>

<?php include 'layouts/body.php'; ?>
<div id="layout-wrapper">
<?php include 'layouts/menu.php'; ?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <h4 class="mb-4">Assigned Leads</h4>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Sr No.</th>
                            <th>Company Name</th>
                            <th>Lead Title</th>
                            <th>Priority</th>
                        

                            <!-- <th>Assigned to</th> -->
                               <th>Amount</th>
                                  <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data populated via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php include 'layouts/footer.php'; ?>
</div>
</div>

<?php include 'layouts/right-sidebar.php'; ?>
<?php include 'layouts/vendor-scripts.php'; ?>
<script src="assets/js/app.js"></script>

<script>
function assignedLeads() {
  $.ajax({
    url: 'api.php', 
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({
      action: 'assignedLeads',
      user_id: <?php echo isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0; ?>
    }),
    success: function(response) {
      console.log('Assigned Leads Response:', response);

      if (response.status === 'success') {
        const leads = response.data;
        let rows = '';

        leads.forEach(function(lead, index) {
          let totalAmount = 0;
          lead.products.forEach(prod => {
            totalAmount += parseFloat(prod.total_amount) || 0;
          });

          rows += `
            <tr>
              <td>${index + 1}</td>
              <td onclick="triggerReplay(${lead.iLead_id})" style="cursor:pointer; color:blue; text-decoration:underline;">
                ${lead.sCompany_name}
              </td>
              <td onclick="triggerReplay(${lead.iLead_id})" style="cursor:pointer; color:blue; text-decoration:underline;">
                ${lead.sLead_name}
              </td>
              <td onclick="triggerReplay(${lead.iLead_id})" style="cursor:pointer; color:blue; text-decoration:underline;">
                ${lead.sPrioritylevel || 'N/A'}
              </td>
             
              <td onclick="triggerReplay(${lead.iLead_id})" style="cursor:pointer; color:blue; text-decoration:underline;">
                ${totalAmount.toFixed(2)}
              </td>
              <td onclick="triggerReplay(${lead.iLead_id})" style="cursor:pointer; color:blue; text-decoration:underline;">
  ${lead.status || 'N/A'}
</td>

            </tr>`;
        });

        if ($.fn.DataTable.isDataTable('#datatable')) {
          $('#datatable').DataTable().destroy();
        }

        $('#datatable tbody').html(rows);

        $('#datatable').DataTable({
          paging: true,
          pageLength: 10,
          searching: true,
          ordering: true,
          order: [[0, 'asc']],
          responsive: true
        });

      } else {
        $('#datatable tbody').html('<tr><td colspan="5">No assigned leads found</td></tr>');
      }
    },
    error: function(xhr, status, error) {
      console.error('Error fetching assigned leads:', error);
    }
  });
}

$(document).ready(function() {
  assignedLeads();
});

function triggerReplay(leadId) {
    let form = document.createElement('form');
    form.method = 'post';
    form.action = 'lead-replay.php';
    let input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'leadId';
    input.value = leadId;
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}
</script>
</body>
</html>
