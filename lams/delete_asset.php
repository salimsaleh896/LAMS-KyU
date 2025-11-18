<?php
require_once 'includes/db_config.php';
require_once 'includes/functions.php';
session_start();
check_login(); // Security: Only logged-in users can delete

// 1. Check if the ID parameter is present and is a non-empty value
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {

    // Sanitize and get the asset ID
    $asset_id = trim($_GET["id"]);

    // 2. Prepare a DELETE statement using a prepared statement for security
    $sql = "DELETE FROM assets WHERE asset_id = ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
        // Bind the ID parameter to the statement
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        $param_id = $asset_id;

        // 3. Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            // Success: Redirect back to the assets list page with a success status
            header("location: assets.php?status=deleted");
            exit();
        } else {
            // Error handling
            // Redirect with an error status or display an error page
            header("location: assets.php?status=delete_error");
            exit();
        }
        mysqli_stmt_close($stmt);
    }
} else {
    // ID parameter missing or empty, redirect to asset list
    header("location: assets.php");
    exit();
}

mysqli_close($link);
