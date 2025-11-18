<?php
// Start session, include DB config, and functions
session_start();
require_once 'includes/db_config.php';
require_once 'includes/functions.php';

// Ensure the user is logged in (TC-SEC-003)
check_login();

$reports_err = "";

// The database link ($link) is assumed to be set up in db_config.php

// --- START OF HTML OUTPUT ---
include 'includes/header.php'; // This opens the <body> tag

// We are now inside the main content wrapper (from header.php)
?>

<h2 class="page-title">Inventory Reports</h2>

<?php
if (!empty($reports_err)) {
    echo '<div class="alert alert-danger">' . $reports_err . '</div>';
}
?>

<div class="report-section" id="value">
    <h3>3. Total Asset Value Summary</h3>
    <?php
    // SQL to calculate the sum of the 'value' column
    $sql_value = "SELECT SUM(value) AS total_value FROM assets";

    if ($result = mysqli_query($link, $sql_value)) {
        $row = mysqli_fetch_assoc($result);
        $total_value = number_format($row['total_value'] ?? 0, 2);
        echo "<p>The **TOTAL MONETARY VALUE** of all assets in the inventory is:</p>";
        echo "<h2>Ksh {$total_value}</h2>";
        mysqli_free_result($result);
    } else {
        echo "<p class='alert alert-danger'>Error calculating total value: " . mysqli_error($link) . "</p>";
    }
    ?>
</div>

<div class="report-section">
    <h3>2. Asset Count by Location (Summary Report)</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Location</th>
                <th>Asset Count</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // SQL to group assets by location and count them
            $sql_location = "SELECT l.location_name, COUNT(a.asset_id) AS asset_count 
                             FROM locations l 
                             LEFT JOIN assets a ON l.location_id = a.location_id 
                             GROUP BY l.location_name ORDER BY l.location_name";

            if ($result = mysqli_query($link, $sql_location)) {
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['location_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['asset_count']) . "</td>";
                        echo "</tr>";
                    }
                    mysqli_free_result($result);
                } else {
                    echo "<tr><td colspan='2'>No locations found in the database.</td></tr>";
                }
            } else {
                echo "<tr><td colspan='2' class='alert alert-danger'>Error loading location report: " . mysqli_error($link) . "</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<div class="report-section" id="faulty">
    <h3>1. Faulty/Needs Repair Assets (Detail Report)</h3>
    <p>List of all assets currently marked as 'Faulty' or 'Under Repair'.</p>
    <table class="data-table">
        <thead>
            <tr>
                <th>Asset Name</th>
                <th>Serial Number</th>
                <th>Status</th>
                <th>Location</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // SQL to select all assets where the status is 'Faulty' (or similar)
            $sql_faulty = "SELECT a.asset_name, a.serial_number, a.status, l.location_name
                           FROM assets a
                           LEFT JOIN locations l ON a.location_id = l.location_id
                           WHERE a.status IN ('Faulty', 'Under Repair', 'Needs Service') 
                           ORDER BY a.asset_name";

            if ($result = mysqli_query($link, $sql_faulty)) {
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['asset_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['serial_number']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['location_name']) . "</td>";
                        echo "</tr>";
                    }
                    mysqli_free_result($result);
                } else {
                    echo "<tr><td colspan='4'>**TC-RPT-004 PASSED:** No faulty assets currently in the inventory.</td></tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='alert alert-danger'>Error loading faulty assets report: " . mysqli_error($link) . "</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>


<?php
// Close the connection only if it's open
if (isset($link)) {
    mysqli_close($link);
}

include 'includes/footer.php';
