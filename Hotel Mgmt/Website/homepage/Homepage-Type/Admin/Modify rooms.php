<?php
session_start();

// Use absolute path because relative path not working
include('C:/xampp/htdocs/Hotel Mgmt/Website/inc/db_connect.php');


// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../../../login.php');
    exit();
}

if (!isset($db) || !$db instanceof PDO) {
    $error = "Database connection failed. Check db_connect.php path or configuration.";
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update Room
    if (isset($_POST['update_room'])) {
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

    // Add Room
    if (isset($_POST['add_room'])) {
        $room_num = $_POST['new_room_num'];
        $room_type = $_POST['new_room_type'];
        $room_status = $_POST['new_room_status'];
        $rate_plan = floatval($_POST['new_rate_plan']);

        $sql = "INSERT INTO rooms (room_num, room_type, room_status, rate_plan) VALUES (:room_num, :room_type, :room_status, :rate_plan)";
        $stmt = $db->prepare($sql);
        try {
            $stmt->execute([
                ':room_num' => $room_num,
                ':room_type' => $room_type,
                ':room_status' => $room_status,
                ':rate_plan' => $rate_plan
            ]);
            $message = "Room $room_num added successfully!";
        } catch (PDOException $e) {
            $error = "Error adding room: " . $e->getMessage();
        }
    }

    // Delete Room
    if (isset($_POST['delete_room'])) {
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
}

// Fetch all rooms
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
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:hover { background-color: #f5f5f5; }
        .error { color: red; font-weight: bold; }
        .message { color: green; font-weight: bold; }
        button { padding: 5px 10px; margin: 5px; }
        .delete-btn { background-color: #ff4444; color: white; border: none; cursor: pointer; }
        .delete-btn:hover { background-color: #cc0000; }
        .form-section { margin-top: 20px; }
        select, input[type="number"], input[type="text"] { padding: 5px; width: 150px; }
        .add-form { background-color: #f9f9f9; padding: 10px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <h2>Room Management</h2>

    <?php 
    if (isset($error)) { echo "<p class='error'>$error</p>"; }
    if (isset($message)) { echo "<p class='message'>$message</p>"; }
    ?>

    <!-- Room List -->
    <table>
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
                                <button type="submit" name="update_room">Update</button>
                                <button type="submit" name="delete_room" class="delete-btn" onclick="return confirm('Are you sure you want to delete Room <?php echo $room['room_num']; ?>?');">Delete</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Add New Room Form -->
    <div class="form-section">
        <h3>Add New Room</h3>
        <form method="POST" action="" class="add-form">
            <label>Room Number: <input type="number" name="new_room_num" required></label><br>
            <label>Room Type: 
                <select name="new_room_type" required>
                    <option value="Single">Single</option>
                    <option value="Double">Double</option>
                    <option value="Suite">Suite</option>
                </select>
            </label><br>
            <label>Status: 
                <select name="new_room_status" required>
                    <option value="available">Available</option>
                    <option value="occupied">Occupied</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="dirty">Dirty</option>
                </select>
            </label><br>
            <label>Rate Plan ($): <input type="number" step="0.01" name="new_rate_plan" required></label><br>
            <button type="submit" name="add_room">Add Room</button>
        </form>
    </div>

    <p><a href="./admin_homepage.php">Back to Admin Homepage</a></p>
</body>
</html>