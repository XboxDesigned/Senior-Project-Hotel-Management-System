<?php

require_once('../../Website/inc/db_connect.php');

$error_message = '';
$success_message = '';

try {

    $query = "
        SELECT g.guest_id, g.first_name, g.last_name, g.contact_num, g.email_address, r.status
        FROM guests g
        LEFT JOIN reservations r ON g.guest_id = r.guest_id
    ";
    $stmt = $db->query($query);
    $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        .hidden { display: none; }
    </style>
</head>
<body>
    <?php if (!empty($error_message)) { ?>
        <p class="error"><?php echo $error_message; ?></p>
    <?php } ?>
    <?php if (!empty($success_message)) { ?>
        <p class="success"><?php echo $success_message; ?></p>
    <?php } ?>

	<br><br>
    <div class="query-buttons-container">
	<br><br>
        <button onclick="showAllGuests()" id="query-buttons">All Guests</button>
		<br><br>
        <button onclick="showCheckedIn()" id="query-buttons">Checked In</button>
		<br>
        <button onclick="showCheckedOut()" id="query-buttons">Checked Out</button>
    </div>

    <div class="table">
        <table border="1" id="guests-table">
            <thead>
                <tr>
                    <th>Guest ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Contact Number</th>
                    <th>Email Address</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($guests)) { ?>
                    <?php foreach ($guests as $guest) { ?>
                        <tr data-status="<?php echo htmlspecialchars($guest['status']); ?>">
                            <td><?php echo htmlspecialchars($guest['guest_id']); ?></td>
                            <td><?php echo htmlspecialchars($guest['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($guest['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($guest['contact_num']); ?></td>
                            <td><?php echo htmlspecialchars($guest['email_address']); ?></td>
                            <td><?php echo htmlspecialchars($guest['status']); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="6">No guests found.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <script>
        function showAllGuests() {
            const rows = document.querySelectorAll('#guests-table tbody tr');
            rows.forEach(row => row.classList.remove('hidden'));
        }

        function showCheckedIn() {
            const rows = document.querySelectorAll('#guests-table tbody tr');
            rows.forEach(row => {
                if (row.getAttribute('data-status') === 'checked-in') {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        }

        function showCheckedOut() {
            const rows = document.querySelectorAll('#guests-table tbody tr');
            rows.forEach(row => {
                if (row.getAttribute('data-status') === 'checked-out') {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        }
    </script>
</body>
</html>