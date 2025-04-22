<?php
// Include database connection
include('../inc/db_connect.php');

if (!isset($db)) {
    die("Database connection not established.");
}

// Initialize message variable
$audit_message = "";

// Check if night audit has already run today
$query = "SELECT run_date FROM night_audit_log WHERE run_date = CURDATE() LIMIT 1";
$statement = $db->prepare($query);
$statement->execute();
$audit_ran = $statement->fetch(PDO::FETCH_ASSOC);
$statement->closeCursor();

// Run Night Audit when button is clicked
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$audit_ran) {
    $query = "CALL night_audit()";
    $statement = $db->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    $statement->closeCursor();
    
    // Check if the procedure returned a message
    if (!empty($result) && isset($result[0]['message'])) {
        $audit_message = $result[0]['message'];
    } else {
        $audit_message = "Night audit completed successfully.";
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && $audit_ran) {
    $audit_message = "Night audit already ran today.";
}

// Fetch Checked-Out Guests
$query = "SELECT confirmation_num, guest_id, room_num, checkout_date FROM reservations WHERE checkout_date = CURDATE() AND status = 'checked-out'";
$statement = $db->prepare($query);
$statement->execute();
$checkouts = $statement->fetchAll(PDO::FETCH_ASSOC);
$statement->closeCursor();

// Fetch No-Show Reservations
$query = "SELECT confirmation_num, guest_id, room_num, checkin_date FROM reservations WHERE checkin_date = CURDATE() AND status = 'no-show'";
$statement = $db->prepare($query);
$statement->execute();
$noshow = $statement->fetchAll(PDO::FETCH_ASSOC);
$statement->closeCursor();

// Fetch Daily Revenue
$query = "SELECT SUM(balance) AS daily_revenue FROM reservations WHERE checkout_date = CURDATE() AND status = 'checked-out'";
$statement = $db->prepare($query);
$statement->execute();
$revenue = $statement->fetch(PDO::FETCH_ASSOC);
$statement->closeCursor();

// Fetch Pending Housekeeping Tasks
$hk_query = "SELECT task_id, room_num, task_description, status, 'housekeeping' AS task_type 
             FROM housekeeping_tasks WHERE status = 'pending'";
$hk_statement = $db->prepare($hk_query);
$hk_statement->execute();
$hk_tasks = $hk_statement->fetchAll(PDO::FETCH_ASSOC);
$hk_statement->closeCursor();

// Fetch Pending Maintenance Tasks
$maint_query = "SELECT task_id, room_num, task_description, status, 'maintenance' AS task_type 
                FROM maintenance_tasks WHERE status = 'pending'";
$maint_statement = $db->prepare($maint_query);
$maint_statement->execute();
$maint_tasks = $maint_statement->fetchAll(PDO::FETCH_ASSOC);
$maint_statement->closeCursor();

// Merge tasks
$pending_tasks = array_merge($hk_tasks, $maint_tasks);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Night Audit - End of Day Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid black; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        button { padding: 10px 15px; background: blue; color: white; border: none; cursor: pointer; margin: 10px 0; }
        button:hover { background: darkblue; }
        .message { color: red; font-weight: bold; }
    </style>
    <script>
        function printReport() {
            window.print();
        }
    </script>
</head>
<body>

    <h2>Night Audit - End of Day Report</h2>
    
    <?php if ($audit_message) { ?>
        <p class="message"><?= htmlspecialchars($audit_message) ?></p>
    <?php } ?>
    
    <form method="POST">
        <button type="submit">Run Night Audit</button>
    </form>

    <h3>Checked-Out Guests</h3>
    <table>
        <tr><th>Reservation ID</th><th>Guest ID</th><th>Room ID</th><th>Check-Out Date</th></tr>
        <?php foreach ($checkouts as $row) { ?>
            <tr><td><?= htmlspecialchars($row["confirmation_num"]) ?></td><td><?= htmlspecialchars($row["guest_id"]) ?></td><td><?= htmlspecialchars($row["room_num"]) ?></td><td><?= htmlspecialchars($row["checkout_date"]) ?></td></tr>
        <?php } ?>
    </table>

    <h3>No-Show Reservations</h3>
    <table>
        <tr><th>Confirmation Number</th><th>Guest ID</th><th>Room ID</th><th>Check-In Date</th></tr>
        <?php foreach ($noshow as $row) { ?>
            <tr><td><?= htmlspecialchars($row["confirmation_num"]) ?></td><td><?= htmlspecialchars($row["guest_id"]) ?></td><td><?= htmlspecialchars($row["room_num"]) ?></td><td><?= htmlspecialchars($row["checkin_date"]) ?></td></tr>
        <?php } ?>
    </table>

    <h3>Daily Revenue</h3>
    <p>Total Revenue: <strong>$<?= number_format($revenue["daily_revenue"], 2) ?></strong></p>

    <h3>Pending Housekeeping / Maintenance Tasks</h3>
    <table>
        <tr><th>Task ID</th><th>Room ID</th><th>Description</th><th>Status</th><th>Task Type</th></tr>
        <?php foreach ($pending_tasks as $row) { ?>
            <tr>
                <td><?= htmlspecialchars($row["task_id"]) ?></td>
                <td><?= htmlspecialchars($row["room_num"]) ?></td>
                <td><?= htmlspecialchars($row["task_description"]) ?></td>
                <td><?= htmlspecialchars($row["status"]) ?></td>
                <td><?= htmlspecialchars($row["task_type"]) ?></td>
            </tr>
        <?php } ?>
    </table>

    <button onclick="printReport()">Print Report</button>

</body>
</html>