<?php
require_once('../../Website/inc/db_connect.php');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get the room number from the URL
$room_num = filter_input(INPUT_GET, 'update_room_id', FILTER_VALIDATE_INT);
$room = null;

// Fetch room data
if ($room_num) {
    $queryRoom = "
    SELECT
        room_num,
        room_type,
        room_status,
        rate_plan
    FROM
        rooms
    WHERE room_num = :room_num
    ";

    $stmtRoom = $db->prepare($queryRoom);
    $stmtRoom->bindValue(':room_num', $room_num);
    $stmtRoom->execute();
    $room = $stmtRoom->fetch();
    $stmtRoom->closeCursor();
}

// Handle update form
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_room'])) {
    $room_num = filter_input(INPUT_POST, 'room_num', FILTER_VALIDATE_INT);
    $room_type = filter_input(INPUT_POST, 'room_type', FILTER_SANITIZE_STRING);
    $room_status = filter_input(INPUT_POST, 'room_status', FILTER_SANITIZE_STRING);
    $rate_plan = filter_input(INPUT_POST, 'rate_plan', FILTER_VALIDATE_FLOAT);
    $error = "";

    if (!$room_num) $error .= "Invalid Room Number<br>";
    if (!$room_type) $error .= "Room Type required<br>";
    if (!$room_status) $error .= "Room Status required<br>";
    if ($rate_plan === false) $error .= "Invalid Rate Plan<br>";

    if ($error !== "") {
        echo "<div style='color:red;'>$error</div>";
    } else {
        $updateQuery = "
        UPDATE rooms
        SET room_type = :room_type, room_status = :room_status, rate_plan = :rate_plan
        WHERE room_num = :room_num
        ";
        $stmtUpdate = $db->prepare($updateQuery);
        $stmtUpdate->bindValue(':room_num', $room_num);
        $stmtUpdate->bindValue(':room_type', $room_type);
        $stmtUpdate->bindValue(':room_status', $room_status);
        $stmtUpdate->bindValue(':rate_plan', $rate_plan);
        $stmtUpdate->execute();
        $stmtUpdate->closeCursor();

        header("Location: homepage.php?modroomy=0");

        exit();
    }
}
?>

<style>
.form-container {
    width: 50%;
    max-width: 500px;
    margin: 50px auto 50px 230px;
    padding: 20px;
    background-color: #f8f8f8;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

@media (max-width: 900px) {
    .form-container {
        margin-left: auto;
        margin-right: auto;
        width: 80%;
    }
}

h3 { text-align: center; }
table { width: 100%; }
td { padding: 10px; }

input, select {
    width: 95%; 
    padding: 10px;
    font-size: 18px; 
    border: 1px solid #ccc;
    border-radius: 5px;
}

.form-container input[type="submit"], 
.form-container button {
    width: 100%;
    padding: 10px;
    font-size: 16px;
    margin-top: 10px;
    cursor: pointer;
    border: none;
    background-color: #007bff;
    color: white;
    border-radius: 5px;
}

.form-container button {
    background-color: #dc3545;
}

.form-container input[type="submit"]:hover {
    background-color: #0056b3;
}

.form-container button:hover {
    background-color: #c82333;
}
</style>

<?php if ($room): ?>
<div class="form-container">
    <h3>Update Room #<?php echo htmlspecialchars($room_num); ?></h3>
    <form method="post" id="update_room_form">
        <input type="hidden" name="room_num" value="<?php echo htmlspecialchars($room_num); ?>">
        <table>
            <tr>
                <th>Room Type:</th>
                <td>
                    <select name="room_type" required>
                        <option value="Single" <?php echo (strtolower($room["room_type"]) === "single") ? "selected" : ""; ?>>Single</option>
                        <option value="Double" <?php echo (strtolower($room["room_type"]) === "double") ? "selected" : ""; ?>>Double</option>
                        <option value="Suite" <?php echo (strtolower($room["room_type"]) === "suite") ? "selected" : ""; ?>>Suite</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Room Status:</th>
                <td>
                    <select name="room_status" required>
                        <option value="Available" <?php echo (strtolower($room["room_status"]) === "available") ? "selected" : ""; ?>>Available</option>
                        <option value="Occupied" <?php echo (strtolower($room["room_status"]) === "occupied") ? "selected" : ""; ?>>Occupied</option>
                        <option value="Maintenance" <?php echo (strtolower($room["room_status"]) === "maintenance") ? "selected" : ""; ?>>Maintenance</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Rate Plan ($):</th>
                <td><input type="number" step="0.01" name="rate_plan" value="<?php echo htmlspecialchars($room["rate_plan"]); ?>" required></td>
            </tr>
        </table>
        <br>
        <input type="submit" name="update_room" value="Update Room">
    </form>
    <button onclick="window.location.href='homepage.php?modroomy=0';">CANCEL</button>
</div>
<?php else: ?>
<p style="color:red;">Room not found.</p>
<?php endif; ?>
