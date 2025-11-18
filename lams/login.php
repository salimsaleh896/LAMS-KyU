<?php
// Start session management
session_start();

// Check if the user is already logged in, if so, redirect them to the dashboard
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: dashboard.php");
    exit;
}

// Include database configuration
require_once 'includes/db_config.php';

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if username is empty
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate credentials and ensure database connection is available
    if (empty($username_err) && empty($password_err) && isset($link)) {

        // Prepare a select statement
        $sql = "SELECT user_id, username, password, role FROM users WHERE username = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);

            // Set parameters
            $param_username = $username;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Store result
                mysqli_stmt_store_result($stmt);

                // Check if username exists, if yes then verify password
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $role);
                    if (mysqli_stmt_fetch($stmt)) {
                        // TC-SEC-002: Verify the password against the stored hash
                        if (password_verify($password, $hashed_password)) {

                            // Password is correct, so start a new session
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $id;
                            mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $role);

                            // Close statement and connection before redirecting
                            mysqli_stmt_close($stmt);
                            mysqli_close($link);

                            // Redirect user to dashboard page
                            header("location: dashboard.php");
                            exit; // IMPORTANT: Always exit after a header redirect
                        } else {
                            // Password is not valid, display a generic error
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else {
                    // Username doesn't exist, display a generic error
                    $login_err = "Invalid username or password.";
                }
            } else {
                // Database query execution failed
                $login_err = "Oops! Something went wrong. Please try again later. (DB Execute Error)";
            }

            // Close statement if it was opened
            if (isset($stmt)) {
                mysqli_stmt_close($stmt);
            }
        } else {
            // Database preparation failed
            $login_err = "Oops! Something went wrong. Please try again later. (DB Prepare Error)";
        }
    }

    // Close connection if it's open, if we reached the end of the POST block
    if (isset($link)) {
        mysqli_close($link);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login - LAMS</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Minimal CSS for a clean login page - you can expand this in style.css */
        body {
            font: 14px sans-serif;
        }

        .wrapper {
            width: 360px;
            padding: 20px;
            margin: 50px auto;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: block;
            width: 100%;
            text-align: center;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .error-msg {
            color: red;
            font-size: 0.9em;
            display: block;
            margin-top: 5px;
        }

        .login-title {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <h2 class="login-title">LAMS User Login</h2>
        <p>Please fill in your credentials to log in.</p>

        <?php
        if (!empty($login_err)) {
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>">
                <span class="error-msg"><?php echo $username_err; ?></span>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password">
                <span class="error-msg"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn-primary" value="Login">
            </div>
        </form>
    </div>
</body>

</html>