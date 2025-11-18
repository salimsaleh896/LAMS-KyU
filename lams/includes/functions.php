<?php
// ===========================================
// CORE HELPER FUNCTIONS (Dropdowns & Security)
// ===========================================

// Function to check if a user is logged in
function check_login()
{
    // Check if the session variable 'loggedin' is NOT set OR is NOT true
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

        // User is not logged in, redirect them immediately to the login page
        header("location: login.php");
        exit; // Essential to stop script execution
    }
}

// Function to fetch data for dropdowns (Categories and Locations)
function fetch_dropdown_data($link, $table, $id_col, $name_col)
{
    $data = [];
    $sql = "SELECT {$id_col}, {$name_col} FROM {$table} ORDER BY {$name_col}";
    if ($result = mysqli_query($link, $sql)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[$row[$id_col]] = $row[$name_col];
        }
        mysqli_free_result($result);
    }
    return $data;
}

// Function to generate HTML options for a select box
function generate_options($data_array, $selected_value = null)
{
    $options = "<option value=''>-- Select --</option>";
    foreach ($data_array as $id => $name) {
        $selected = ($id == $selected_value) ? 'selected' : '';
        $options .= "<option value='{$id}' {$selected}>" . htmlspecialchars($name) . "</option>";
    }
    return $options;
}


// =================================
// REPORT FUNCTIONS
// =================================

function generate_report_summary($link)
{
    echo '<h3>Report 1: Asset Count by Location and Status</h3>';

    $sql = "SELECT
                l.location_name,
                a.status,
                COUNT(a.asset_id) AS total_count
            FROM assets a
            JOIN locations l ON a.location_id = l.location_id
            GROUP BY l.location_name, a.status
            ORDER BY l.location_name, a.status";

    $result = mysqli_query($link, $sql);

    if (mysqli_num_rows($result) > 0) {
        echo '<div class="table-responsive"><table>';
        echo '<thead><tr><th>Location</th><th>Status</th><th>Total Count</th></tr></thead><tbody>';
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['location_name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['status']) . '</td>';
            echo '<td><strong>' . $row['total_count'] . '</strong></td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';
    } else {
        echo '<p>No data available for this report.</p>';
    }
}

function generate_report_faulty($link)
{
    echo '<h3>Report 2: Detailed List of Faulty Assets</h3>';

    $sql = "SELECT
                a.asset_name,
                a.serial_number,
                c.category_name,
                l.location_name,
                a.notes,
                a.last_updated
            FROM assets a
            JOIN categories c ON a.category_id = c.category_id
            JOIN locations l ON a.location_id = l.location_id
            WHERE a.status = 'Faulty'
            ORDER BY a.last_updated ASC";

    $result = mysqli_query($link, $sql);

    if (mysqli_num_rows($result) > 0) {
        echo '<div class="table-responsive"><table>';
        echo '<thead><tr><th>Asset Name</th><th>Serial No.</th><th>Category</th><th>Location</th><th>Last Noted</th><th>Notes</th></tr></thead><tbody>';

        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['asset_name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['serial_number']) . '</td>';
            echo '<td>' . htmlspecialchars($row['category_name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['location_name']) . '</td>';
            echo '<td>' . date("Y-m-d", strtotime($row['last_updated'])) . '</td>';
            echo '<td>' . htmlspecialchars($row['notes']) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';
    } else {
        echo '<p class="alert alert-success">✅ Excellent! No assets currently flagged as faulty.</p>';
    }
}

function generate_report_value($link)
{
    echo '<h3>Report 3: Total Asset Value by Category (Ksh)</h3>';

    $sql = "SELECT
                c.category_name,
                SUM(a.value) AS total_value,
                COUNT(a.asset_id) AS total_items
            FROM assets a
            JOIN categories c ON a.category_id = c.category_id
            JOIN locations l ON a.location_id = l.location_id
            GROUP BY c.category_name
            ORDER BY total_value DESC";

    $result = mysqli_query($link, $sql);
    $grand_total = 0;

    if (mysqli_num_rows($result) > 0) {
        echo '<div class="table-responsive"><table>';
        echo '<thead><tr><th>Category</th><th>Total Items</th><th>Total Value (Ksh)</th></tr></thead><tbody>';

        while ($row = mysqli_fetch_assoc($result)) {
            $grand_total += $row['total_value'];
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['category_name']) . '</td>';
            echo '<td>' . $row['total_items'] . '</td>';
            echo '<td>' . number_format($row['total_value'], 2) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '<tfoot><tr>';
        echo '<td colspan="2"><strong>GRAND TOTAL INVENTORY VALUE</strong></td>';
        echo '<td><strong>' . number_format($grand_total, 2) . '</strong></td>';
        echo '</tr></tfoot>';
        echo '</table></div>';
    } else {
        echo '<p>No asset value data available.</p>';
    }
}
