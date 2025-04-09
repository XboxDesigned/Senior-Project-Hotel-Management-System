<?php
require_once('../../Website/inc/db_connect.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: ../../../login.php');
    exit();
}

$error_message = '';
$success_message = '';
$search_term = '';

if (isset($_POST['search'])) {
    $search_term = trim($_POST['search_term']);
    if (strlen($search_term) > 50) {
        $error_message = "Search term must be 50 characters or less.";
    } else {
        $stmt = $db->prepare("SELECT r.reservation_id, r.confirmation_num, r.checkin_date, r.checkout_date, 
                              r.room_num, r.status, g.first_name, g.last_name 
                              FROM reservations r 
                              JOIN guests g ON r.guest_id = g.guest_id
                              WHERE r.confirmation_num LIKE :search 
                              OR g.first_name LIKE :search 
                              OR g.last_name LIKE :search");
        $stmt->bindValue(':search', '%' . $search_term . '%');
        $stmt->execute();
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    $reservations = $db->query("SELECT r.reservation_id, r.confirmation_num, r.checkin_date, r.checkout_date, 
                               r.room_num, r.status, g.first_name, g.last_name 
                               FROM reservations r 
                               JOIN guests g ON r.guest_id = g.guest_id")
                       ->fetchAll(PDO::FETCH_ASSOC);
}

$rooms = $db->query("SELECT room_num, room_type, rate_plan FROM rooms ORDER BY room_num")
            ->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['modify_reservation'])) {
    $reservation_id = filter_var($_POST['reservation_id'], FILTER_VALIDATE_INT);
    $checkin_date = $_POST['checkin_date'];
    $checkout_date = $_POST['checkout_date'];
    $room_num = filter_var($_POST['room_num'], FILTER_VALIDATE_INT);
    $status = $_POST['status'];

    // Validate required fields
    if (!$reservation_id || empty($checkin_date) || empty($checkout_date) || !$room_num || empty($status)) {
        $error_message = "All fields are required and must be valid.";
    } else {
        // Validate date format and order
        try {
            $check_in = new DateTime($checkin_date);
            $check_out = new DateTime($checkout_date);
            $nights = $check_in->diff($check_out)->days;

            if ($check_out <= $check_in) {
                $error_message = "Check-out date must be after check-in date.";
            } elseif ($nights < 1) {
                $error_message = "Reservation must be for at least 1 night.";
            }
        } catch (Exception $e) {
            $error_message = "Invalid date format.";
        }

        // Validate status
        $valid_statuses = ['confirmed', 'checked-in', 'checked-out', 'cancelled'];
        if (!in_array($status, $valid_statuses)) {
            $error_message = "Invalid status selected.";
        }

        // Validate room exists
        $roomCheck = $db->prepare("SELECT COUNT(*) FROM rooms WHERE room_num = :room_num");
        $roomCheck->execute([':room_num' => $room_num]);
        if ($roomCheck->fetchColumn() == 0) {
            $error_message = "Selected room does not exist.";
        }

        // If no errors so far, check availability
        if (empty($error_message)) {
            $checkAvailability = $db->prepare("SELECT * FROM reservations 
                                              WHERE room_num = :room_num 
                                              AND reservation_id != :reservation_id
                                              AND status IN ('confirmed', 'checked-in')
                                              AND (
                                                (checkin_date <= :check_in AND checkout_date > :check_in)
                                                OR 
                                                (checkin_date < :check_out AND checkout_date >= :check_out)
                                                OR 
                                                (checkin_date >= :check_in AND checkout_date <= :check_out)
                                              )");
            $checkAvailability->execute([
                ':room_num' => $room_num,
                ':reservation_id' => $reservation_id,
                ':check_in' => $checkin_date,
                ':check_out' => $checkout_date
            ]);

            if ($checkAvailability->fetch()) {
                $error_message = "Selected room is not available for these dates.";
            }
        }

        // If still no errors, proceed with update
        if (empty($error_message)) {
            $rateStmt = $db->prepare("SELECT rate_plan FROM rooms WHERE room_num = :room_num");
            $rateStmt->execute([':room_num' => $room_num]);
            $room = $rateStmt->fetch(PDO::FETCH_ASSOC);
            $new_balance = $room['rate_plan'] * $nights;

            $updateStmt = $db->prepare("UPDATE reservations 
                                       SET checkin_date = :checkin_date,
                                           checkout_date = :checkout_date,
                                           room_num = :room_num,
                                           status = :status,
                                           nights = :nights,
                                           balance = :balance
                                       WHERE reservation_id = :reservation_id");
            $result = $updateStmt->execute([
                ':checkin_date' => $checkin_date,
                ':checkout_date' => $checkout_date,
                ':room_num' => $room_num,
                ':status' => $status,
                ':nights' => $nights,
                ':balance' => $new_balance,
                ':reservation_id' => $reservation_id
            ]);

            if ($result) {
                $success_message = "Reservation updated successfully.";
                $reservations = $db->query("SELECT r.reservation_id, r.confirmation_num, r.checkin_date, r.checkout_date, 
                                          r.room_num, r.status, g.first_name, g.last_name 
                                          FROM reservations r 
                                          JOIN guests g ON r.guest_id = g.guest_id")
                                 ->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $error_message = "Error updating reservation.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modify Reservations</title>
</head>
<body>

<h1>Modify Reservations</h1>

<form method="post">
    <label>Search by Confirmation #, First Name, or Last Name:</label>
    <input type="text" name="search_term" value="<?php echo htmlspecialchars($search_term); ?>">
    <input type="submit" name="search" value="Search">
</form>

<?php if (!empty($error_message)) { ?>
    <p><?php echo $error_message; ?></p>
<?php } ?>
<?php if (!empty($success_message)) { ?>
    <p><?php echo $success_message; ?></p>
<?php } ?>

 <div class="table">
        <table id="guests-table" border="1">
    <tr>
        <th>Confirmation #</th>
        <th>Guest Name</th>
        <th>Check-in</th>
        <th>Check-out</th>
        <th>Room</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php foreach ($reservations as $reservation) { ?>
        <tr>
            <td><?php echo htmlspecialchars($reservation['confirmation_num']); ?></td>
            <td><?php echo htmlspecialchars($reservation['first_name'] . ' ' . $reservation['last_name']); ?></td>
            <td><?php echo htmlspecialchars($reservation['checkin_date']); ?></td>
            <td><?php echo htmlspecialchars($reservation['checkout_date']); ?></td>
            <td><?php echo htmlspecialchars($reservation['room_num']); ?></td>
            <td><?php echo htmlspecialchars($reservation['status']); ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="reservation_id" value="<?php echo $reservation['reservation_id']; ?>">
                    Check-in: <input type="date" name="checkin_date" value="<?php echo $reservation['checkin_date']; ?>"><br>
                    Check-out: <input type="date" name="checkout_date" value="<?php echo $reservation['checkout_date']; ?>"><br>
                    Room: 
                    <select name="room_num">
                        <?php foreach ($rooms as $room) { ?>
                            <option value="<?php echo $room['room_num']; ?>" 
                                    <?php echo $room['room_num'] == $reservation['room_num'] ? 'selected' : ''; ?>>
                                <?php echo $room['room_num'] . ' - ' . $room['room_type'] . ' - $' . $room['rate_plan']; ?>
                            </option>
                        <?php } ?>
                    </select><br>
                    Status: 
                    <select name="status">
                        <option value="confirmed" <?php echo $reservation['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="checked-in" <?php echo $reservation['status'] == 'checked-in' ? 'selected' : ''; ?>>Checked-in</option>
                        <option value="checked-out" <?php echo $reservation['status'] == 'checked-out' ? 'selected' : ''; ?>>Checked-out</option>
                        <option value="cancelled" <?php echo $reservation['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select><br>
                    <input type="submit" name="modify_reservation" value="Modify">
                </form>
            </td>
        </tr>
    <?php } ?>
    <?php if (empty($reservations)) { ?>
        <tr>
            <td colspan="7">No reservations found.</td>
        </tr>
    <?php } ?>
</table>

</body>
</html>
