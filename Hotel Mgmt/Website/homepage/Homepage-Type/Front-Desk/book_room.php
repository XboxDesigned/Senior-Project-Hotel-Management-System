<?php
require_once '../../../inc/db_connect.php';

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
	
//Initialize error and success message
$error_message = '';
$success_message = '';
?>

<!DOCTYPE html>
<html>
<head>
	<title>Book Room</title>
	<link rel="stylesheet" type="text/css" href="../../../inc/homepage_main.css">
</head>
<header>
	<h2>Hello, <?php echo htmlspecialchars($name); ?></h2>

	<!-- Logout Button -->
	<form method="post">
		<button type="submit" name="logout" id="submit" class="logout-register-btn">LOGOUT</button><br>
	</form>

</header>
<body>
<div class="side-buttons-container">
	<div class="side-buttons-top">
	<form method="post">
        <button type="submit" name="admin_1">Home</button>
    </form>
	<br>
	
	<form method="post">
        <button type="submit" name="admin_2">Rooms</button>
    </form>
	<br>
	
	<form method="post">
        <button type="submit" name="admin_3">Cancellations</button>
    </form>
	<br>
	
	<form method="post">
        <button type="submit" name="admin_4">Guests</button>
    </form>
	</div>
	
	<div class="side-buttons-bottom">
	<form method="post">
        <button type="submit" name="admin_5">Maintenance</button>
    </form>
	<br>
	
    <form method="post">
        <button type="submit" name="admin_6">Night Audit</button>
    </form>
	<br>
	
	<form method="post">
        <button type="submit" name="admin_7">Book Room</button>
    </form>
	<br>
	
	<form method="post">
        <button type="submit" name="check_in">Check In</button>
    </form>
	<br>
	
	<form method="post">
        <button type="submit" name="admin_9">Check Out</button>
    </form>
	</div>
</div>
	<form class="book-room-form" method="post">
	<h1>BOOK ROOM</h1><br>
	<?php if (!empty($error_message)){ ?>
			<p class="error"><?php echo $error_message; ?></p>
	<?php } ?>
	<?php if (!empty($success_message)){ ?>
			<p class="success"><?php echo $success_message; ?></p>
	<?php } ?>
	
	<h3>Guest Information:</h3><br>
		<label>First Name</label>
		<input type="text" name="firstName" placeholder="Enter First Name:"><br>
		
		<label>Last Name</label>
		<input type="text" name="lastName" placeholder="Enter Last Name:"><br>
		
		<label>Email Address</label>
		<input type="username" name="emailAddress" placeholder="Enter Email Address:"><br>
		
		<label>Phone Number</label>
		<input type="tel" name="contactNum" placeholder="Enter Phone Number:"><br>
		
		<br>
		
	<h3>Booking Details:</h3><br>
		<label>Assign Room:</label>
        <select name="rooms">
          <option <?php echo ''; ?>></option>
		</select><br>
		
		<label>Number of Guests:</label>
        <select name="guests_num">
          <option <?php echo ''; ?>></option>
		</select><br>
		
		<label>Check In Date:</label>
        <select name="check_in_date">
          <option <?php echo ''; ?>></option>
		</select><br>
		
		<label>Check Out Date:</label>
        <select name="check_out_date">
          <option <?php echo ''; ?>></option>
		</select><br>
		
		<button type="submit" name="confirm_btn" class="confirm-btn">Confirm</button> 
	</form>
</body>
</html>