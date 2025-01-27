<?php
// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: ../login.php'); // Redirect to the login if not logged in
    exit();
}

// Check if the logout button is clicked
if (isset($_POST['logout'])) {
    // Destroy the session
    session_destroy();

    // Redirect the user to the login page
    header("Location: ../login.php");
    exit();
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

    <!-- Logout Button -->
    <form method="post">
        <button type="submit" name="logout">Logout</button>
    </form>

    <br>
</body>
</html>
