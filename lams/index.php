<?php
// Include the database configuration file
require_once 'includes/db_config.php';
// Include the header (which starts the session)
include 'includes/header.php';

// Check if the connection link is active (for testing purposes)
if ($link) {
    $db_status = "Successfully connected to the MySQL database!";
} else {
    $db_status = "Connection Failed!";
}
?>

<h2>Welcome to the Computer Lab</h2>
<p>This is the main landing page. Only basic information should be visible here.</p>

<div class="status-box">
    Database Status: <strong><?php echo $db_status; ?></strong>
</div>

<?php
include 'includes/footer.php';
// Close the connection when the page script finishes executing
mysqli_close($link);
?>