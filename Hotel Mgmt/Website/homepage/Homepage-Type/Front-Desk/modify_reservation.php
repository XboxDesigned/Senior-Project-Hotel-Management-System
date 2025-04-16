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

if (isset($_POST['modify']) && isset($_POST['search_val'])) {
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

if (isset($_POST['modify']) && isset($_POST['submit_val'])) {
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
                // Refresh the data after successful update
                if (!empty($search_term)) {
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
                } else {
                    $reservations = $db->query("SELECT r.reservation_id, r.confirmation_num, r.checkin_date, r.checkout_date, 
                                              r.room_num, r.status, g.first_name, g.last_name 
                                              FROM reservations r 
                                              JOIN guests g ON r.guest_id = g.guest_id")
                                     ->fetchAll(PDO::FETCH_ASSOC);
                }
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
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
        }
		
		.search-container {
        display: flex;
        justify-content: center;
        margin: 20px 0;
    }
    .search-form {
        display: flex;
        align-items: center;
        gap: 10px;
    }
       
    </style>
</head>
<body>

<h1>Modify Reservations</h1>

<div class="search-container">
    <form method="post" class="search-form">
        <label>Search by Confirmation #, First Name, or Last Name:</label>
        <input type="text" name="search_term" value="<?php echo htmlspecialchars($search_term); ?>">
        <input type="submit" name="modify" value="Search">
        <input type="hidden" name="search_val"> 
    </form>
</div>

<?php if (!empty($error_message)) { ?>
    <p class="error"><?php echo $error_message; ?></p>
<?php } ?>
<?php if (!empty($success_message)) { ?>
    <p class="success"><?php echo $success_message; ?></p>
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
                    <button onclick="openModal('<?php echo $reservation['reservation_id']; ?>', 
                                              '<?php echo $reservation['confirmation_num']; ?>', 
                                              '<?php echo $reservation['first_name'] . ' ' . $reservation['last_name']; ?>', 
                                              '<?php echo $reservation['checkin_date']; ?>', 
                                              '<?php echo $reservation['checkout_date']; ?>', 
                                              '<?php echo $reservation['room_num']; ?>', 
                                              '<?php echo $reservation['status']; ?>')">
                        Edit
                    </button>
                </td>
            </tr>
        <?php } ?>
        <?php if (empty($reservations)) { ?>
            <tr>
                <td colspan="7">No reservations found.</td>
            </tr>
        <?php } ?>
    </table>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Edit Reservation</h2>
        <form method="post" id="reservationForm">
            <input type="hidden" name="reservation_id" id="modal_reservation_id">
            <p><strong>Confirmation #:</strong> <span id="modal_confirmation_num"></span></p>
            <p><strong>Guest Name:</strong> <span id="modal_guest_name"></span></p>
            
            <label for="modal_checkin_date">Check-in:</label>
            <input type="date" name="checkin_date" id="modal_checkin_date"><br>
            
            <label for="modal_checkout_date">Check-out:</label>
            <input type="date" name="checkout_date" id="modal_checkout_date"><br>
            
            <label for="modal_room_num">Room:</label>
            <select name="room_num" id="modal_room_num">
                <?php foreach ($rooms as $room) { ?>
                    <option value="<?php echo $room['room_num']; ?>">
                        <?php echo $room['room_num'] . ' - ' . $room['room_type'] . ' - $' . $room['rate_plan']; ?>
                    </option>
                <?php } ?>
            </select><br>
            
            <label for="modal_status">Status:</label>
            <select name="status" id="modal_status">
                <option value="confirmed">Confirmed</option>
                <option value="checked-in">Checked-in</option>
                <option value="checked-out">Checked-out</option>
                <option value="cancelled">Cancelled</option>
            </select><br><br>
            
            <input type="submit" name="modify" value="Save Changes">
			<input type="hidden" name="submit_val"> 
        </form>
    </div>
</div>

<script>
    function openModal(reservation_id, confirmation_num, guest_name, checkin_date, checkout_date, room_num, status) {
        document.getElementById('modal_reservation_id').value = reservation_id;
        document.getElementById('modal_confirmation_num').textContent = confirmation_num;
        document.getElementById('modal_guest_name').textContent = guest_name;
        document.getElementById('modal_checkin_date').value = checkin_date;
        document.getElementById('modal_checkout_date').value = checkout_date;
        document.getElementById('modal_room_num').value = room_num;
        document.getElementById('modal_status').value = status;
        
        document.getElementById('editModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
    }
    

    window.onclick = function(event) {
        if (event.target == document.getElementById('editModal')) {
            closeModal();
        }
    }
</script>

</body>
</html>