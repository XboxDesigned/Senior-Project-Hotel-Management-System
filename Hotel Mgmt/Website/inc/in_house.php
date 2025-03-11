<?php
session_start();
include('../inc/db_connect.php');

if (!isset($db) || !$db instanceof PDO) {
    $error = "Database connection failed. Check db_connect.php.";
}

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['username'])) {
    $error = "You must be logged in to access this page.";
    header('Location: ../login.php');
    exit();
}

$logged_in_username = $_SESSION['user']['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In-House Guests</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:hover { background-color: #f5f5f5; cursor: pointer; }
        .modal { display: <?php echo isset($_POST['show_guest']) ? 'block' : 'none'; ?>; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: white; margin: 5% auto; padding: 20px; width: 80%; max-width: 600px; }
        .charges-table, .payments-table, .form-section { margin-top: 20px; }
        button { margin: 5px; padding: 5px 10px; }
        .error { color: red; font-weight: bold; }
        .hidden-submit { display: none; }
        td { pointer-events: auto; }
        .delete-btn { background-color: #ff4444; color: white; border: none; cursor: pointer; }
        .delete-btn:hover { background-color: #cc0000; }
    </style>
    <script defer>
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.guest-row');
            rows.forEach(row => {
                row.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (e.target.tagName === 'BUTTON' || e.target.tagName === 'INPUT') {
                        return;
                    }
                    const confNum = this.getAttribute('data-conf-num');
                    const form = document.getElementById('form-' + confNum);
                    if (form) {
                        form.submit();
                    }
                });
            });
        });
    </script>
