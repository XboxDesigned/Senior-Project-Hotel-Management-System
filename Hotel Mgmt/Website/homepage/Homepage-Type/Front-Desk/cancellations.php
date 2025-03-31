<?php

require_once('../../Website/inc/db_connect.php');

$error_message = '';
$success_message = '';

try {

    $query = "
        SELECT g.guest_id, g.first_name, g.last_name, g.contact_num, g.email_address, r.confirmation_num, r.checkin_date, r.checkout_date
        FROM guests g
        JOIN reservations r ON g.guest_id = r.guest_id
        WHERE r.status = 'cancelled'
    ";
    $stmt = $db->query($query);
    $cancelled_guests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = 'Database error: ' . $e->getMessage();
}

?>

<!DOCTYPE html>
<html>
<head>
<style>
        .hidden { display: none; }
    </style>
    
</head>
<body>
<br><br>

    <?php if (!empty($error_message)) { ?>
        <p class="error"><?php echo $error_message; ?></p>
    <?php } ?>
    <?php if (!empty($success_message)) { ?>
        <p class="success"><?php echo $success_message; ?></p>
    <?php } ?>

<div class="table">
    <table border="1">
        <thead>
            <tr>
                <th>Guest ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Contact Number</th>
                <th>Email Address</th>
            </tr>
        </thead>
        <tbody>
                <?php if (!empty($cancelled_guests)) { ?>
                    <?php foreach ($cancelled_guests as $guest) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($guest['guest_id']); ?></td>
                            <td><?php echo htmlspecialchars($guest['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($guest['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($guest['contact_num']); ?></td>
                            <td><?php echo htmlspecialchars($guest['email_address']); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">No guests with cancelled reservations found.</td>
                    </tr>
                <?php } ?>
            </tbody>
    </div>
</div>

    <div class="query-buttons-container">
	<div class="query-buttons-top">

</body>
</html>