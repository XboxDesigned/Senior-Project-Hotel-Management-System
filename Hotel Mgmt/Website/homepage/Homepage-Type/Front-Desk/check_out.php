<?php
require_once('../../Website/inc/db_connect.php');

$error_message = '';
$success_message = '';

// Fetch currently checked-in guests
$checked_in_guests = $db->query("SELECT g.guest_id, g.first_name, g.last_name FROM guests g 
                                JOIN reservations r ON g.guest_id = r.guest_id
                                WHERE r.status = 'checked-in'")
                                ->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['check_out'])) {
    $guest_id = $_POST['guest_id'] ?? null;

    if (!$guest_id) {
        $error_message = 'Please select a guest to check out.';
    } else {
  
        $updateQuery = "UPDATE reservations SET status = 'checked-out', checkout_date = CURDATE() WHERE guest_id = :guest_id";
        $stmt = $db->prepare($updateQuery);
        $stmt->bindValue(':guest_id', $guest_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $success_message = 'Guest successfully checked out!';
        } else {
            $error_message = 'Error processing check-out.';
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Check Out</title>
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
    <h1>GUEST CHECK OUT</h1><br>

    <?php if (!empty($error_message)) { ?>
        <p class="error"><?php echo $error_message; ?></p>
    <?php } ?>
    <?php if (!empty($success_message)) { ?>
        <p class="success"><?php echo $success_message; ?></p>
    <?php } ?>

    <label>Select Guest</label>
    <select name="guest_id">
        <option value="">-- Select a Guest --</option>
        <?php foreach ($checked_in_guests as $guest) { ?>
            <option value="<?php echo $guest['guest_id']; ?>">
                <?php echo htmlspecialchars($guest['first_name'] . ' ' . $guest['last_name']); ?>
            </option>
        <?php } ?>
    </select>
    <br>

    <button type="submit" name="check_out" class="check-in-out-btn">Check Out</button> 
</form>
</body>
</html>
