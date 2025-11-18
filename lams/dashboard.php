<?php
session_start();
require_once 'includes/db_config.php';
require_once 'includes/functions.php';

// Call the security function: if not logged in, user is redirected to login.php
check_login();

// --- PHP LOGIC: Fetch KPI Data ---

// 1. Total Assets Count
$sql_total = "SELECT COUNT(asset_id) AS total_assets FROM assets";
$result_total = mysqli_query($link, $sql_total);
$data_total = mysqli_fetch_assoc($result_total);
$total_assets = $data_total['total_assets'] ?? 0;

// 2. Faulty Assets Count
$sql_faulty = "SELECT COUNT(asset_id) AS faulty_assets FROM assets WHERE status IN ('Faulty', 'In Repair')";
$result_faulty = mysqli_query($link, $sql_faulty);
$data_faulty = mysqli_fetch_assoc($result_faulty);
$faulty_assets = $data_faulty['faulty_assets'] ?? 0;

// 3. Assets Assigned
$sql_assigned = "SELECT COUNT(asset_id) AS assigned_assets FROM assets WHERE location_id IS NOT NULL AND location_id != 0";
$result_assigned = mysqli_query($link, $sql_assigned);
$data_assigned = mysqli_fetch_assoc($result_assigned);
$assigned_assets = $data_assigned['assigned_assets'] ?? 0;

// 4. Total Monetary Value
$sql_value = "SELECT SUM(value) AS total_value FROM assets";
$result_value = mysqli_query($link, $sql_value);
$data_value = mysqli_fetch_assoc($result_value);
// Format value to currency with commas (no decimals)
$total_value = number_format($data_value['total_value'] ?? 0, 0);

// --- END OF PHP LOGIC ---

include 'includes/header.php';

// --- START OF DISPLAY (HTML & PHP) ---

// FIX: Safely retrieve session data (fixes the 'Undefined array key' warnings)
$displayed_username = htmlspecialchars($_SESSION["username"] ?? 'ADMIN');
$displayed_role = htmlspecialchars($_SESSION["role"] ?? 'Manager');

echo '<div style="padding: 20px;">';

// Line 1: Welcome Message
echo '<h2>WELCOME TO YOUR DASHBOARD, ' . $displayed_username . '👋</h2>';

// Line 2: Role Message
echo '<p style="font-size: 1.1em; color: #555; margin-bottom: 25px;">';
echo 'You are logged in as an KIRINYAGA UNIVERSITY ICT MANAGER, ';
echo '<strong>' . $displayed_role . '</strong>.';
echo '</p>';


// --- KPI GRID (Now with clickable links) ---
echo '<div class="kpi-grid">
    
    <a href="assets.php" class="kpi-link">
        <div class="kpi-card total">
            <h3>Total Assets</h3>
            <p class="kpi-value">' . $total_assets . '</p>
        </div>
    </a>
    
    <a href="assets.php?status=assigned" class="kpi-link">
        <div class="kpi-card assigned">
            <h3>Assets Assigned</h3>
            <p class="kpi-value">' . $assigned_assets . '</p>
        </div>
    </a>

    <a href="reports.php#faulty" class="kpi-link">
        <div class="kpi-card faulty">
            <h3>Faulty Assets</h3>
            <p class="kpi-value">' . $faulty_assets . '</p>
        </div>
    </a>

    <a href="reports.php#value" class="kpi-link">
        <div class="kpi-card value">
            <h3>Inventory Value</h3>
            <p class="kpi-value">Ksh ' . $total_value . '</p>
        </div>
    </a>
</div>';
// --- END KPI GRID ---

include 'includes/footer.php';
