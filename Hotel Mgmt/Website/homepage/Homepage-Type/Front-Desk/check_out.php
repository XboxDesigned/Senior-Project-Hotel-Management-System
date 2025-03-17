<?php

require_once('../../Website/inc/db_connect.php');

$error_message = '';
$success_message = '';

$guests = $db->query("SELECT g.guest_id, g.first_name, g.last_name FROM guests g 
                      JOIN reservations r ON g.guest_id = r.guest_id
                      WHERE r.status != 'checked-out'")
                      ->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['check_out']) && isset($_POST['guest_id'])) {
    $guest_id = $_POST['guest_id'];


    $stmt = $db->prepare("SELECT * FROM guests WHERE guest_id = :guest_id");
    $stmt->bindValue(':guest_id', $guest_id, PDO::PARAM_INT);
    $stmt->execute();
    $guest = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($guest) {

        $updateQuery = "UPDATE reservations SET status = 'checked-out', checkout_date = CURDATE() WHERE guest_id = :guest_id";
        $stmt = $db->prepare($updateQuery);
        $stmt->bindValue(':guest_id', $guest_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $success_message = 'Guest successfully checked out.';
        } else {
            $error_message = 'Error updating reservation status.';
        }
    } else {
        $error_message = 'Selected guest not found.';
    }


    $guests = $db->query("SELECT g.guest_id, g.first_name, g.last_name FROM guests g 
                          JOIN reservations r ON g.guest_id = r.guest_id
                          WHERE r.status != 'checked-out'")
                          ->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html>
<head>

</head>
<body>

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
                <th>Submit</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($guests)) { ?>
                <?php foreach ($guests as $guest) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($guest['guest_id']); ?></td>
                        <td><?php echo htmlspecialchars($guest['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($guest['last_name']); ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="guest_id" value="<?php echo $guest['guest_id']; ?>">
                                <button type="submit" name="check_out" class="table-button">Check Out</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="4">No guests found.</td>
                </tr>
            <?php } ?>
        </tbody>
</div>
	
    <div class="query-buttons-container">
	<div class="query-buttons-top">

</body>
</html>
