<?php
require_once('../../Website/inc/db_connect.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$room_num = filter_input(INPUT_GET, 'delete_room_id', FILTER_VALIDATE_INT);
$room = null;

// Fetch room info to show on confirmation
if ($room_num) {
    $query = "
        SELECT room_num, room_type, room_status, rate_plan
        FROM rooms
        WHERE room_num = :room_num
    ";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':room_num', $room_num);
    $stmt->execute();
    $room = $stmt->fetch();
    $stmt->closeCursor();
}

// Handle deletion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['confirm_delete'])) {
    $room_num = filter_input(INPUT_POST, 'room_num', FILTER_VALIDATE_INT);

    // First delete any related maintenance tasks
    $stmtDeleteTasks = $db->prepare("DELETE FROM maintenance_tasks WHERE room_num = :room_num");
    $stmtDeleteTasks->bindValue(':room_num', $room_num);
    $stmtDeleteTasks->execute();
    $stmtDeleteTasks->closeCursor();

    // Then delete the room itself
    $stmtDeleteRoom = $db->prepare("DELETE FROM rooms WHERE room_num = :room_num");
    $stmtDeleteRoom->bindValue(':room_num', $room_num);
    $stmtDeleteRoom->execute();
    $stmtDeleteRoom->closeCursor();

    // Redirect back to modify room view
    header("Location: homepage.php?modroomy=0");
    exit();
}
?>

<style>
.form-container {
    width: 50%;
    max-width: 500px;
    margin: 50px auto 50px 230px;
    padding: 20px;
    background-color: #fcebea;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(255, 0, 0, 0.1);
}

h3 {
    text-align: center;
    color: #b00020;
}

p {
    text-align: center;
    font-size: 18px;
}

.form-container form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.form-container input[type="submit"],
.form-container button {
    width: 100%;
    padding: 12px 16px;
    font-size: 16px;
    cursor: pointer;
    border: none;
    border-radius: 5px;
    color: white;
    box-sizing: border-box;
}

.form-container input[type="submit"] {
    background-color: #dc3545;
}

.form-container input[type="submit"]:hover {
    background-color: #c82333;
}

.form-container button {
    background-color: #6c757d;
}

.form-container button:hover {
    background-color: #5a6268;
}

@media (max-width: 900px) {
    .form-container {
        margin-left: auto;
        margin-right: auto;
        width: 80%;
    }
}
</style>

<?php if ($room): ?>
<div class="form-container">
    <h3>Confirm Delete</h3>
    <p>Are you sure you want to delete Room #<?php echo htmlspecialchars($room['room_num']); ?> (<?php echo htmlspecialchars($room['room_type']); ?>)?</p>
    <form method="post">
        <input type="hidden" name="room_num" value="<?php echo htmlspecialchars($room['room_num']); ?>">
        <input type="submit" name="confirm_delete" value="Yes, Delete Room">
    </form>
    <button onclick="window.location.href='homepage.php?modroomy=0';">Cancel</button>
</div>
<?php else: ?>
<p style="color:red; text-align:center;">Room not found.</p>
<?php endif; ?>
