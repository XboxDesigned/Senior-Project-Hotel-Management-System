<?php
require_once('../../Website/inc/db_connect.php');

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['username'])) {
    header('Location: ../login.php');
    exit();
}
$username = $_SESSION['user']['username'];

// Initialize messages
$error_message = '';
$success_message = '';
$search_term = '';

// Get all available rooms for dropdown
try {
    $roomsQuery = "SELECT room_num, room_type, rate_plan FROM rooms WHERE room_status = 'available'";
    $availableRooms = $db->query($roomsQuery)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = 'Database error fetching rooms: ' . $e->getMessage();
}

// Fetch guests with confirmed reservations
$guests = $db->query("SELECT g.guest_id, g.first_name, g.last_name, r.confirmation_num, r.nights, r.checkin_date, r.checkout_date 
                     FROM guests g 
                     JOIN reservations r ON g.guest_id = r.guest_id
                     WHERE r.status = 'confirmed'")
             ->fetchAll(PDO::FETCH_ASSOC);

// Handle check-in with room assignment
if (isset($_POST['check_in']) && isset($_POST['guest_id']) && isset($_POST['room_num']) && !isset($_POST['cancel_val'])) {
    $guest_id = (int)$_POST['guest_id'];
    $room_num = (int)$_POST['room_num'];
    
    try {
        // Begin transaction
        $db->beginTransaction();

        // Verify guest and reservation exist
        $stmt = $db->prepare("SELECT g.*, r.confirmation_num, r.nights, r.checkin_date, r.checkout_date 
                              FROM guests g 
                              JOIN reservations r ON g.guest_id = r.guest_id 
                              WHERE g.guest_id = :guest_id AND r.status = 'confirmed'");
        $stmt->bindValue(':guest_id', $guest_id, PDO::PARAM_INT);
        $stmt->execute();
        $guest = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($guest) {
            // Get room rate
            $stmt = $db->prepare("SELECT rate_plan FROM rooms WHERE room_num = :room_num AND room_status = 'available'");
            $stmt->bindValue(':room_num', $room_num, PDO::PARAM_INT);
            $stmt->execute();
            $room = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$room) {
                throw new PDOException("Selected room is not available.");
            }

            $rate_plan = (float)$room['rate_plan'];
            $nights = (int)$guest['nights'];

            // Fallback: Calculate nights if not set or invalid
            if ($nights <= 0 || $guest['nights'] === null) {
                $checkin = new DateTime($guest['checkin_date']);
                $checkout = new DateTime($guest['checkout_date']);
                $nights = $checkin->diff($checkout)->days;
                if ($nights <= 0) {
                    throw new PDOException("Invalid reservation dates: nights calculated as $nights.");
                }
                // Update nights in reservations
                $stmt = $db->prepare("UPDATE reservations SET nights = :nights WHERE confirmation_num = :confirmation_num");
                $stmt->bindValue(':nights', $nights, PDO::PARAM_INT);
                $stmt->bindValue(':confirmation_num', $guest['confirmation_num'], PDO::PARAM_STR);
                $stmt->execute();
            }

            // Validate rate_plan
            if ($rate_plan <= 0) {
                throw new PDOException("Invalid room rate: rate_plan is $rate_plan for room $room_num.");
            }

            // Calculate charge and ensure two decimal places
            $charge_amount = number_format($rate_plan * $nights, 2, '.', '');

            // Debug: Log values
            error_log("Check-in for guest_id $guest_id: rate_plan=$rate_plan, nights=$nights, charge_amount=$charge_amount, confirmation_num={$guest['confirmation_num']}, username=$username");

            // Update reservation status and assign room
            $updateReservation = "UPDATE reservations 
                                  SET status = 'checked-in', room_num = :room_num, balance = balance + :charge_amount 
                                  WHERE guest_id = :guest_id AND confirmation_num = :confirmation_num";
            $stmt = $db->prepare($updateReservation);
            $stmt->bindValue(':room_num', $room_num, PDO::PARAM_INT);
            $stmt->bindValue(':charge_amount', $charge_amount, PDO::PARAM_STR);
            $stmt->bindValue(':guest_id', $guest_id, PDO::PARAM_INT);
            $stmt->bindValue(':confirmation_num', $guest['confirmation_num'], PDO::PARAM_STR);
            $stmt->execute();

            // Update room status
            $updateRoom = "UPDATE rooms SET room_status = 'occupied' WHERE room_num = :room_num";
            $stmt = $db->prepare($updateRoom);
            $stmt->bindValue(':room_num', $room_num, PDO::PARAM_INT);
            $stmt->execute();

            // Insert charge into guest_charges
            $insertCharge = "INSERT INTO guest_charges 
                             (guest_id, confirmation_num, description, amount, username, date_added) 
                             VALUES (:guest_id, :confirmation_num, :description, :amount, :username, NOW())";
            $stmt = $db->prepare($insertCharge);
            $stmt->bindValue(':guest_id', $guest_id, PDO::PARAM_INT);
            $stmt->bindValue(':confirmation_num', $guest['confirmation_num'], PDO::PARAM_STR);
            $stmt->bindValue(':description', "Room charge for {$nights} nights at room {$room_num}", PDO::PARAM_STR);
            $stmt->bindValue(':amount', $charge_amount, PDO::PARAM_STR);
            $stmt->bindValue(':username', $username, PDO::PARAM_STR);
            $stmt->execute();

            $db->commit();
            $success_message = "Guest successfully checked in to Room {$room_num}. Charge of $" . number_format($charge_amount, 2) . " added.";
            
            // Refresh data
            $guests = $db->query("SELECT g.guest_id, g.first_name, g.last_name, r.confirmation_num, r.nights, r.checkin_date, r.checkout_date 
                                 FROM guests g 
                                 JOIN reservations r ON g.guest_id = r.guest_id
                                 WHERE r.status = 'confirmed'")
                         ->fetchAll(PDO::FETCH_ASSOC);
            $availableRooms = $db->query($roomsQuery)->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $error_message = 'Selected guest or reservation not found, or already checked-in.';
            $db->rollBack();
        }
    } catch (PDOException $e) {
        $db->rollBack();
        $error_message = 'Database error: ' . $e->getMessage();
    }
}

// Handle cancellation
if (isset($_POST['check_in']) && isset($_POST['guest_id']) && isset($_POST['cancel_val'])) {
    $guest_id = (int)$_POST['guest_id'];
    
    try {
        // Begin transaction
        $db->beginTransaction();

        // Verify guest exists
        $stmt = $db->prepare("SELECT * FROM guests WHERE guest_id = :guest_id");
        $stmt->bindValue(':guest_id', $guest_id, PDO::PARAM_INT);
        $stmt->execute();
        $guest = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($guest) {
            // Get room number if assigned (to make it available again)
            $stmt = $db->prepare("SELECT room_num FROM reservations WHERE guest_id = :guest_id");
            $stmt->bindValue(':guest_id', $guest_id, PDO::PARAM_INT);
            $stmt->execute();
            $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
            $room_num = $reservation['room_num'];

            // Update reservation status to canceled
            $updateReservation = "UPDATE reservations SET status = 'cancelled' WHERE guest_id = :guest_id";
            $stmt = $db->prepare($updateReservation);
            $stmt->bindValue(':guest_id', $guest_id, PDO::PARAM_INT);
            $stmt->execute();

            // If a room was assigned, make it available again
            if ($room_num) {
                $updateRoom = "UPDATE rooms SET room_status = 'available' WHERE room_num = :room_num";
                $stmt = $db->prepare($updateRoom);
                $stmt->bindValue(':room_num', $room_num, PDO::PARAM_INT);
                $stmt->execute();
            }

            $db->commit();
            $success_message = 'Reservation successfully cancelled.';
            
            // Refresh data
            $guests = $db->query("SELECT g.guest_id, g.first_name, g.last_name, r.confirmation_num, r.nights, r.checkin_date, r.checkout_date 
                                 FROM guests g 
                                 JOIN reservations r ON g.guest_id = r.guest_id
                                 WHERE r.status = 'confirmed'")
                         ->fetchAll(PDO::FETCH_ASSOC);
            $availableRooms = $db->query($roomsQuery)->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $error_message = 'Selected guest not found.';
            $db->rollBack();
        }
    } catch (PDOException $e) {
        $db->rollBack();
        $error_message = 'Database error: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Guest Check-In</title>
</head>
<body>
    <?php if (!empty($error_message)) { ?>
        <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php } ?>
    <?php if (!empty($success_message)) { ?>
        <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
    <?php } ?>
    
    <div class="query-buttons-container">
    </div>

    <div class="table">
        <table id="guests-table" border="1">
            <thead>
                <tr>
                    <th>Guest ID</th>
                    <th>Name</th>
                    <th>Assign Room</th>
                    <th>Check In</th>
                    <th>Cancel</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($guests)) { ?>
                    <?php foreach ($guests as $guest) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($guest['guest_id']); ?></td>
                            <td><?php echo htmlspecialchars($guest['first_name'] . ' ' . $guest['last_name']); ?></td>
                            <td>
                                <select name="room_num" form="checkin-form-<?php echo $guest['guest_id']; ?>">
                                    <option value="">Select Room</option>
                                    <?php foreach ($availableRooms as $room) { ?>
                                        <option value="<?php echo htmlspecialchars($room['room_num']); ?>">
                                            Room <?php echo htmlspecialchars($room['room_num']); ?> - 
                                            <?php echo htmlspecialchars($room['room_type']); ?> - $
                                            <?php echo htmlspecialchars($room['rate_plan']); ?>/night
                                        </option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td>
                                <form method="post" id="checkin-form-<?php echo $guest['guest_id']; ?>" onsubmit="return confirmCheckIn(this);">
                                    <input type="hidden" name="guest_id" value="<?php echo $guest['guest_id']; ?>">
                                    <button type="submit" name="check_in" class="table-button">Check In</button>
                                </form>
                            </td>
                            <td>
                                <form method="post" id="cancel-form-<?php echo $guest['guest_id']; ?>" onsubmit="return confirmCancel(this);">
                                    <input type="hidden" name="guest_id" value="<?php echo $guest['guest_id']; ?>">
                                    <input type="hidden" name="cancel_val">
                                    <button type="submit" name="check_in" class="table-button">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="5">No guests found.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <script>
    function confirmCheckIn(form) {
        const row = form.closest('tr');
        const roomSelect = row.querySelector('select[name="room_num"]');
        const selectedRoom = roomSelect ? roomSelect.value : '';
        const guestName = row.cells[1].textContent.trim();
        
        if (!selectedRoom) {
            alert('Please select a room before checking in.');
            return false;
        }
        
        return confirm(`Are you sure you want to check in ${guestName} to Room ${selectedRoom}?`);
    }

    function confirmCancel(form) {
        const row = form.closest('tr');
        const guestName = row.cells[1].textContent.trim();
        return confirm(`Are you sure you want to cancel ${guestName}'s reservation? This action cannot be undone.`);
    }
    </script>
</body>
</html>