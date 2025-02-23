<?php
require_once('../../Website/inc/db_connect.php');

?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../inc/homepage_main.css">
    <title>Guests</title>
</head>
<body>

    <div class="centered-content">
        <h1>Guests</h1>
		<br><br><br>
		
        <table>
            <tr>
                <th>Guest ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Contact Number</th>
                <th>Email Address</th>
            </tr>

            <?php
            try {
                $query = "SELECT guest_id, first_name, last_name, contact_num, email_address FROM guests";
                $stmt = $db->prepare($query);
                $stmt->execute();

                $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($guests) > 0) {
                    foreach ($guests as $row) {
                        echo "<tr>
                                <td>{$row['guest_id']}</td>
                                <td>{$row['first_name']}</td>
                                <td>{$row['last_name']}</td>
                                <td>{$row['contact_num']}</td>
                                <td>{$row['email_address']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No guests found.</td></tr>";
                }
            } catch (PDOException $e) {
                echo "<tr><td colspan='5'>Error: " . $e->getMessage() . "</td></tr>";
            }

            ?>
        </table>
    </div>

</body>
</html>