</head>
<body>
    <div class="in-house-tab">
        <h2>In-House Guests</h2>
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <table>
            <thead>
                <tr>
                    <th>Conf #</th>
                    <th>Guest Name</th>
                    <th>Contact</th>
                    <th>Room</th>
                    <th>Type</th>
                    <th>Check-In</th>
                    <th>Check-Out</th>
                    <th>Balance</th>
                    <th>Charges</th>
                    <th>Payments</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!isset($error)) {
                    $query = "
                        SELECT 
                            r.reservation_id,
                            r.confirmation_num,
                            g.guest_id,
                            g.first_name,
                            g.last_name,
                            g.contact_num,
                            g.email_address,
                            r.room_num,
                            rm.room_type,
                            rm.rate_plan,
                            r.checkin_date,
                            r.checkout_date,
                            r.status,
                            r.balance AS reservation_balance,
                            IFNULL(SUM(gc.amount), 0) AS total_charges,
                            IFNULL(SUM(i.payment_received), 0) AS total_payments
                        FROM reservations r
                        JOIN guests g ON r.guest_id = g.guest_id
                        JOIN rooms rm ON r.room_num = rm.room_num
                        LEFT JOIN guest_charges gc ON r.confirmation_num = gc.confirmation_num
                        LEFT JOIN invoices i ON r.confirmation_num = i.confirmation_num
                        WHERE r.status IN ('checked-in', 'active')
                        GROUP BY r.reservation_id, r.confirmation_num, g.guest_id, 
                            g.first_name, g.last_name, g.contact_num, g.email_address,
                            r.room_num, rm.room_type, rm.rate_plan,
                            r.checkin_date, r.checkout_date, r.status, r.balance";
                    
                    $statement = $db->prepare($query);
                    if (!$statement) {
                        echo "<tr><td colspan='10' class='error'>Failed to prepare query: " . implode(", ", $db->errorInfo()) . "</td></tr>";
                    } else {
                        $statement->execute();
                        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (empty($result)) {
                            echo "<tr><td colspan='10'>No in-house guests found.</td></tr>";
                        } else {
                            foreach ($result as $row) {
                                $conf_num = htmlspecialchars($row['confirmation_num']);
                                echo "<tr class='guest-row' data-conf-num='$conf_num'>";
                                echo "<td>";
                                echo "<form id='form-$conf_num' method='POST' style='margin:0;'>";
                                echo "<input type='hidden' name='confirmation_num' value='$conf_num'>";
                                echo "<input type='hidden' name='show_guest' value='true'>";
                                echo "<button type='submit' class='hidden-submit'>Show</button>";
                                echo "$conf_num";
                                echo "</form>";
                                echo "</td>";
                                echo "<td>{$row['first_name']} {$row['last_name']}</td>";
                                echo "<td>{$row['contact_num']}<br>{$row['email_address']}</td>";
                                echo "<td>{$row['room_num']}</td>";
                                echo "<td>{$row['room_type']}</td>";
                                echo "<td>{$row['checkin_date']}</td>";
                                echo "<td>{$row['checkout_date']}</td>";
                                echo "<td>$" . number_format($row['reservation_balance'], 2) . "</td>";
                                echo "<td>$" . number_format($row['total_charges'], 2) . "</td>";
                                echo "<td>$" . number_format($row['total_payments'], 2) . "</td>";
                                echo "</tr>";
                            }
                        }
                    }
                }
                ?>
            </tbody>
        </table>

        <!-- Guest Details Modal -->
        <div id="guestDetailsModal" class="modal">
            <div class="modal-content">
                <?php
                if (isset($_POST['show_guest']) && !isset($error)) {
                    $conf = $_POST['confirmation_num'];
                    $guest_query = "
                        SELECT 
                            r.confirmation_num,
                            g.first_name,
                            g.last_name,
                            g.contact_num,
                            g.email_address,
                            r.room_num,
                            rm.room_type,
                            r.checkin_date,
                            r.checkout_date,
                            r.balance AS reservation_balance
                        FROM reservations r
                        JOIN guests g ON r.guest_id = g.guest_id
                        JOIN rooms rm ON r.room_num = rm.room_num
                        WHERE r.confirmation_num = ?";
                    
                    $statement = $db->prepare($guest_query);
                    if (!$statement) {
                        echo "<p class='error'>Failed to prepare guest query: " . implode(", ", $db->errorInfo()) . "</p>";
                    } else {
                        $statement->execute([$conf]);
                        $guest_result = $statement->fetch(PDO::FETCH_ASSOC);

                        if ($guest_result) {
                            echo "<h3>Guest Details: {$guest_result['confirmation_num']}</h3>";
                            echo "<p><strong>Name:</strong> {$guest_result['first_name']} {$guest_result['last_name']}</p>";
                            echo "<p><strong>Contact:</strong> {$guest_result['contact_num']} | {$guest_result['email_address']}</p>";
                            echo "<p><strong>Room:</strong> {$guest_result['room_num']} ({$guest_result['room_type']})</p>";
                            echo "<p><strong>Check-In:</strong> {$guest_result['checkin_date']}</p>";
                            echo "<p><strong>Check-Out:</strong> {$guest_result['checkout_date']}</p>";
                            echo "<p><strong>Balance:</strong> $" . number_format($guest_result['reservation_balance'], 2) . "</p>";

                            $charges_query = "SELECT charge_id, description, amount, date_added AS date, username FROM guest_charges WHERE confirmation_num = ?";
                            $statement = $db->prepare($charges_query);
                            if (!$statement) {
                                echo "<p class='error'>Failed to prepare charges query: " . implode(", ", $db->errorInfo()) . "</p>";
                            } else {
                                $statement->execute([$conf]);
                                $charges_result = $statement->fetchAll(PDO::FETCH_ASSOC);
                                
                                echo "<div class='charges-table'>";
                                echo "<h4>Charges</h4>";
                                echo "<table id='chargesTable'>";
                                echo "<thead><tr><th>Description</th><th>Amount</th><th>Date</th><th>Added By</th><th>Action</th></tr></thead>";
                                echo "<tbody>";
                                if (empty($charges_result)) {
                                    echo "<tr><td colspan='5'>No charges found.</td></tr>";
                                } else {
                                    foreach ($charges_result as $charge) {
                                        echo "<tr>";
                                        echo "<td>{$charge['description']}</td>";
                                        echo "<td>$" . number_format($charge['amount'], 2) . "</td>";
                                        echo "<td>{$charge['date']}</td>";
                                        echo "<td>{$charge['username']}</td>";
                                        echo "<td>";
                                        echo "<form method='POST' style='display:inline;'>";
                                        echo "<input type='hidden' name='confirmation_num' value='$conf'>";
                                        echo "<input type='hidden' name='charge_id' value='{$charge['charge_id']}'>";
                                        echo "<button type='submit' name='remove_charge' class='delete-btn'>Remove</button>";
                                        echo "</form>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                }
                                echo "</tbody></table></div>";
                            }

                            $payments_query = "SELECT invoice_id, payment_received AS amount, date_added AS date, username FROM invoices WHERE confirmation_num = ?";
                            $statement = $db->prepare($payments_query);
                            if (!$statement) {
                                echo "<p class='error'>Failed to prepare payments query: " . implode(", ", $db->errorInfo()) . "</p>";
                            } else {
                                $statement->execute([$conf]);
                                $payments_result = $statement->fetchAll(PDO::FETCH_ASSOC);
                                
                                echo "<div class='payments-table'>";
                                echo "<h4>Payments</h4>";
                                echo "<table id='paymentsTable'>";
                                echo "<thead><tr><th>Amount</th><th>Date</th><th>Added By</th><th>Action</th></tr></thead>";
                                echo "<tbody>";
                                if (empty($payments_result)) {
                                    echo "<tr><td colspan='4'>No payments found.</td></tr>";
                                } else {
                                    foreach ($payments_result as $payment) {
                                        echo "<tr>";
                                        echo "<td>$" . number_format($payment['amount'], 2) . "</td>";
                                        echo "<td>{$payment['date']}</td>";
                                        echo "<td>{$payment['username']}</td>";
                                        echo "<td>";
                                        echo "<form method='POST' style='display:inline;'>";
                                        echo "<input type='hidden' name='confirmation_num' value='$conf'>";
                                        echo "<input type='hidden' name='invoice_id' value='{$payment['invoice_id']}'>";
                                        echo "<button type='submit' name='remove_payment' class='delete-btn'>Remove</button>";
                                        echo "</form>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                }
                                echo "</tbody></table></div>";
                            }
                        } else {
                            echo "<p class='error'>Guest not found.</p>";
                        }
                    }
                }
                ?>

                <div class="form-section">
                    <h4>Add Charge</h4>
                    <form method="POST" action="">
                        <input type="hidden" name="confirmation_num" value="<?php echo isset($conf) ? $conf : ''; ?>">
                        <input type="hidden" name="show_guest" value="true">
                        <label>Description: <input type="text" name="description" required></label><br>
                        <label>Amount: <input type="number" step="0.01" name="amount" required></label><br>
                        <button type="submit" name="add_charge">Add Charge</button>
                    </form>
                </div>

                <div class="form-section">
                    <h4>Add Payment</h4>
                    <form method="POST" action="">
                        <input type="hidden" name="confirmation_num" value="<?php echo isset($conf) ? $conf : ''; ?>">
                        <input type="hidden" name="show_guest" value="true">
                        <label>Amount: <input type="number" step="0.01" name="payment_amount" required></label><br>
                        <button type="submit" name="add_payment">Add Payment</button>
                    </form>
                </div>

                <form method="POST" action="">
                    <button type="submit" name="back_to_list">Back</button>
                    <button type="submit" name="close_modal">Close</button>
                </form>
            </div>
        </div>
    </div>

    <?php
    // Add Charge
    if (isset($_POST['add_charge']) && !isset($error)) {
        $confirmation_num = $_POST['confirmation_num'];
        $description = $_POST['description'];
        $amount = $_POST['amount'];
        $username = $logged_in_username;

        $guest_query = "SELECT guest_id FROM reservations WHERE confirmation_num = ?";
        $statement = $db->prepare($guest_query);
        if (!$statement) {
            echo "<p class='error'>Failed to prepare guest query for charge: " . implode(", ", $db->errorInfo()) . "</p>";
        } else {
            $statement->execute([$confirmation_num]);
            $guest_id = $statement->fetch(PDO::FETCH_ASSOC)['guest_id'];

            if ($guest_id) {
                $sql = "INSERT INTO guest_charges (guest_id, confirmation_num, description, amount, username, date_added) 
                        VALUES (?, ?, ?, ?, ?, NOW())";
                $statement = $db->prepare($sql);
                if (!$statement) {
                    echo "<p class='error'>Failed to prepare charge insertion: " . implode(", ", $db->errorInfo()) . "</p>";
                } else {
                    $statement->execute([$guest_id, $confirmation_num, $description, $amount, $username]);
                    // Refresh with POST
                    echo "<form id='refreshForm' method='POST' action='in_house.php'>";
                    echo "<input type='hidden' name='confirmation_num' value='$confirmation_num'>";
                    echo "<input type='hidden' name='show_guest' value='true'>";
                    echo "</form>";
                    echo "<script>document.getElementById('refreshForm').submit();</script>";
                }
            } else {
                echo "<p class='error'>Guest ID not found for charge.</p>";
            }
        }
    }

    // Add Payment
    if (isset($_POST['add_payment']) && !isset($error)) {
        $confirmation_num = $_POST['confirmation_num'];
        $payment_amount = $_POST['payment_amount'];
        $username = $logged_in_username;

        $guest_query = "SELECT guest_id FROM reservations WHERE confirmation_num = ?";
        $statement = $db->prepare($guest_query);
        if (!$statement) {
            echo "<p class='error'>Failed to prepare guest query for payment: " . implode(", ", $db->errorInfo()) . "</p>";
        } else {
            $statement->execute([$confirmation_num]);
            $guest_id = $statement->fetch(PDO::FETCH_ASSOC)['guest_id'];

            if ($guest_id) {
                $sql = "INSERT INTO invoices (guest_id, confirmation_num, total_amount, payment_received, username, date_added) 
                        VALUES (?, ?, ?, ?, ?, NOW())";
                $statement = $db->prepare($sql);
                if (!$statement) {
                    echo "<p class='error'>Failed to prepare invoice insertion: " . implode(", ", $db->errorInfo()) . "</p>";
                } else {
                    $statement->execute([$guest_id, $confirmation_num, $payment_amount, $payment_amount, $username]);
                    $update_sql = "UPDATE reservations SET balance = balance - ? WHERE confirmation_num = ?";
                    $statement = $db->prepare($update_sql);
                    if (!$statement) {
                        echo "<p class='error'>Failed to prepare balance update: " . implode(", ", $db->errorInfo()) . "</p>";
                    } else {
                        $statement->execute([$payment_amount, $confirmation_num]);
                        // Refresh with POST
                        echo "<form id='refreshForm' method='POST' action='in_house.php'>";
                        echo "<input type='hidden' name='confirmation_num' value='$confirmation_num'>";
                        echo "<input type='hidden' name='show_guest' value='true'>";
                        echo "</form>";
                        echo "<script>document.getElementById('refreshForm').submit();</script>";
                    }
                }
            } else {
                echo "<p class='error'>Guest ID not found for payment.</p>";
            }
        }
    }

    // Remove Charge
    if (isset($_POST['remove_charge']) && !isset($error)) {
        $confirmation_num = $_POST['confirmation_num'];
        $charge_id = $_POST['charge_id'];

        $sql = "DELETE FROM guest_charges WHERE charge_id = ? AND confirmation_num = ?";
        $statement = $db->prepare($sql);
        if (!$statement) {
            echo "<p class='error'>Failed to prepare charge deletion: " . implode(", ", $db->errorInfo()) . "</p>";
        } else {
            $statement->execute([$charge_id, $confirmation_num]);
            // Refresh with POST
            echo "<form id='refreshForm' method='POST' action='in_house.php'>";
            echo "<input type='hidden' name='confirmation_num' value='$confirmation_num'>";
            echo "<input type='hidden' name='show_guest' value='true'>";
            echo "</form>";
            echo "<script>document.getElementById('refreshForm').submit();</script>";
        }
    }

    // Remove Payment
    if (isset($_POST['remove_payment']) && !isset($error)) {
        $confirmation_num = $_POST['confirmation_num'];
        $invoice_id = $_POST['invoice_id'];

        $payment_query = "SELECT payment_received FROM invoices WHERE invoice_id = ? AND confirmation_num = ?";
        $statement = $db->prepare($payment_query);
        if ($statement) {
            $statement->execute([$invoice_id, $confirmation_num]);
            $payment = $statement->fetch(PDO::FETCH_ASSOC);
            $payment_amount = $payment['payment_received'];

            $sql = "DELETE FROM invoices WHERE invoice_id = ? AND confirmation_num = ?";
            $statement = $db->prepare($sql);
            if (!$statement) {
                echo "<p class='error'>Failed to prepare payment deletion: " . implode(", ", $db->errorInfo()) . "</p>";
            } else {
                $statement->execute([$invoice_id, $confirmation_num]);
                $update_sql = "UPDATE reservations SET balance = balance + ? WHERE confirmation_num = ?";
                $statement = $db->prepare($update_sql);
                if (!$statement) {
                    echo "<p class='error'>Failed to prepare balance update: " . implode(", ", $db->errorInfo()) . "</p>";
                } else {
                    $statement->execute([$payment_amount, $confirmation_num]);
                    // Refresh with POST
                    echo "<form id='refreshForm' method='POST' action='in_house.php'>";
                    echo "<input type='hidden' name='confirmation_num' value='$confirmation_num'>";
                    echo "<input type='hidden' name='show_guest' value='true'>";
                    echo "</form>";
                    echo "<script>document.getElementById('refreshForm').submit();</script>";
                }
            }
        } else {
            echo "<p class='error'>Failed to prepare payment query: " . implode(", ", $db->errorInfo()) . "</p>";
        }
    }

    // Handle Back and Close
    if (isset($_POST['back_to_list']) || isset($_POST['close_modal'])) {
        echo "<meta http-equiv='refresh' content='0;url=in_house.php'>";
    }
    ?>
</body>
</html>