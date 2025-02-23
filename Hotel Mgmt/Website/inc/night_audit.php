<?php
// Include database connection
include('../inc/db_connect.php');

if (!isset($db)) {
    die("Database connection not established.");
}

// Run Night Audit when button is clicked
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $query = "CALL night_audit()";
    $statement = $db->prepare($query);
    $statement->execute();
    $statement->closeCursor();
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
$query = "SELECT task_id, room_num, task_description, status FROM housekeeping_tasks WHERE status = 'pending'";
$statement = $db->prepare($query);
$statement->execute();
$housekeeping = $statement->fetchAll(PDO::FETCH_ASSOC);
$statement->closeCursor();
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
    </style>
    <script>
        function printReport() {
            window.print();
        }
    </script>
</head>
<body>

    <h2>Night Audit - End of Day Report</h2>
    
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
        <tr><th>Task ID</th><th>Room ID</th><th>Description</th><th>Status</th></tr>
        <?php foreach ($housekeeping as $row) { ?>
            <tr><td><?= htmlspecialchars($row["task_id"]) ?></td><td><?= htmlspecialchars($row["room_num"]) ?></td><td><?= htmlspecialchars($row["task_description"]) ?></td><td><?= htmlspecialchars($row["status"]) ?></td></tr>
        <?php } ?>
    </table>

    <button onclick="printReport()">Print Report</button>

</body>
</html>