<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';

?>

<head>
    
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <title>Dashboard</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>

<?php include 'layouts/body.php'; ?>
<style>
a:hover .card {
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.15);
    transform: scale(1.02);
}

   .card {
    background-color: #ffffff;
    border: 1px solid #ddd;
    border-radius: 10px;
    transition: 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    text-align: center;
    padding: 10px 0px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* On hover */
.card:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

/* Card header text */
.card-title {
    font-size: 18px;
    font-weight: 500;
    color: #333;
    margin-bottom: 10px;
}

/* Card number/value text */
.card-text {
    font-size: 28px;
    font-weight: bold;
    color: #0080cd;
    margin: 0;
}

/* Specific section card height */
.card.large-card {
    min-height: 150px;
}
/* .card-body{
     margin : 0px;
       } */
</style>
<!-- Begin page -->
<div id="layout-wrapper">

    <?php include 'layouts/menu.php'; ?>



    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <div class="main-content" >

        <div class="page-content">
            <div class="container-fluid">
                
                    <div class="container" >
                        <h2><center>Welcome <?php echo $_SESSION['username']; ?></center></h2>
                      <br>
                      <br>
                      <br>
                    <div class="row">
              <div class="row mb-4">
 <div class="col-md-4" onclick="window.location.href='list-reminder_users.php'" style="cursor: pointer;">
    <div class="card large-card">
        <div class="card-body">
            <h6 class="card-title">Today's Reminders</h6>
            <p class="card-text" id="reminderCount">0</p>
        </div>
    </div>
</div>

  <div class="col-md-4" onclick="window.location.href='list-followups_users.php'" style="cursor: pointer;">
    <div class="card large-card">
        <div class="card-body">
            <h6 class="card-title">Today's Follow Ups</h6>
            <p class="card-text" id="followupCount">0</p>
        </div>
    </div>
</div>

<div class="col-md-4">
    <a href="assigned-leads.php" style="text-decoration: none; color: inherit;">
        <div class="card large-card">
            <div class="card-body">
                <h6 class="card-title">Assigned Leads</h6>
                <p class="card-text" id="assignedCount">0</p>
            </div>
        </div>
    </a>
</div>


</div>
<center><h2>Lead Status</h2></center>

<div class="row" id="statusCardsRow">
    <!-- Status cards will be appended here by JavaScript -->
</div>

                    </div>

            </div>
        </div>

        <?php 

        ?>



        <div class="modal fade"  id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <p class="modal-title" id="myLargeModalLabel">Load Cell Due Date</p>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="details" class="row">
                   
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
    
    <!-- ============================================================== -->

        <?php include 'layouts/footer.php'; ?>
    <!-- end main content-->
    </div>
</div>
<!-- END layout-wrapper -->

<!-- Right Sidebar -->
<?php include 'layouts/right-sidebar.php'; ?>
<!-- /Right-bar -->

<!-- JAVASCRIPT -->
<?php include 'layouts/vendor-scripts.php'; ?>

<!-- apexcharts -->
<script src="assets/libs/apexcharts/apexcharts.min.js"></script>
<!-- <script src="assets/js/pages/dashboard.init.js"></script> -->

<!-- App js -->


<script src="assets/js/app.js"></script>

</body>

<script>
function checkTokenStatus() {
    $.ajax({
        url: 'check_token.php', 
        method: 'GET',
        success: function(response) {
            if (response.status === 'error') {
              
                alert(response.message); 
                window.location.href = 'auth-login.php'; 
            } else {
             
                console.log(response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error checking token:', error);
            window.location.href = 'auth-login.php';
        }
    });
}


checkTokenStatus();
</script>

<script>
$(document).ready(function () {
    $.ajax({
        url: 'dashboard_data.php',
        type: 'GET',
        dataType: 'json',
        success: function (res) {
            if (res.status === 'success') {
                $('#reminderCount').text(res.reminders);
                $('#followupCount').text(res.followups);
                $('#assignedCount').text(res.assigned);
            } else {
                console.error(res.message);
            }
        },
        error: function (xhr, status, error) {
            console.error("API error:", error);
        }
    });
});
</script>
<script>
$(function () {

    /* 1.  Load reminder / follow‑up / assigned counts */
    $.getJSON('dashboard_data.php').done(res=>{
        if(res.status==='success'){
            $('#reminderCount').text(res.reminders);
            $('#followupCount').text(res.followups);
            $('#assignedCount').text(res.assigned);
        }
    });

    /* 2.  Build status cards */
    const $row = $('#statusCardsRow').empty();

    $.getJSON('lead_status_summary.php')
      .done(res=>{
          if(res.status!=='success'){console.error(res.message);return;}
          res.data.forEach(item=>{
              $row.append(`
                  <div class="col-md-4 mb-4" style="cursor:pointer"
                       onclick="location.href='list-leads-by-status.php?status=${item.status_id}'">
                      <div class="card large-card">
                          <div class="card-body">
                              <h6 class="card-title">${item.status}</h6>
                              <p  class="card-text">${item.count}</p>
                          </div>
                      </div>
                  </div>
              `);
          });
      })
    .fail((jqxhr, textStatus, errorThrown) => {
    console.error('AJAX error →', textStatus, errorThrown);
    console.error('Raw response →', jqxhr.responseText);   // shows PHP error HTML
});

});
</script>

</html>

