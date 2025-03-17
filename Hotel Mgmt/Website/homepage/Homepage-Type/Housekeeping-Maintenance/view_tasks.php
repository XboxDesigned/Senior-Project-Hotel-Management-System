<?php
//NOT COMPLETE
require_once('../../Website/inc/db_connect.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user']['user_id']) || !isset($_SESSION['user']['role'])) {
    header("Location: ../../Website/login.php");
    exit();
}

$user_id = $_SESSION['user']['user_id'];
$role = $_SESSION['user']['role'];

// If a task update is requested via GET, update that task's status to 'completed'
if (isset($_GET['update_id'])) {
    $task_id = filter_input(INPUT_GET, 'update_id', FILTER_VALIDATE_INT);
    if ($task_id) {
        if ($role === 'maintenance') {
            $queryUpdate = "UPDATE maintenance_tasks SET status = 'completed' WHERE task_id = :task_id AND assigned_to = :user_id";
        } elseif ($role === 'housekeeper') {
            $queryUpdate = "UPDATE housekeeping_tasks SET status = 'completed' WHERE task_id = :task_id AND assigned_to = :user_id";
        } else {
            die("Unauthorized access.");
        }
        $stmt = $db->prepare($queryUpdate);
        $stmt->bindValue(':task_id', $task_id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->closeCursor();
        
       

        //exit();
    }
}

// Fetch tasks assigned to the user from the appropriate table
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
<!DOCTYPE HTML>
<html>
<head>
    <title>Tasks</title>
    <link rel="stylesheet" type="text/css" href="../inc/homepage_main.css">
    <style>
        /* Push the table to the right so it's not covered by the menu */
        table {
            margin-left: 220px;
            width: 80%;
            max-width: 1000px;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
        }
        @media (max-width: 900px) {
            table {
                width: 90%;
                margin-left: auto;
                margin-right: auto;
                overflow-x: auto;
                display: block;
            }
        }
    </style>
</head>
<main>
    
<body>
    <div id="data">
         <h3>Select an option:</h3>
    </div>
    <br>
    <table>
        <tr>
            <th>Task ID</th>
            <th>Room Number</th>
            <th>Description</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php foreach ($tasks as $task) : ?>
        <tr>
            <td><?php echo htmlspecialchars($task['task_id']); ?></td>
            <td><?php echo htmlspecialchars($task['room_num']); ?></td>
            <td><?php echo htmlspecialchars($task['task_description']); ?></td>
            <td><?php echo htmlspecialchars($task['status']); ?></td>
            <td>
                <?php if ($task['status'] !== 'completed'): ?>
                    <button>
                        <a href="?view_tasks=1&update_id=<?php echo $task['task_id']; ?>">Complete Task</a>
                    </button>
                <?php else: ?>
                    Completed
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</main>
</html>
