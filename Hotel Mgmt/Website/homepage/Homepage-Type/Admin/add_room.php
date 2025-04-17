<?php
require_once('../../Website/inc/db_connect.php');

$room_num = '';
$r_type = '';
$r_status = '';
$r_rate = '';
$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['register_room'])) {
    $room_num = filter_input(INPUT_POST, 'room_num', FILTER_VALIDATE_INT);
    $r_type = filter_input(INPUT_POST, 'room_type', FILTER_SANITIZE_STRING);
    $r_status = filter_input(INPUT_POST, 'room_status', FILTER_SANITIZE_STRING);
    $r_rate = filter_input(INPUT_POST, 'rate_plan', FILTER_VALIDATE_FLOAT);

    if (!$room_num || empty($r_type) || empty($r_status) || $r_rate === false) {
        $message = "Please fill in all fields correctly.";
    } else {
        // Check if room number already exists
        $checkQuery = "SELECT COUNT(*) FROM rooms WHERE room_num = :room_num";
        $stmtCheck = $db->prepare($checkQuery);
        $stmtCheck->bindValue(':room_num', $room_num);
        $stmtCheck->execute();
        $exists = $stmtCheck->fetchColumn();

        if ($exists > 0) {
            $message = "That room number already exists.";
        } else {
            $insertQuery = "INSERT INTO rooms (room_num, room_type, room_status, rate_plan)
                            VALUES (:room_num, :r_type, :r_status, :r_rate)";
            $stmt = $db->prepare($insertQuery);
            $stmt->bindValue(':room_num', $room_num);
            $stmt->bindValue(':r_type', $r_type);
            $stmt->bindValue(':r_status', $r_status);
            $stmt->bindValue(':r_rate', $r_rate);

            if ($stmt->execute()) {
                $message = "Room added successfully!";
                $room_num = $r_type = $r_status = '';
                $r_rate = '';
            } else {
                $message = "Error adding room.";
            }
        }
    }
}
?>

<!-- Form styling for the Add Room form. This CSS block here doesn't affect menu buttons elsewhere -->
<div style="width: 50%; margin: 20px auto; background: #f5f5f5; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
    <h2>Add New Room</h2>

    <?php if (!empty($message)): ?>
        <div style="margin-top: 10px; padding: 10px; border-radius: 5px;
            background-color: <?php echo (strpos($message, 'successfully') !== false) ? '#d4edda' : '#f8d7da'; ?>;
            color: <?php echo (strpos($message, 'successfully') !== false) ? '#155724' : '#721c24'; ?>;
            border: 1px solid <?php echo (strpos($message, 'successfully') !== false) ? '#c3e6cb' : '#f5c6cb'; ?>;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="homepage.php">
        <input type="hidden" name="addroom" value="1">
        
        <label>Room Number:</label>
        <input type="number" name="room_num" value="<?php echo htmlspecialchars($room_num); ?>" required><br><br>

        <label>Room Type:</label>
        <select name="room_type" required>
            <option value="">-- Select Type --</option>
            <option value="Single" <?php echo ($r_type == 'Single') ? 'selected' : ''; ?>>Single</option>
            <option value="Double" <?php echo ($r_type == 'Double') ? 'selected' : ''; ?>>Double</option>
            <option value="Suite" <?php echo ($r_type == 'Suite') ? 'selected' : ''; ?>>Suite</option>
        </select><br><br>

        <label>Room Status:</label>
        <select name="room_status" required>
            <option value="">-- Select Status --</option>
            <option value="Available" <?php echo ($r_status == 'Available') ? 'selected' : ''; ?>>Available</option>
            <option value="Occupied" <?php echo ($r_status == 'Occupied') ? 'selected' : ''; ?>>Occupied</option>
            <option value="Maintenance" <?php echo ($r_status == 'Maintenance') ? 'selected' : ''; ?>>Maintenance</option>
        </select><br><br>

        <label>Rate Plan ($):</label>
        <input type="number" step="0.01" name="rate_plan" value="<?php echo htmlspecialchars($r_rate); ?>" required><br><br>

        <button type="submit" name="register_room" style="padding: 12px 24px; font-size: 16px;">Add Room</button>
    </form>

    <!-- Back button -->
    <div style="text-align: center; margin-top: 20px;">
        <button onclick="window.location.href='homepage.php?modroomy=0';" 
            style="padding: 12px 24px; font-size: 16px; background-color: #6c757d; border: none; color: white; border-radius: 6px; cursor: pointer;">
            Back to Modify Rooms
        </button>
    </div>
</div>
