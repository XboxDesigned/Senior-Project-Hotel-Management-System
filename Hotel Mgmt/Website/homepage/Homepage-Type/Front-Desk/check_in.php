<?php
require_once('../../Website/inc/db_connect.php');

$error_message = '';
$success_message = '';


$guests = $db->query("SELECT g.guest_id, g.first_name, g.last_name FROM guests g 
                      JOIN reservations r ON g.guest_id = r.guest_id
					  WHERE r.status != 'checked-in'")
                      ->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['check_in'])) {

    $guest_id = $_POST['guest_id'] ?? null;
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email_address = trim($_POST['email_address'] ?? '');
    $contact_num = trim($_POST['contact_num'] ?? '');

    if ($guest_id) {
        $stmt = $db->prepare("SELECT * FROM guests WHERE guest_id = :guest_id");
        $stmt->bindValue(':guest_id', $guest_id, PDO::PARAM_INT);
        $stmt->execute();
        $guest = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($guest) {
            $first_name = $guest['first_name'];
            $last_name = $guest['last_name'];
            $email_address = $guest['email_address'];
            $contact_num = $guest['contact_num'];
        } else {
            $error_message = 'Selected guest not found.';
        }
    } else {
        if (empty($first_name) || empty($last_name) || empty($email_address) || empty($contact_num)) {
            $error_message = 'Please select a guest to check in.';
        }

        if (empty($error_message)) {
            $query = "INSERT INTO guests (first_name, last_name, contact_num, email_address) 
                      VALUES (:first_name, :last_name, :contact_num, :email_address)";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':first_name', $first_name);
            $stmt->bindValue(':last_name', $last_name);
            $stmt->bindValue(':contact_num', $contact_num);
            $stmt->bindValue(':email_address', $email_address);
            
            if ($stmt->execute()) {
                $guest_id = $db->lastInsertId();
            } else {
                $error_message = 'Error registering new guest.';
            }
        }
    }

    if (!empty($guest_id) && empty($error_message)) {
        // Update reservation status to "checked-in"
        $updateQuery = "UPDATE reservations SET status = 'checked-in' WHERE guest_id = :guest_id";
        $stmt = $db->prepare($updateQuery);
        $stmt->bindValue(':guest_id', $guest_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $success_message = 'Successful Check-In!';
        } else {
            $error_message = 'Error updating reservation status.';
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
    <h2>Hello, <?php echo htmlspecialchars($_SESSION['user']['username'] ?? 'Guest'); ?></h2>

    <form method="post">
        <button type="submit" name="logout" class="logout-register-btn">LOGOUT</button><br>
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

    <label>Select Guest</label>
    <select name="guest_id">
        <option value="">-- Select a Guest --</option>
        <?php foreach ($guests as $guest) { ?>
            <option value="<?php echo $guest['guest_id']; ?>">
                <?php echo htmlspecialchars($guest['first_name'] . ' ' . $guest['last_name']); ?>
            </option>
        <?php } ?>
    </select>
    <br>
    
    <button type="submit" name="check_in" class="check-in-out-btn">Check In</button> 
</form>
</body>
</html>
