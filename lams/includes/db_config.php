<?php
// Hostname: sql205.infinityfree.com
define('DB_SERVER', 'sql205.infinityfree.com');

// Username: if0_40358283
define('DB_USERNAME', 'if0_40358283');

// Password: This must be your main InfinityFree account password (vPanel Password).
// NOTE: InfinityFree uses the same password for the database as your main account.
define('DB_PASSWORD', 'Tingole9');

// Database Name: if0_40358283_lams_db
define('DB_NAME', 'if0_40358283_lams_db');


/* Attempt to connect to MySQL database */
// NOTE: We remove the port (3307) here, as it's only needed for your local XAMPP setup.
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($link === false) {
    // This error will be displayed if the connection fails (e.g., wrong password, wrong hostname).
    die("ERROR: Could not connect to the live database. Check DB_PASSWORD and DB_SERVER. " . mysqli_connect_error());
}

// Set character set to UTF-8
mysqli_set_charset($link, "utf8");

// The $link variable is now available for use in all files that include this script.
