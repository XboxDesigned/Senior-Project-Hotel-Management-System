<?php
// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) 
{
    session_start();
}

if (!isset($_SESSION['user'])) 
{
    header('Location: ../login.php'); // Redirect to the login if not logged in
    exit();
}

// Check if the logout button is clicked
if (isset($_POST['logout'])) 
{
    // Destroy the session
    session_destroy();

    // Redirect the user to the login page
    header("Location: ../login.php");
    exit();
}
	$name = $_SESSION['user']['username'];
	$role = $_SESSION['user']['role'];
?>

<!DOCTYPE html>
<html>
<head>
        <style>
 			td {
       			padding: 10px 20px; /* Adjust space inside each cell, temporary */
                width: 1200px;
   			}
		</style>
	<header>
		<title>Home Page</title>
		<link rel="stylesheet" type="text/css" href="../inc/homepage_main.css">
	</head>
	<body>
		<h2>Hello, <?php echo htmlspecialchars($name); ?></h2>

		<!-- Logout Button -->
		<form method="post">
			<button type="submit" name="logout" id="submit" class="logout-register-btn">LOGOUT</button><br>
		</form>

	</header>
	
	<h1>Home</h1>
	
    <br>
</body>
</html>

<?php

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