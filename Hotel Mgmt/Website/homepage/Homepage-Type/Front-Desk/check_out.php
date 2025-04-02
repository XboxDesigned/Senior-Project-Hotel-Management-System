<?php
require_once('../../Website/inc/db_connect.php');

$error_message = '';
$success_message = '';

// Modified query to include room number from reservations
$guests = $db->query("SELECT g.guest_id, g.first_name, g.last_name, r.room_num 
                      FROM guests g 
                      JOIN reservations r ON g.guest_id = r.guest_id
                      WHERE r.status != 'checked-out'
					  AND r.status != 'cancelled'")
              ->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['check_out']) && isset($_POST['guest_id']) && isset($_POST['room_num'])) {
    $guest_id = (int)$_POST['guest_id'];
    $room_num = (int)$_POST['room_num'];

    try {
        // Begin transaction
        $db->beginTransaction();

        // Verify guest exists
        $stmt = $db->prepare("SELECT * FROM guests WHERE guest_id = :guest_id");
        $stmt->bindValue(':guest_id', $guest_id, PDO::PARAM_INT);
        $stmt->execute();
        $guest = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($guest) {
            // Update reservation status
            $updateReservation = "UPDATE reservations SET status = 'checked-out', checkout_date = CURDATE() WHERE guest_id = :guest_id";
            $stmt = $db->prepare($updateReservation);
            $stmt->bindValue(':guest_id', $guest_id, PDO::PARAM_INT);
            $stmt->execute();

            // Update room status back to available
            $updateRoom = "UPDATE rooms SET room_status = 'available' WHERE room_num = :room_num";
            $stmt = $db->prepare($updateRoom);
            $stmt->bindValue(':room_num', $room_num, PDO::PARAM_INT);
            $stmt->execute();

            $db->commit();
            $success_message = 'Guest successfully checked out and room made available.';
            
            // Refresh guest list
            $guests = $db->query("SELECT g.guest_id, g.first_name, g.last_name, r.room_num 
                                 FROM guests g 
                                 JOIN reservations r ON g.guest_id = r.guest_id
                                 WHERE r.status != 'checked-out'
								 AND r.status != 'cancelled'")
                         ->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Guest Check-Out</title>
    <style>
        .hidden { display: none; }
    </style>
</head>
<body>
    <?php if (!empty($error_message)) { ?>
        <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php } ?>
    <?php if (!empty($success_message)) { ?>
        <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
    <?php } ?>
    
    <div class="query-buttons-container">
        <br><br>
        <button onclick="showCurrentDate()" class="query-buttons">Current Date</button>
        <br><br>
        <button onclick="showAllDate()" class="query-buttons">All Dates</button>
    </div>
    
    <div class="table">
        <table id="guests-table" border="1">
            <thead>
                <tr>
                    <th>Guest ID</th>
                    <th>Name</th>
                    <th>Room</th>
                    <th>Check Out</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($guests)) { ?>
                    <?php foreach ($guests as $guest) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($guest['guest_id']); ?></td>
                            <td><?php echo htmlspecialchars($guest['first_name'] . ' ' . htmlspecialchars($guest['last_name'])); ?></td>

                            <td><?php echo isset($guest['room_num']) ? htmlspecialchars($guest['room_num']) : 'Not assigned'; ?></td>
                            <td>
                                <form method="post" onsubmit="return confirmCheckOut(this);">
                                    <input type="hidden" name="guest_id" value="<?php echo $guest['guest_id']; ?>">
                                    <input type="hidden" name="room_num" value="<?php echo isset($guest['room_num']) ? $guest['room_num'] : ''; ?>">
                                    <button type="submit" name="check_out" class="table-button">Check Out</button>
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
    function confirmCheckOut(form) {
        const row = form.closest('tr');
        const guestName = row.cells[1].textContent.trim();
        const roomNum = row.cells[2].textContent.trim();
        
        return confirm(`Are you sure you want to check out ${guestName} from Room ${roomNum}?`);
    }

    function showCurrentDate() {
        // Your implementation for showing current date
    }
    
    function showAllDate() {
        const rows = document.querySelectorAll('#guests-table tbody tr');
        rows.forEach(row => row.classList.remove('hidden'));
    }
</script>
</body>
</html>