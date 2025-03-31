<?php
require_once('../../Website/inc/db_connect.php');

// Initialize messages
$error_message = '';
$success_message = '';
$search_term = '';

// Get all available rooms for dropdown
try {
    $roomsQuery = "SELECT room_num FROM rooms WHERE room_status = 'available'";
    $availableRooms = $db->query($roomsQuery)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = 'Database error fetching rooms: ' . $e->getMessage();
}

// Handle search
if (isset($_POST['search'])) {
    $search_term = trim($_POST['search']);
    $stmt = $db->prepare("SELECT g.guest_id, g.first_name, g.last_name 
                         FROM guests g 
                         JOIN reservations r ON g.guest_id = r.guest_id
                         WHERE (g.guest_id LIKE :search_term 
                         OR g.first_name LIKE :search_term 
                         OR g.last_name LIKE :search_term)
                         AND r.status != 'checked-in'");
    $stmt->bindValue(':search_term', '%' . $search_term . '%', PDO::PARAM_STR);
    $stmt->execute();
    $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Get all guests not checked in
    $guests = $db->query("SELECT g.guest_id, g.first_name, g.last_name 
                         FROM guests g 
                         JOIN reservations r ON g.guest_id = r.guest_id
                         WHERE r.status != 'checked-in'")
                 ->fetchAll(PDO::FETCH_ASSOC);
}

// Handle check-in with room assignment
if (isset($_POST['check_in']) && isset($_POST['guest_id']) && isset($_POST['room_num'])) {
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
            $updateReservation = "UPDATE reservations SET status = 'checked-in' WHERE guest_id = :guest_id";
            $stmt = $db->prepare($updateReservation);
            $stmt->bindValue(':guest_id', $guest_id, PDO::PARAM_INT);
            $stmt->execute();

            // Update room status
            $updateRoom = "UPDATE rooms SET room_status = 'occupied' WHERE room_num = :room_num";
            $stmt = $db->prepare($updateRoom);
            $stmt->bindValue(':room_num', $room_num, PDO::PARAM_INT);
            $stmt->execute();

            // Assign room to reservation
            $assignRoom = "UPDATE reservations SET room_num = :room_num WHERE guest_id = :guest_id";
            $stmt = $db->prepare($assignRoom);
            $stmt->bindValue(':room_num', $room_num, PDO::PARAM_INT);
            $stmt->bindValue(':guest_id', $guest_id, PDO::PARAM_INT);
            $stmt->execute();

            $db->commit();
            $success_message = 'Guest successfully checked in and assigned to room.';
            
            // Refresh data
            $guests = $db->query("SELECT g.guest_id, g.first_name, g.last_name 
                                 FROM guests g 
                                 JOIN reservations r ON g.guest_id = r.guest_id
                                 WHERE r.status != 'checked-in'")
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

// Handle cancel
if (isset($_POST['cancel']) && isset($_POST['guest_id'])) {
    // Your existing cancel logic here
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Guest Check-In</title>
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
                            <td><?php echo htmlspecialchars($guest['first_name'] . ' ' . htmlspecialchars($guest['last_name'])); ?></td>
                           
						   <td>
								<select name="room_num" form="checkin-form-<?php echo $guest['guest_id']; ?>">
									<option value="">Select Room</option>
									<?php 

									$roomsQuery = "SELECT room_num, room_type, rate_plan FROM rooms WHERE room_status = 'available'";
									$availableRooms = $db->query($roomsQuery)->fetchAll(PDO::FETCH_ASSOC);
									
									foreach ($availableRooms as $room) { ?>
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
                                <form method="post">
                                    <input type="hidden" name="guest_id" value="<?php echo $guest['guest_id']; ?>">
                                    <button type="submit" name="cancel" class="table-button">Cancel</button>
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

    function showCurrentDate() {
        
    }
    
    function showAllDate() {
        const rows = document.querySelectorAll('#guests-table tbody tr');
        rows.forEach(row => row.classList.remove('hidden'));
    }
</script>
</body>
</html>