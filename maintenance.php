<?php
require_once('../../Website/inc/db_connect.php');

$error_message = '';
$success_message = '';

try {
    // Fetch all rooms
    $query = "SELECT room_num, room_type, room_status, rate_plan FROM rooms";
    $stmt = $db->query($query);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Handle maintenance request submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['maintenance']) && isset($_POST['submit_val'])) {
        $room_num = $_POST['room_num'];
        $description = $_POST['description'];
        $assigned_to = $_POST['assigned_to'];
        
        // Validate inputs
        if (empty($room_num) || empty($description) || empty($assigned_to)) {
            $error_message = 'Room number, description, and assigned staff are required.';
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
                $success_message = 'Maintenance request submitted successfully!';
                
                // Refresh room data
                $query = "SELECT room_num, room_type, room_status, rate_plan FROM rooms";
                $stmt = $db->query($query);
                $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $db->rollBack();
                $error_message = 'Database error: ' . $e->getMessage();
            }
        }
    }
    
    // Fetch only maintenance staff for assignment dropdown
    $staff_query = "SELECT u_id, username FROM users WHERE role = 'maintenance'";
    $staff_stmt = $db->query($staff_query);
    $maintenance_staff = $staff_stmt->fetchAll(PDO::FETCH_ASSOC);

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
       
    </style>
</head>
<body>
    <h1>Room Management</h1>
    
    <?php if (!empty($error_message)) { ?>
        <div class="error-message" style="color: red; padding: 10px; background-color: #ffebee; margin-bottom: 15px; border-radius: 4px;">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php } ?>
    
    <?php if (!empty($success_message)) { ?>
        <div class="success">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php } ?>

    <div class="query-buttons-container">

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
                                <?php if ($room['room_status'] !== 'maintenance') { ?>
                                    <button class="maintenance-btn" onclick="openMaintenanceModal('<?php echo $room['room_num']; ?>')">
                                        Request Maintenance
                                    </button>
                                <?php } else { ?>
                                    <span style="color: #e74c3c;">Maintenance in progress</span>
                                <?php } ?>
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

    <!-- Maintenance Request Modal -->
    <div id="maintenanceModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeMaintenanceModal()">&times;</span>
            <h2>Submit Maintenance Request</h2>
            <form method="post" action="">
                <input type="hidden" id="modal-room-num" name="room_num">
                <input type="hidden" name="maintenance_val" value="1">
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="4" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="assigned_to">Assign To:</label>
                    <select id="assigned_to" name="assigned_to" required>
                        <option value="">-- Select Maintenance Staff --</option>
                        <?php foreach ($maintenance_staff as $staff) { ?>
                            <option value="<?php echo $staff['u_id']; ?>">
                                <?php echo htmlspecialchars($staff['username']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" onclick="closeMaintenanceModal()" class="btn btn-cancel">Cancel</button>
					<input type="hidden" name="submit_val"> 
                    <button type="submit" name="maintenance" class="btn btn-submit">Submit Request</button>
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

        // Maintenance modal functions
        function openMaintenanceModal(roomNum) {
            document.getElementById('modal-room-num').value = roomNum;
            document.getElementById('maintenanceModal').style.display = 'block';
        }

        function closeMaintenanceModal() {
            document.getElementById('maintenanceModal').style.display = 'none';
            document.getElementById('description').value = '';
            document.getElementById('assigned_to').value = '';
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('maintenanceModal');
            if (event.target === modal) {
                closeMaintenanceModal();
            }
        }
    </script>
</body>
</html>