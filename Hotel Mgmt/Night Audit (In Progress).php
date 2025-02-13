<?php
// Include database connection
include('../inc/db_connect.php');

if (!isset($db)) {
    die("Database connection not established.");
}

// Initialize variables to prevent warnings
$highBalanceGuests = [];
$checkedOutGuests = [];
$totalRevenue = 0.00;

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Call the night_audit stored procedure
        $query = "CALL night_audit()";
        $statement = $db->prepare($query);
        $statement->execute();

        // Fetch all results from the stored procedure
        do {
            $statement->fetchAll(PDO::FETCH_ASSOC);
        } while ($statement->nextRowset());

        // Close cursor before executing new queries
        $statement->closeCursor();
        
        // Fetch high balance guests
        $highBalanceQuery = "SELECT g.first_name, g.last_name, r.room_num, r.balance FROM reservations r 
                             JOIN guests g ON r.guest_id = g.guest_id 
                             WHERE r.balance > 500"; // Adjust threshold as needed
        $highBalanceStmt = $db->prepare($highBalanceQuery);
        $highBalanceStmt->execute();
        $highBalanceGuests = $highBalanceStmt->fetchAll(PDO::FETCH_ASSOC);
        $highBalanceStmt->closeCursor();
        
        // Fetch checked-out guests
        $checkoutQuery = "SELECT g.first_name, g.last_name, r.room_num, r.checkout_datetime FROM reservations r 
                          JOIN guests g ON r.guest_id = g.guest_id 
                          WHERE r.checkout_datetime IS NOT NULL";
        $checkoutStmt = $db->prepare($checkoutQuery);
        $checkoutStmt->execute();
        $checkedOutGuests = $checkoutStmt->fetchAll(PDO::FETCH_ASSOC);
        $checkoutStmt->closeCursor();
        
        // Fetch total revenue from rooms
        $revenueQuery = "SELECT SUM(r.rate_plan) AS total_revenue FROM reservations res 
                         JOIN rooms r ON res.room_num = r.room_num 
                         WHERE res.checkout_datetime IS NOT NULL";
        $revenueStmt = $db->prepare($revenueQuery);
        $revenueStmt->execute();
        $totalRevenue = $revenueStmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0.00;
        $revenueStmt->closeCursor();
    }
} catch (PDOException $e) {
    echo "Error executing night audit: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Night Audit Results</title>
    <script>
        function printPage() {
            window.print();
        }
    </script>
</head>
<body>
    <h2>Night Audit Results</h2>
    
    <form method="POST">
        <button type="submit">Run Night Audit</button>
    </form>
    
    <h3>High Balance Guests</h3>
    <table border="1">
        <tr>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Room Number</th>
            <th>Balance</th>
        </tr>
        <?php foreach ($highBalanceGuests as $guest): ?>
            <tr>
                <td><?= htmlspecialchars($guest['first_name']) ?></td>
                <td><?= htmlspecialchars($guest['last_name']) ?></td>
                <td><?= htmlspecialchars($guest['room_num']) ?></td>
                <td>$<?= number_format($guest['balance'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    
    <h3>Checked-Out Guests</h3>
    <table border="1">
        <tr>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Room Number</th>
            <th>Checkout Time</th>
        </tr>
        <?php foreach ($checkedOutGuests as $guest): ?>
            <tr>
                <td><?= htmlspecialchars($guest['first_name']) ?></td>
                <td><?= htmlspecialchars($guest['last_name']) ?></td>
                <td><?= htmlspecialchars($guest['room_num']) ?></td>
                <td><?= htmlspecialchars($guest['checkout_datetime']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    
    <h3>Total Room Revenue</h3>
    <p><strong>$<?= number_format($totalRevenue, 2) ?></strong></p>
    
    <button onclick="printPage()">Print Report</button>
</body>
</html>
