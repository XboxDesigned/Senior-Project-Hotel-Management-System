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

	$role = $_SESSION['user']['role'];

    switch ($role) {
        case 'front_desk':
            include_once('Homepage-Type/Front-Desk/frontdesk_homepage.php');
            break;
        case 'admin':
            include_once('Homepage-Type/Admin/admin_homepage.php');
            break;
        case 'maintenance':
            include_once('Homepage-Type/Housekeeping-Maintenance/housekeeping_homepage.php');
            break;
        default:
            echo "Invalid action!";
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
