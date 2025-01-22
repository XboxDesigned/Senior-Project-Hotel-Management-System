<?php
// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Action handling logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'front_desk':
            include_once('user_types/frontdesk_login.php');
            break;
        case 'admin':
            include_once('user_types/admin_login.php');
            break;
        case 'housekeeping':
            include_once('user_types/housekeeping_login.php');
            break;
        default:
            echo "Invalid action!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home Page</title>
    <link rel="stylesheet" type="text/css" href="styles.css"> 
</head>
<body>
    <h2>Welcome back!</h2>
    <h3>What would you like to do today?</h3>
    <br>

    <button><a class="login-logout" href="user_types/logout.php">Logout</a></button>
    <br>
</body>
</html>
