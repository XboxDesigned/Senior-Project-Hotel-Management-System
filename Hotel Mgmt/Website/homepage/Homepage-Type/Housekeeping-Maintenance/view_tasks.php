<?php
require_once('../../Website/inc/db_connect.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check login
if (!isset($_SESSION['user']['user_id']) || !isset($_SESSION['user']['role'])) {
    header("Location: ../../Website/login.php");
    exit();
}

$user_id = $_SESSION['user']['user_id'];
$role = $_SESSION['user']['role'];

// Handle task status toggle via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id']) && isset($_POST['new_status'])) {
    $task_id = filter_input(INPUT_POST, 'update_id', FILTER_VALIDATE_INT);
    $new_status = $_POST['new_status'] === 'completed' ? 'completed' : 'in progress';

    if ($task_id) {
        if ($role === 'maintenance') {
            $queryUpdate = "UPDATE maintenance_tasks SET status = :new_status WHERE task_id = :task_id AND assigned_to = :user_id";
        } elseif ($role === 'housekeeper') {
            $queryUpdate = "UPDATE housekeeping_tasks SET status = :new_status WHERE task_id = :task_id AND assigned_to = :user_id";
        } else {
            die("Unauthorized access.");
        }

        $stmt = $db->prepare($queryUpdate);
        $stmt->bindValue(':new_status', $new_status, PDO::PARAM_STR);
        $stmt->bindValue(':task_id', $task_id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->closeCursor();
    }
}

// Fetch tasks
if ($role === 'maintenance') {
    $queryTasks = "SELECT task_id, room_num, task_description, status FROM maintenance_tasks WHERE assigned_to = :user_id";
} elseif ($role === 'housekeeper') {
    $queryTasks = "SELECT task_id, room_num, task_description, status FROM housekeeping_tasks WHERE assigned_to = :user_id";
} else {
    die("Unauthorized access.");
}

$stmt = $db->prepare($queryTasks);
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$tasks = $stmt->fetchAll();
$stmt->closeCursor();
?>
<link rel="stylesheet" type="text/css" href="../inc/homepage_main.css">
<style>

</style>

<br>

<?php if (empty($tasks)): ?>
    <div style="margin-left: 220px; color: black;">
        <h3>No tasks assigned to you at the moment.</h3>
    </div>
<?php else: ?>



<div class="table-container">
        <table border="1" id="rooms-table">
    <table>
        <tr>
            <th>Task ID</th>
            <th>Room Number</th>
            <th>Description</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php foreach ($tasks as $task): ?>
        <tr>
            <td><?php echo htmlspecialchars($task['task_id']); ?></td>
            <td><?php echo htmlspecialchars($task['room_num']); ?></td>
            <td><?php echo htmlspecialchars($task['task_description']); ?></td>
            <td><?php echo htmlspecialchars($task['status']); ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="update_id" value="<?php echo $task['task_id']; ?>">
                    <input type="hidden" name="new_status" value="<?php echo $task['status'] === 'completed' ? 'in progress' : 'completed'; ?>">
                    <button class="task-btn" type="submit">
                        <?php echo $task['status'] === 'completed' ? 'Mark In Progress' : 'Mark Completed'; ?>
                    </button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
