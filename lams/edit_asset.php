<?php
session_start();
require_once 'includes/db_config.php';
require_once 'includes/functions.php';

// Security check
check_login();

// Variables for asset data and error handling
$asset_id = 0;
$asset_name = $serial_number = $status = $purchase_date = $notes = '';
$category_id = $location_id = null;
$value = 0.00;

$asset_name_err = $serial_number_err = $status_err = $general_err = '';
$success_msg = '';

// --- FETCH DROPDOWN DATA (Using your functions) ---
$categories = fetch_dropdown_data($link, 'categories', 'category_id', 'category_name');
$locations = fetch_dropdown_data($link, 'locations', 'location_id', 'location_name');


// --- 2. GET ASSET ID AND LOAD CURRENT DATA (via GET or POST) ---
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    // Page loaded via link (GET request)
    $asset_id = trim($_GET["id"]);
    $param_id = $asset_id; // Set param_id for initial load
} elseif (isset($_POST["asset_id"]) && !empty(trim($_POST["asset_id"]))) {
    // Page reloaded after POST attempt
    $asset_id = trim($_POST["asset_id"]);
    $param_id = $asset_id; // Set param_id for POST
} else {
    // ID parameter not set in URL or POST
    header("location: assets.php");
    exit();
}


// --- 3. PROCESS UPDATE FORM SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 3.1. Retrieve and Validate POST Data

    // Validate Asset Name
    if (empty(trim($_POST["asset_name"]))) {
        $asset_name_err = "Asset name is required.";
    } else {
        $asset_name = trim($_POST["asset_name"]);
    }

    // Validate Serial Number
    if (empty(trim($_POST["serial_number"]))) {
        $serial_number_err = "Serial number is required.";
    } else {
        $serial_number = trim($_POST["serial_number"]);
    }

    // Validate Status 
    $status = $_POST['status'] ?? '';
    if (empty($status) || !in_array($status, ['Working', 'Faulty', 'In Repair', 'Retired'])) {
        $status_err = "Status is required and must be valid.";
    }

    // Set other variables safely
    $category_id = $_POST['category_id'] ?? null;
    $location_id = $_POST['location_id'] ?? null;
    $purchase_date = $_POST['purchase_date'] ?? null;
    $value = $_POST['value'] ?? 0.00;
    $notes = trim($_POST['notes'] ?? '');


    // 3.2. Check for errors and proceed with database UPDATE
    if (empty($asset_name_err) && empty($serial_number_err) && empty($status_err)) {

        $sql = "UPDATE assets SET 
                    serial_number=?, asset_name=?, category_id=?, location_id=?, 
                    status=?, purchase_date=?, value=?, notes=?, last_updated=NOW()
                WHERE asset_id=?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param(
                $stmt,
                "ssiissdsi",
                $serial_number,
                $asset_name,
                $category_id,
                $location_id,
                $status,
                $purchase_date,
                $value,
                $notes,
                $asset_id
            );

            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Asset **{$asset_name}** updated successfully! (TC-CRUD-002)";
            } else {
                $general_err = "Error updating asset. Check database connection or serial number duplication.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $general_err = "Database preparation error. Check your SQL query.";
        }
    }
}


// --- 4. RELOAD DATA AFTER POST OR FOR INITIAL GET REQUEST ---
// Only runs if $asset_id is valid
if (!empty($asset_id)) {
    $sql = "SELECT * FROM assets WHERE asset_id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        $param_id = $asset_id;

        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);
                // Populate variables with existing data (or data submitted via POST)
                if ($_SERVER["REQUEST_METHOD"] != "POST" || !empty($general_err)) {
                    $asset_name = $row['asset_name'];
                    $serial_number = $row['serial_number'];
                    $category_id = $row['category_id'];
                    $location_id = $row['location_id'];
                    $status = $row['status'];
                    $purchase_date = $row['purchase_date'];
                    $value = $row['value'];
                    $notes = $row['notes'];
                }
            } else {
                // Asset ID not found
                header("location: assets.php");
                exit();
            }
        } else {
            $general_err = "Oops! Something went wrong fetching data.";
        }
        mysqli_stmt_close($stmt);
    }
}


// --- 5. DISPLAY HTML FORM (Output) ---
include 'includes/header.php';
?>

<div class="form-container">
    <h2>Edit Asset #<?php echo htmlspecialchars($asset_id); ?></h2>

    <?php
    // Display general errors or success messages
    if (!empty($success_msg)) {
        echo '<div class="alert alert-success">' . $success_msg . '</div>';
    } elseif (!empty($general_err)) {
        echo '<div class="alert alert-danger">' . $general_err . '</div>';
    }
    ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $asset_id; ?>" method="post">
        <input type="hidden" name="asset_id" value="<?php echo htmlspecialchars($asset_id); ?>">

        <label for="asset_name">Asset Name <span class="required">*</span></label>
        <input type="text" name="asset_name" required
            value="<?php echo htmlspecialchars($asset_name); ?>">
        <span class="error-msg"><?php echo $asset_name_err; ?></span>

        <label for="serial_number">Serial Number <span class="required">*</span></label>
        <input type="text" name="serial_number" required
            value="<?php echo htmlspecialchars($serial_number); ?>">
        <span class="error-msg"><?php echo $serial_number_err; ?></span>

        <label for="category_id">Category <span class="required">*</span></label>
        <select name="category_id" required>
            <?php echo generate_options($categories, $category_id); ?>
        </select>

        <label for="location_id">Location <span class="required">*</span></label>
        <select name="location_id" required>
            <?php echo generate_options($locations, $location_id); ?>
        </select>

        <label for="status">Status <span class="required">*</span></label>
        <select name="status" required>
            <option value="">-- Select Status --</option>
            <?php
            $statuses = ['Working', 'Faulty', 'In Repair', 'Retired'];
            foreach ($statuses as $s):
            ?>
                <option value="<?php echo $s; ?>" <?php echo ($status == $s) ? 'selected' : ''; ?>><?php echo $s; ?></option>
            <?php endforeach; ?>
        </select>
        <span class="error-msg"><?php echo $status_err; ?></span>

        <label for="purchase_date">Purchase Date</label>
        <input type="date" name="purchase_date" value="<?php echo htmlspecialchars($purchase_date); ?>">

        <label for="value">Value (Ksh) <span class="required">*</span></label>
        <input type="number" name="value" step="0.01" required min="1"
            value="<?php echo htmlspecialchars($value); ?>">

        <label for="notes">Notes</label>
        <textarea name="notes"><?php echo htmlspecialchars($notes); ?></textarea>

        <input type="submit" class="btn-primary" value="Update Asset">
        <a href="assets.php" class="btn-secondary">Cancel</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>