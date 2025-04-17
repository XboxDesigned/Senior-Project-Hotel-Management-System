<?php
require_once('../../Website/inc/db_connect.php');

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../../../login.php');
    exit();
}

if (!isset($db) || !$db instanceof PDO) {
    $error = "Database connection failed. Check db_connect.php path or configuration.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modify_room']) && isset($_POST['modify_val'])) {
    // Update Room
        $room_num = $_POST['room_num'];
        $room_type = $_POST['room_type'];
        $room_status = $_POST['room_status'];
        $rate_plan = floatval($_POST['rate_plan']);

        $sql = "UPDATE rooms SET room_type = :room_type, room_status = :room_status, rate_plan = :rate_plan WHERE room_num = :room_num";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':room_num' => $room_num,
            ':room_type' => $room_type,
            ':room_status' => $room_status,
            ':rate_plan' => $rate_plan
        ]);
        $message = "Room $room_num updated successfully!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modify_room']) && isset($_POST['delete_val'])){
        $room_num = $_POST['room_num'];
        $check_sql = "SELECT COUNT(*) FROM reservations WHERE room_num = :room_num AND status IN ('active', 'checked-in', 'confirmed')";
        $check_stmt = $db->prepare($check_sql);
        $check_stmt->execute([':room_num' => $room_num]);
        $active_reservations = $check_stmt->fetchColumn();

        if ($active_reservations > 0) {
            $error = "Cannot delete Room $room_num: It is tied to an active reservation.";
        } else {
            $sql = "DELETE FROM rooms WHERE room_num = :room_num";
            $stmt = $db->prepare($sql);
            $stmt->execute([':room_num' => $room_num]);
            $message = "Room $room_num deleted successfully!";
        }
}


if (isset($db)) {
    $sql = "SELECT room_num, room_type, room_status, rate_plan FROM rooms ORDER BY room_num";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $rooms = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Management</title>
    <style>
		.hidden { display: none; }
    </style>
</head>
<body>
    <h2>Room Management</h2>
	
	<br><br>
    <div class="query-buttons-container">
        <br><br>
        <button onclick="showAllRooms()" id="query-buttons">All Rooms</button>
        <br><br><br>
        <button onclick="showSingleRooms()" id="query-buttons">Single Rooms</button>
        <br>
        <button onclick="showDoubleRooms()" id="query-buttons">Double Rooms</button>
        <br><br><br>
        <button onclick="showOccupiedRooms()" id="query-buttons">Occupied</button>
        <br>
        <button onclick="showAvailableRooms()" id="query-buttons">Available</button>
        <br>
        <button onclick="showMaintenanceRooms()" id="query-buttons">Under Maintenance</button>
    </div>


    <div class="table-container">
        <table border="1" id="rooms-table">
        <thead>
            <tr>
                <th>Room Number</th>
                <th>Room Type</th>
                <th>Status</th>
                <th>Rate Plan ($)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rooms)): ?>
                <tr><td colspan="5">No rooms found or database connection failed.</td></tr>
            <?php else: ?>
                <?php foreach ($rooms as $room): ?>
                    <tr>
					<tr data-status="<?php echo htmlspecialchars($room['room_status']); ?>" data-type="<?php echo htmlspecialchars($room['room_type']); ?>">
                        <form method="POST" action="">
                            <td>
                                <input type="hidden" name="room_num" value="<?php echo htmlspecialchars($room['room_num']); ?>">
                                <?php echo htmlspecialchars($room['room_num']); ?>
                            </td>
                            <td>
                                <select name="room_type" required>
                                    <option value="Single" <?php echo $room['room_type'] === 'Single' ? 'selected' : ''; ?>>Single</option>
                                    <option value="Double" <?php echo $room['room_type'] === 'Double' ? 'selected' : ''; ?>>Double</option>
                                    <option value="Suite" <?php echo $room['room_type'] === 'Suite' ? 'selected' : ''; ?>>Suite</option>
                                </select>
                            </td>
                            <td>
                                <select name="room_status" required>
                                    <option value="available" <?php echo $room['room_status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="occupied" <?php echo $room['room_status'] === 'occupied' ? 'selected' : ''; ?>>Occupied</option>
                                    <option value="maintenance" <?php echo $room['room_status'] === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                    <option value="dirty" <?php echo $room['room_status'] === 'dirty' ? 'selected' : ''; ?>>Dirty</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="rate_plan" value="<?php echo htmlspecialchars($room['rate_plan']); ?>" required>
                            </td>
                            <td>
                                <button type="submit" name="modify_room">Update</button>
								<input type="hidden" name="modify_val"> 
								
								
                                <button type="submit" name="modify_room" class="delete-btn" onclick="return confirm('Are you sure you want to delete Room <?php echo $room['room_num']; ?>?');">Delete</button>
								<input type="hidden" name="delete_val"> 
								
								
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
	</div>
<script>
        function showAllRooms() {
            const rows = document.querySelectorAll('#rooms-table tbody tr');
            rows.forEach(row => row.classList.remove('hidden'));
        }

        function showMaintenanceRooms() {
            const rows = document.querySelectorAll('#rooms-table tbody tr');
            rows.forEach(row => {
                if (row.getAttribute('data-status') === 'maintenance') {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        }
        
        function showOccupiedRooms() {
            const rows = document.querySelectorAll('#rooms-table tbody tr');
            rows.forEach(row => {
                if (row.getAttribute('data-status') === 'occupied') {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        }
        
        function showAvailableRooms() {
            const rows = document.querySelectorAll('#rooms-table tbody tr');
            rows.forEach(row => {
                if (row.getAttribute('data-status') === 'available') {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        }

        
        function showSingleRooms() {
            const rows = document.querySelectorAll('#rooms-table tbody tr');
            rows.forEach(row => {
                if (row.getAttribute('data-type') === 'Single') {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        }
        
        function showDoubleRooms() {
            const rows = document.querySelectorAll('#rooms-table tbody tr');
            rows.forEach(row => {
                if (row.getAttribute('data-type') === 'Double') {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        }
    </script>
 
</body>
</html>