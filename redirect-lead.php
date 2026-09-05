<?php
session_start(); // optional, if you want to check login
if (isset($_GET['leadId'])) {
    $leadId = intval($_GET['leadId']);
    echo "
    <form id='redirectForm' action='lead-replay.php' method='post'>
        <input type='hidden' name='leadId' value='$leadId'>
    </form>
    <script>document.getElementById('redirectForm').submit();</script>
    ";
} else {
    echo "Invalid lead ID.";
}
?>
