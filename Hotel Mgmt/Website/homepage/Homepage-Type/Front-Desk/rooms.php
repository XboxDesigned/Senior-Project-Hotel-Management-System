<?php

require_once('../../Website/inc/db_connect.php');

$error_message = '';
$success_message = '';

try {
    $query = "SELECT room_num, room_type, room_status, rate_plan FROM rooms";
    $stmt = $db->query($query);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = 'Database error: ' . $e->getMessage();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Room Management</title>
    <style>
        .hidden { display: none; }
    </style>
</head>
<body>
    <?php if (!empty($error_message)) { ?>
        <p class="error"><?php echo $error_message; ?></p>
    <?php } ?>
    <?php if (!empty($success_message)) { ?>
        <p class="success"><?php echo $success_message; ?></p>
    <?php } ?>

	<br><br>
    <div class="query-buttons-container">
		<br><br>
        <button onclick="showAllRooms()" id="query-buttons">All Rooms</button>
		<br><br><br>
		<button onclick="showSingleRooms()" id="query-buttons">Single Rooms</button>
		<br>
		<button onclick="showDoubleRooms()" id="query-buttons">Double Rooms</button>
		<br><br><br>
        <button onclick="showPendingRooms()" id="query-buttons">Pending</button>
		<br>
		<button onclick="showOccupiedRooms()" id="query-buttons">Occupied</button>
		<br>
		<button onclick="showAvailableRooms()" id="query-buttons">Available</button>
		<br>
		<button onclick="showMaintenanceRooms()" id="query-buttons">Under Maintenance</button>

    </div>

    <div class="table">
        <table border="1" id="rooms-table">
            <thead>
                <tr>
                    <th>Room Number</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Rate Plan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($rooms)) { ?>
                    <?php foreach ($rooms as $room) { ?>
                        <tr data-status="<?php echo htmlspecialchars($room['room_status']); ?>" data-type="<?php echo htmlspecialchars($room['room_type']); ?>">
						
                            <td><?php echo htmlspecialchars($room['room_num']); ?></td>
                            <td><?php echo htmlspecialchars($room['room_type']); ?></td>
                            <td><?php echo htmlspecialchars($room['room_status']); ?></td>
                            <td>$<?php echo htmlspecialchars($room['rate_plan']); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No rooms found.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <script>

        function showAllRooms() {
            const rows = document.querySelectorAll('#rooms-table tbody tr');
            rows.forEach(row => row.classList.remove('hidden'));
        }

        function showPendingRooms() {
            const rows = document.querySelectorAll('#rooms-table tbody tr');
            rows.forEach(row => {
                if (row.getAttribute('data-status') === 'pending') {
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