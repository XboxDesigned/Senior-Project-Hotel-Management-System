<?php
require_once('../../Website/inc/db_connect.php');

$error_message = '';
$success_message = '';
$rooms = [];
$all_staff = [];

try {
    // Fetch all rooms
    $query = "SELECT room_num, room_type, room_status, rate_plan FROM rooms";
    $stmt = $db->query($query);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Handle maintenance request submission only if form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['maintenance'])) {
        // Initialize variables
        $room_num = $_POST['room_num'] ?? '';
        $description = $_POST['description'] ?? '';
        $assigned_to = $_POST['assigned_to'] ?? '';
        
        // Validate inputs
        if (empty($room_num) || empty($description) || empty($assigned_to)) {
            $error_message = '';
        } else {
            // Begin transaction
            $db->beginTransaction();
            
            try {
                // Insert maintenance task
                $stmt = $db->prepare("INSERT INTO maintenance_tasks (room_num, task_description, status, assigned_to) 
                                     VALUES (:room_num, :description, 'pending', :assigned_to)");
                $stmt->bindParam(':room_num', $room_num);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':assigned_to', $assigned_to);
                $stmt->execute();
                
                // Update room status to maintenance
                $stmt = $db->prepare("UPDATE rooms SET room_status = 'maintenance' WHERE room_num = :room_num");
                $stmt->bindParam(':room_num', $room_num);
                $stmt->execute();
                
                $db->commit();
                
                // Refresh room data
                $query = "SELECT room_num, room_type, room_status, rate_plan FROM rooms";
                $stmt = $db->query($query);
                $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $db->rollBack();
            }
        }
    }
    
    // Fetch maintenance and housekeeping staff with roles for assignment dropdown
    $staff_query = "SELECT u_id, username, role FROM users WHERE role = 'maintenance' OR role = 'housekeeper'";
    $staff_stmt = $db->query($staff_query);
    $all_staff = $staff_stmt->fetchAll(PDO::FETCH_ASSOC);

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
            max-width: 600px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .filter-buttons {
            margin-bottom: 15px;
        }
        .filter-buttons button {
            margin-right: 5px;
            padding: 5px 10px;
            cursor: pointer;
        }
        .staff-option {
            display: flex;
            justify-content: space-between;
        }
        .staff-role {
            color: #666;
            font-style: italic;
        }
		
    </style>
</head>
<body>
    <h1>Room Management</h1>
    
    <?php if (!empty($error_message)) { ?>
            <?php echo htmlspecialchars($error_message); ?>
    <?php } ?>
    
    <?php if (!empty($success_message)) { ?>
            <?php echo htmlspecialchars($success_message); ?>
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
                    <th>Type</th>
                    <th>Status</th>
                    <th>Rate Plan</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($rooms)) { ?>
                    <?php foreach ($rooms as $room) { 
                        $status_class = 'status-' . strtolower($room['room_status']);
                    ?>
                        <tr data-status="<?php echo htmlspecialchars($room['room_status']); ?>" 
                            data-type="<?php echo htmlspecialchars($room['room_type']); ?>">
                            <td><?php echo htmlspecialchars($room['room_num']); ?></td>
                            <td><?php echo htmlspecialchars($room['room_type']); ?></td>
                            <td class="<?php echo $status_class; ?>">
                                <?php echo htmlspecialchars($room['room_status']); ?>
                            </td>
                            <td>$<?php echo number_format($room['rate_plan'], 2); ?></td>
                            <td>
                                <button class="maintenance-btn" onclick="openMaintenanceModal('<?php echo $room['room_num']; ?>')">
                                    Request Action
                                </button>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">No rooms found.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div id="maintenanceModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeMaintenanceModal()">&times;</span>
            <h2>Submit Request</h2>
            <form method="post" action="">
                <input type="hidden" id="modal-room-num" name="room_num">
                <input type="hidden" name="maintenance" value="1">
                
                <div class="form-group">
                    <label id="description-label" for="description">Description for Room #<span id="display-room-num"></span>:</label><br>
                    <textarea id="description" name="description" rows="4" cols="50" required></textarea>
                </div>
                
                <div class="filter-buttons">
                    <button class="request-btn" type="button" onclick="filterStaff('all')">All Staff</button>
                    <button class="request-btn" type="button" onclick="filterStaff('maintenance')">Maintenance Only</button>
                    <button class="request-btn" type="button" onclick="filterStaff('housekeeper')">Housekeeping Only</button>
                </div>
                
                <div class="form-group">
                    <label for="assigned_to">Assign To:</label><br>
                    <select id="assigned_to" name="assigned_to" required>
                        <option value="">-- Select Staff Member --</option>
                        <?php foreach ($all_staff as $staff) { ?>
                            <option value="<?php echo $staff['u_id']; ?>" data-role="<?php echo htmlspecialchars($staff['role']); ?>">
                                <span class="staff-option">
                                    <span class="staff-name"><?php echo htmlspecialchars($staff['username']); ?></span>
                                    <span class="staff-role">(<?php echo htmlspecialchars($staff['role']); ?>)</span>
                                </span>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" onclick="closeMaintenanceModal()" class="request-btn">Cancel</button>
                    <button type="submit" class="request-btn">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Room filtering functions
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
        
        function showSuiteRooms() {
            const rows = document.querySelectorAll('#rooms-table tbody tr');
            rows.forEach(row => {
                if (row.getAttribute('data-type') === 'Suite') {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        }

        // Staff filtering for modal
        function filterStaff(role) {
            const options = document.querySelectorAll('#assigned_to option');
            options.forEach(option => {
                if (option.value === "") {
                    option.style.display = 'block'; // Always show the placeholder
                    return;
                }
                
                const optionRole = option.getAttribute('data-role');
                if (role === 'all' || optionRole === role) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });
            
            // Reset selection when filtering
            document.getElementById('assigned_to').value = "";
        }

        // Maintenance modal functions
        function openMaintenanceModal(roomNum) {
            document.getElementById('modal-room-num').value = roomNum;
            document.getElementById('display-room-num').textContent = roomNum;
            document.getElementById('maintenanceModal').style.display = 'block';
            // Show all staff by default when modal opens
            filterStaff('all');
        }

        function closeMaintenanceModal() {
            document.getElementById('maintenanceModal').style.display = 'none';
            document.getElementById('description').value = '';
            document.getElementById('assigned_to').value = '';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('maintenanceModal');
            if (event.target === modal) {
                closeMaintenanceModal();
            }
        }
    </script>
</body>
</html>