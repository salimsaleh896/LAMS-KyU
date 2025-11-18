<?php
// NOTE: This file assumes the main page (e.g., dashboard.php) has already called session_start().
// REMOVED redundant session_start() here to prevent conflicts.

// Helper variable to safely access the username. If the session key is missing,
// it defaults to 'Guest', which suppresses the "Undefined array key" warning.
$username_display = htmlspecialchars($_SESSION["username"] ?? 'Guest');
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
       
    <meta charset="UTF-8">
       
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>LAMS - Lab Asset Management System</title>
       
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>

<body>
        <header>
                <nav class="navbar">
                        <div class="logo">
                <img src="kyu_logo.png" alt="University Logo" style="max-height: 60px; width: auto;">
                KIRINYAGA UNIVERSITY LAMS
            </div>
                        <ul class="nav-links">
                                <?php if ($is_logged_in): // Only show internal links if logged in 
                                ?>
                                        <li><a href="dashboard.php">Dashboard</a></li>
                                        <li><a href="assets.php">Assets</a></li>
                                        <li><a href="reports.php">Reports</a></li>
                                       
                                        <li class="logout-link">
                                                <a href="logout.php">Logout (<?php echo $username_display; ?>)</a>
                                            </li>
                                    <?php else: // Show public links if not logged in 
                                    ?>
                                        <li><a href="index.php">Home</a></li>
                                        <li><a href="login.php">Login</a></li>
                                    <?php endif; ?>
                            </ul>
                    </nav>
            </header>
        <main class="container">