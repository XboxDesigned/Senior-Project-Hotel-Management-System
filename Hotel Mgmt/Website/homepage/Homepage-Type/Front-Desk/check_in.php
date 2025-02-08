<?php
require_once '../../../inc/db_connect.php';

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) 
{
    session_start();
}

if (!isset($_SESSION['user'])) 
{
    header('Location: ../../../login.php'); // Redirect to the login if not logged in
    exit();
}

// Check if the logout button is clicked
if (isset($_POST['logout'])) 
{
    // Destroy the session
    session_destroy();

    // Redirect the user to the login page
    header('Location: ../../../login.php');
    exit();
}
	$name = $_SESSION['user']['username'];
	$role = $_SESSION['user']['role'];
	
//Initialize error and success message
$error_message = '';
$success_message = '';

if (isset($_POST['checkIn'])) {
    //Get guest info from the form
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $emailAddress = $_POST['emailAddress'];
    $contactNum = $_POST['contactNum'];
	
	//Check for empty fields
    if (empty($firstName || $lastName || $emailAddress || $contactNum)) {
        $error_message = 'Please provide more info.';
    }
	
	// Check if guest already exists
	if (empty($error_msg)) {
			
        $statement = $db->prepare('SELECT guest_id FROM guests WHERE first_name = :firstName
																 AND last_name = :lastName
																 AND contact_num = :contactNum
																 AND email_address = :emailAddress');
		$statement->bindValue(':firstName', $firstName);
		$statement->bindValue(':lastName', $lastName);
		$statement->bindValue(':contactNum', $contactNum);
		$statement->bindValue(':emailAddress', $emailAddress);
        $statement->execute();
		
			if ($statement->fetch()) {
				$error_message = 'Guest already checked in.';
			}
	}
	
	if (empty($error_message)) {

        //SQL query to insert the new guest into the database
        $query = 'INSERT INTO guests
                 (first_name, last_name, contact_num, email_address)
              VALUES
                 (:firstName, :lastName, :contactNum, :emailAddress)';
				 
		$statement = $db->prepare($query);
		$statement->bindValue(':firstName', $firstName);
		$statement->bindValue(':lastName', $lastName);
		$statement->bindValue(':contactNum', $contactNum);
		$statement->bindValue(':emailAddress', $emailAddress);
        
        if ($statement->execute()) {
			include ('book_room.php');
			exit();
			$error_message != '';
			$logout_msg != '';
			$success_message = 'Successful Check In!';
		}
	}	
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>Check In</title>
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
	<form class="check-in-out-form" method="post">
	<h1>GUEST CHECK IN</h1><br>
	<?php if (!empty($error_message)){ ?>
			<p class="error"><?php echo $error_message; ?></p>
	<?php } ?>
	<?php if (!empty($success_message)){ ?>
			<p class="success"><?php echo $success_message; ?></p>
	<?php } ?>
		<label>First Name</label>
		<input type="text" name="firstName" placeholder="Enter First Name:"><br>
		
		<label>Last Name</label>
		<input type="text" name="lastName" placeholder="Enter Last Name:"><br>
		
		<label>Email Address</label>
		<input type="username" name="emailAddress" placeholder="Enter Email Address:"><br>
		
		<label>Phone Number</label>
		<input type="tel" name="contactNum" placeholder="Enter Phone Number:"><br>
		
		<button type="submit" name="checkIn" class="check-in-out-btn">Check In</button> 
	</form>
</body>
</html>


