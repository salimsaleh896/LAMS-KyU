<?php
session_start();

require_once 'includes/db_config.php';
require_once 'includes/functions.php'; // Contains check_login(), fetch_dropdown_data(), and generate_options()

// Security check: redirects if not logged in
check_login();

// Variables to store form data and errors
$asset_name = $serial_number = $purchase_date = $value = $notes = '';
$status = ''; // Initialize $status to prevent undefined variable warnings
$category_id = $location_id = null;
$asset_name_err = $serial_number_err = $status_err = $general_err = '';
$success_msg = '';


$categories_data = fetch_dropdown_data($link, 'categories', 'category_id', 'category_name');
$locations_data = fetch_dropdown_data($link, 'locations', 'location_id', 'location_name');


// --- 1. PROCESS FORM SUBMISSION (Server-Side Logic) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1.1. Validate Asset Name
    if (empty(trim($_POST["asset_name"]))) {
        $asset_name_err = "Asset name is required.";
    } else {
        $asset_name = trim($_POST["asset_name"]);
    }

    // 1.2. Validate Serial Number (Basic check and setting variable)
    if (empty(trim($_POST["serial_number"]))) {
        $serial_number_err = "Serial number is required.";
    } else {
        $serial_number = trim($_POST["serial_number"]);
    }

    // 1.3. Set and Validate Dropdown Selections
    $status = $_POST['status'] ?? '';
    $category_id = $_POST['category_id'] ?? null;
    $location_id = $_POST['location_id'] ?? null;

    if (!in_array($status, ['Working', 'Faulty', 'In Repair', 'Retired'])) {
        $status_err = "Invalid status selected.";
    }

    // Set other variables safely
    $purchase_date = $_POST['purchase_date'] ?? null;
    $value = $_POST['value'] ?? 0.00;
    $notes = trim($_POST['notes'] ?? '');


    // 1.4. Check for errors and proceed with database INSERT
    if (empty($asset_name_err) && empty($serial_number_err) && empty($status_err)) {

        $sql = "INSERT INTO assets (serial_number, asset_name, category_id, location_id, status, purchase_date, value, notes, added_by_user_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind parameters: s=string, i=integer, d=double
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
                $_SESSION['user_id']
            );

            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Asset **{$asset_name}** added successfully to inventory!";
                // Clear variables to reset the form:
                $asset_name = $serial_number = $purchase_date = $value = $notes = $status = '';
                $category_id = $location_id = null;
            } else {
                $general_err = "Error adding asset. Check if the Serial Number already exists or database connection.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $general_err = "Database preparation error. Check your SQL query.";
        }
    }
}

// --- 2. DISPLAY HTML FORM (Output) ---
include 'includes/header.php';
?>

<div class="form-container">
    <h2>Add New Asset</h2>

    <?php
    if (!empty($success_msg)) {
        echo '<div class="alert alert-success">' . $success_msg . '</div>';
    } elseif (!empty($general_err)) {
        echo '<div class="alert alert-danger">' . $general_err . '</div>';
    }
    ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

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
            <option value="">-- Select Category --</option>
            <?php echo generate_options($categories_data, $category_id); ?>
        </select>
        <span class="error-msg"><?php echo $category_id_err ?? ''; ?></span>

        <label for="location_id">Location <span class="required">*</span></label>
        <select name="location_id" required>
            <option value="">-- Select Location --</option>
            <?php echo generate_options($locations_data, $location_id); ?>
        </select>
        <span class="error-msg"><?php echo $location_id_err ?? ''; ?></span>

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
        <textarea name="notes" rows="4"><?php echo htmlspecialchars($notes); ?></textarea>

        <div class="form-actions">
            <input type="submit" class="btn-primary" value="Add Asset">
            <a href="assets.php" class="btn-secondary">Cancel</a>
        </div>

    </form>
</div>
<?php include 'includes/footer.php'; ?>