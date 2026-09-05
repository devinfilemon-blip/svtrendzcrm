<?php

/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
// define('DB_SERVER', '127.0.0.1');
// define('DB_USERNAME', 'aronerte_samconsultancy');
// define('DB_PASSWORD', 'Developer@123');
// define('DB_NAME', 'aronerte_dbsamconsultancy');

// define('DB_SERVER', 'localhost');
// define('DB_USERNAME', 'u273469757_inficrmuser');
// define('DB_PASSWORD', 'Inficrm123');
// define('DB_NAME', 'u273469757_inficrm');



define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'svtrendz_infilemon');


// // live 
// define('DB_SERVER', 'localhost');                                                                                           

define('APP_NAME', 'S V Trendz');
define('APP_TAGLINE', 'Industrial Solutions');
define('APP_LOGO', 'assets/images/svtrendz-logo.png');
define('APP_ICON', 'assets/images/svtrendz-logo-mark.png');



/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}




