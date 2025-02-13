<?php
// Include database connection
include('../inc/db_connect.php');

if (!isset($db)) {
    die("Database connection not established.");
}

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Call the night_audit stored procedure
        $query = "CALL night_audit()";
        $statement = $db->prepare($query);
        $statement->execute();

        // First result set: Pending/In-Progress Rooms Report
        $pendingRooms = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Move to next result set: High Balance Report
        $statement->nextRowset();
        $highBalance = $statement->fetchAll(PDO::FETCH_ASSOC);

        $statement->closeCursor();
    }
} catch (PDOException $e) {
    echo "Error executing night audit: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Night Audit Results</title>
    <style>
        /* Optional styling for print */
        @media print {
            button, form { display: none; }
        }
    </style>
    <script>
        function printReport() {
            window.print();
        }
    </script>
</head>
<body>
    <h1>Night Audit Results</h1>
    <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
        <h2>Pending/In-Progress Rooms</h2>
        <?php if (!empty($pendingRooms)): ?>
            <table border="1" cellpadding="5" cellspacing="0">
                <thead>
                    <tr>
                        <th>Room Number</th>
                        <th>Status</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingRooms as $room): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($room['room_num']); ?></td>
                            <td><?php echo htmlspecialchars($room['status']); ?></td>
                            <td><?php echo htmlspecialchars($room['reason']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending or in-progress rooms.</p>
        <?php endif; ?>

        <h2>High Balance Report</h2>
        <?php if (!empty($highBalance)): ?>
            <table border="1" cellpadding="5" cellspacing="0">
                <thead>
                    <tr>
                        <th>Confirmation #</th>
                        <th>Guest ID</th>
                        <th>Guest Name</th>
                        <th>Room Number</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($highBalance as $reservation): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($reservation['confirmation_num']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['guest_id']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['first_name'] . ' ' . $reservation['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['room_num']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['balance']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No high balance reservations found.</p>
        <?php endif; ?>

        <!-- Print Report Button -->
        <button onclick="printReport()">Print Report</button>
    <?php endif; ?>

    <!-- Form to trigger the night audit -->
    <form method="post" action="">
        <button type="submit">Run Night Audit</button>
    </form>
</body>
</html>
