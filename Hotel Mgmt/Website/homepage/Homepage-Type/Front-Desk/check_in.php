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

if (isset($_POST['check_in'])) {
    // Get guest info from the form
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email_address = $_POST['email_address'];
    $contact_num = $_POST['contact_num'];

    // Check for empty fields
    if (empty($first_name) || empty($last_name) || empty($email_address) || empty($contact_num)) {
        $error_message = 'Please provide more info.';
    }

    // Check if guest already exists
    if (empty($error_message)) {
        $statement = $db->prepare('SELECT guest_id FROM guests WHERE first_name = :first_name
                                                         AND last_name = :last_name
                                                         AND contact_num = :contact_num
                                                         AND email_address = :email_address');
        $statement->bindValue(':first_name', $first_name);
        $statement->bindValue(':last_name', $last_name);
        $statement->bindValue(':contact_num', $contact_num);
        $statement->bindValue(':email_address', $email_address);
        $statement->execute();

        if ($statement->fetch()) {
            $error_message = 'Guest already checked in.';
        }
    }

    if (empty($error_message)) {
        // SQL query to insert the new guest into the database
        $query = 'INSERT INTO guests (first_name, last_name, contact_num, email_address)
                  VALUES (:first_name, :last_name, :contact_num, :email_address)';

        $statement = $db->prepare($query);
        $statement->bindValue(':first_name', $first_name);
        $statement->bindValue(':last_name', $last_name);
        $statement->bindValue(':contact_num', $contact_num);
        $statement->bindValue(':email_address', $email_address);

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
    <input type="text" name="first_name" placeholder="Enter First Name:"><br>
    
    <label>Last Name</label>
    <input type="text" name="last_name" placeholder="Enter Last Name:"><br>
    
    <label>Email Address</label>
    <input type="email" name="email_address" placeholder="Enter Email Address:"><br>
    
    <label>Phone Number</label>
    <input type="tel" name="contact_num" placeholder="Enter Phone Number:"><br>
    
    <button type="submit" name="check_in" class="check-in-out-btn">Check In</button> 
</form>
</body>
</html>
