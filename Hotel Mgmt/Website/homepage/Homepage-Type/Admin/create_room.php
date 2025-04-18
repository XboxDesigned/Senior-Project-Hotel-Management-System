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

    </style>
</head>
<body>
    <h2>Room Management</h2>

    <div class="centered-content">
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
            <button class="room-btn" type="submit" name="add_room">Add Room</button>
        </form>
    </div>

</body>
</html>