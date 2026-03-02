<?php
// Hostname: sql205.infinityfree.com
define('DB_SERVER', 'sql205.infinityfree.com');

// Username: if0_40358283
define('DB_USERNAME', 'if0_40358283');
define('DB_PASSWORD', 'Tingole9');

// Database Name: if0_40358283_lams_db
define('DB_NAME', 'if0_40358283_lams_db');



$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($link === false) {
    // This error will be displayed if the connection fails (e.g., wrong password, wrong hostname).
    die("ERROR: Could not connect to the live database. Check DB_PASSWORD and DB_SERVER. " . mysqli_connect_error());
}

// Set character set to UTF-8
mysqli_set_charset($link, "utf8");



