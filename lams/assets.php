<?php
require_once 'includes/db_config.php';
require_once 'includes/functions.php';
session_start();
check_login(); // Security check


// SQL query to retrieve all assets with linked category and location names
$sql = "SELECT 
            a.asset_id, a.asset_name, a.serial_number, a.status, a.last_updated,
            c.category_name, 
            l.location_name
        FROM assets a
        JOIN categories c ON a.category_id = c.category_id
        JOIN locations l ON a.location_id = l.location_id
        ORDER BY a.asset_id DESC"; // Show latest assets first

$result = mysqli_query($link, $sql);

include 'includes/header.php';
?>

<div class="content-header">
    <h2>Computer Lab Inventory List</h2>
    <a href="add_asset.php" class="btn-add">➕ Add New Asset</a>
</div>

<?php if (mysqli_num_rows($result) > 0): ?>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Asset Name</th>
                    <th>Serial No.</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row['asset_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['asset_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['serial_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['location_name']); ?></td>
                        <td class="status-<?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>"><?php echo $row['status']; ?></td>
                        <td><?php echo date("Y-m-d", strtotime($row['last_updated'])); ?></td>
                        <td>
                            <a href="edit_asset.php?id=<?php echo $row['asset_id']; ?>" class="btn-sm btn-edit">Edit</a>
                            <a href="delete_asset.php?id=<?php echo $row['asset_id']; ?>" class="btn-sm btn-delete"
                                onclick="return confirm('Are you sure you want to delete this asset?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

<?php else: ?>
    <p class="alert alert-info">No assets found in the inventory. Start by adding one!</p>
<?php endif; ?>

<?php
mysqli_close($link);
include 'includes/footer.php';
?>