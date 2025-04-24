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

    // Handle task submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['maintenance'])) {
        $room_num = $_POST['room_num'] ?? '';
        $description = $_POST['description'] ?? '';
        $assigned_to = $_POST['assigned_to'] ?? '';

        if (empty($room_num) || empty($description) || empty($assigned_to)) {
            $error_message = 'Please fill in all fields.';
        } else {
            $db->beginTransaction();
            try {
                // Determine role of assigned user
                $role_stmt = $db->prepare("SELECT role FROM users WHERE u_id = :assigned_to");
                $role_stmt->bindParam(':assigned_to', $assigned_to, PDO::PARAM_INT);
                $role_stmt->execute();
                $assigned_role = $role_stmt->fetchColumn();

                if ($assigned_role === 'maintenance') {
                    $insert_query = "INSERT INTO maintenance_tasks (room_num, task_description, status, assigned_to) 
                                     VALUES (:room_num, :description, 'pending', :assigned_to)";
                } elseif ($assigned_role === 'housekeeper') {
                    $insert_query = "INSERT INTO housekeeping_tasks (room_num, task_description, status, assigned_to) 
                                     VALUES (:room_num, :description, 'pending', :assigned_to)";
                } else {
                    throw new Exception("Invalid user role.");
                }

                $stmt = $db->prepare($insert_query);
                $stmt->bindParam(':room_num', $room_num, PDO::PARAM_INT);
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                $stmt->bindParam(':assigned_to', $assigned_to, PDO::PARAM_INT);
                $stmt->execute();

                $stmt = $db->prepare("UPDATE rooms SET room_status = 'in-progress' WHERE room_num = :room_num");
                $stmt->bindParam(':room_num', $room_num, PDO::PARAM_INT);
                $stmt->execute();

                $db->commit();
                $success_message = 'Task submitted successfully.';

                $stmt = $db->query($query);
                $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $db->rollBack();
                $error_message = 'Task submission failed: ' . $e->getMessage();
            }
        }
    }

    // Fetch staff
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

    <?php if (!empty($error_message)) echo "<p style='color:red;'>".htmlspecialchars($error_message)."</p>"; ?>
    <?php if (!empty($success_message)) echo "<p style='color:green;'>".htmlspecialchars($success_message)."</p>"; ?>

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
                <?php if (!empty($rooms)) {
                    foreach ($rooms as $room) {
                        $status_class = 'status-' . strtolower($room['room_status']); ?>
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
                <?php } } else { ?>
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
                    <label for="description">Description for Room #<span id="display-room-num"></span>:</label><br>
                    <textarea id="description" name="description" rows="4" cols="50" required></textarea>
                </div>

                <div class="filter-buttons">
                    <button type="button" onclick="filterStaff('all')">All Staff</button>
                    <button type="button" onclick="filterStaff('maintenance')">Maintenance Only</button>
                    <button type="button" onclick="filterStaff('housekeeper')">Housekeeping Only</button>
                </div>

                <div class="form-group">
                    <label for="assigned_to">Assign To:</label><br>
                    <select id="assigned_to" name="assigned_to" required>
                        <option value="">-- Select Staff Member --</option>
                        <?php foreach ($all_staff as $staff) { ?>
                            <option value="<?php echo $staff['u_id']; ?>" data-role="<?php echo htmlspecialchars($staff['role']); ?>">
                                <?php echo htmlspecialchars($staff['username']) . " (" . htmlspecialchars($staff['role']) . ")"; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="button" onclick="closeMaintenanceModal()">Cancel</button>
                    <button type="submit">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAllRooms() {
            document.querySelectorAll('#rooms-table tbody tr').forEach(row => row.classList.remove('hidden'));
        }
        function showMaintenanceRooms() {
            document.querySelectorAll('#rooms-table tbody tr').forEach(row => {
                row.classList.toggle('hidden', row.getAttribute('data-status') !== 'maintenance');
            });
        }
        function showOccupiedRooms() {
            document.querySelectorAll('#rooms-table tbody tr').forEach(row => {
                row.classList.toggle('hidden', row.getAttribute('data-status') !== 'occupied');
            });
        }
        function showAvailableRooms() {
            document.querySelectorAll('#rooms-table tbody tr').forEach(row => {
                row.classList.toggle('hidden', row.getAttribute('data-status') !== 'available');
            });
        }
        function showSingleRooms() {
            document.querySelectorAll('#rooms-table tbody tr').forEach(row => {
                row.classList.toggle('hidden', row.getAttribute('data-type') !== 'Single');
            });
        }
        function showDoubleRooms() {
            document.querySelectorAll('#rooms-table tbody tr').forEach(row => {
                row.classList.toggle('hidden', row.getAttribute('data-type') !== 'Double');
            });
        }

        function filterStaff(role) {
            document.querySelectorAll('#assigned_to option').forEach(option => {
                const roleAttr = option.getAttribute('data-role');
                if (!roleAttr || role === 'all' || roleAttr === role) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            });
            document.getElementById('assigned_to').value = "";
        }

        function openMaintenanceModal(roomNum) {
            document.getElementById('modal-room-num').value = roomNum;
            document.getElementById('display-room-num').textContent = roomNum;
            document.getElementById('maintenanceModal').style.display = 'block';
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
