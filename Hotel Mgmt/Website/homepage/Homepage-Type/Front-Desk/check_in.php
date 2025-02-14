<?php
require_once '../../../inc/db_connect.php';

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: ../../../login.php'); // Redirect to the login if not logged in
    exit();
}

// Check if the logout button is clicked
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: ../../../login.php');
    exit();
}

$name = $_SESSION['user']['username'];
$role = $_SESSION['user']['role'];

// Initialize error and success messages
$error_message = '';
$success_message = '';

// Redirects
if (isset($_POST['home'])) {
    header('Location: frontdesk_homepage.php');
    exit();
}

if (isset($_POST['rooms'])) {
    header('Location: rooms.php');
    exit();
}

if (isset($_POST['cancellations'])) {
    header('Location: cancellations.php');
    exit();
}

if (isset($_POST['book_room'])) {
    header('Location: book_room.php');
    exit();
}

if (isset($_POST['night_audit'])) {
    header('Location: ../../../inc/night_audit.php');
    exit();
}

if (isset($_POST['check_in'])) {
    header('Location: check_in.php');
    exit();
}

if (isset($_POST['check_out'])) {
    header('Location: check_out.php');
    exit();
}

if (isset($_POST['checkIn'])) {
    // Get guest info from the form
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $emailAddress = $_POST['emailAddress'];
    $contactNum = $_POST['contactNum'];

    // Check for empty fields
    if (empty($firstName) || empty($lastName) || empty($emailAddress) || empty($contactNum)) {
        $error_message = 'Please provide more info.';
    }

    // Check if guest already exists
    if (empty($error_message)) {
        $statement = $db->prepare('SELECT guest_id FROM guests WHERE firstName = :firstName
                                                         AND lastName = :lastName
                                                         AND contactNum = :contactNum
                                                         AND emailAddress = :emailAddress');
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
        // SQL query to insert the new guest into the database
        $query = 'INSERT INTO guests (firstName, lastName, contactNum, emailAddress)
                  VALUES (:firstName, :lastName, :contactNum, :emailAddress)';

        $statement = $db->prepare($query);
        $statement->bindValue(':firstName', $firstName);
        $statement->bindValue(':lastName', $lastName);
        $statement->bindValue(':contactNum', $contactNum);
        $statement->bindValue(':emailAddress', $emailAddress);

        if ($statement->execute()) {
            $success_message = 'Successful Check In!';
            header('Location: book_room.php');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Check In</title>
    <link rel="stylesheet" type="text/css" href="../../../inc/homepage_main.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
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
        <form method="post"><button type="submit" name="home" id="side-buttons">Home</button></form><br>
        <form method="post"><button type="submit" name="rooms" id="side-buttons">Rooms</button></form><br>
        <form method="post"><button type="submit" name="cancellations" id="side-buttons">Cancellations</button></form><br>
        <form method="post"><button type="submit" name="guests" id="side-buttons">Guests</button></form>
    </div>
    
    <div class="side-buttons-bottom">
        <form method="post"><button type="submit" name="maintenance" id="side-buttons">Maintenance</button></form><br>
        <form method="post"><button type="submit" name="night_audit" id="side-buttons">Night Audit</button></form><br>
        <form method="post"><button type="submit" name="book_room" id="side-buttons">Book Room</button></form><br>
        <form method="post"><button type="submit" name="check_in" id="side-buttons">Check In</button></form><br>
        <form method="post"><button type="submit" name="check_out" id="side-buttons">Check Out</button></form>
    </div>
</div>

<form class="check-in-out-form" method="post">
    <h1>GUEST CHECK IN</h1><br>
    <?php if (!empty($error_message)) { ?>
        <p class="error"><?php echo $error_message; ?></p>
    <?php } ?>
    <?php if (!empty($success_message)) { ?>
        <p class="success"><?php echo $success_message; ?></p>
    <?php } ?>
    
    <label>First Name</label>
    <input type="text" name="firstName" placeholder="Enter First Name:"><br>
    
    <label>Last Name</label>
    <input type="text" name="lastName" placeholder="Enter Last Name:"><br>
    
    <label>Email Address</label>
    <input type="email" name="emailAddress" placeholder="Enter Email Address:"><br>
    
    <label>Phone Number</label>
    <input type="tel" name="contactNum" placeholder="Enter Phone Number:"><br>
    
    <button type="submit" name="checkIn" class="check-in-out-btn">Check In</button> 
</form>
</body>
</html>
