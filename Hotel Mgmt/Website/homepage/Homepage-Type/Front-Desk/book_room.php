<?php
require_once('../../Website/inc/db_connect.php');

$name = $_SESSION['user']['username'];
$role = $_SESSION['user']['role'];

// Initialize error and success message
$error_message = '';
$success_message = '';


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_room']) && isset($_POST['submit_val'])) {
    // Get guest details
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $emailAddress = $_POST['emailAddress'];
    $contactNum = $_POST['contactNum'];
    
    // Get booking details
    $room_num = null;
    $num_guests = $_POST['num_guests'];
    $checkin_date = $_POST['checkin_date'];
    $checkout_date = $_POST['checkout_date'];
    
    // Check for empty fields
    if (empty($firstName) || empty($lastName) || empty($emailAddress) || empty($contactNum) || empty($num_guests) || empty($checkin_date) || empty($checkout_date)) {
        $error_message = 'Please fill in all required fields.';
    }
    
    if (empty($error_message)) {
        $checkAvailability = $db->prepare("SELECT * FROM reservations 
                                       WHERE room_num = :room_num 
                                       AND status IN ('confirmed', 'checked-in')
                                       AND (
                                         (checkin_date <= :check_in AND checkout_date > :check_in)
                                         OR 
                                         (checkin_date < :check_out AND checkout_date >= :check_out)
                                         OR 
                                         (checkin_date >= :check_in AND checkout_date <= :check_out)
                                     )");
        
        $checkAvailability->execute([
            ':room_num' => $room_num,
            ':check_in' => $checkin_date,
            ':check_out' => $checkout_date
        ]);
        
        if ($checkAvailability->fetch()) {
            $error_message = 'This room is not available for the selected dates.';
        }
    }
    
    // Check if guest already exists in reservations
    if (empty($error_message)) {
        $statement = $db->prepare("SELECT r.* FROM reservations r 
                               JOIN guests g ON r.guest_id = g.guest_id 
                               WHERE g.email_address = :email
                               AND r.status IN ('confirmed', 'checked-in')");
        $statement->execute([':email' => $emailAddress]);
        
        if ($statement->fetch()) {
            $error_message = 'Guest already has an active reservation.';
        }
    }
    
    // Successful reservation
    if (empty($error_message)) {
        try {
            $db->beginTransaction();
            
            // Check if guest exists
            $statement = $db->prepare('SELECT guest_id FROM guests WHERE first_name = :firstName
                                     AND last_name = :lastName
                                     AND contact_num = :contactNum
                                     AND email_address = :emailAddress');
            $statement->bindValue(':firstName', $firstName);
            $statement->bindValue(':lastName', $lastName);
            $statement->bindValue(':contactNum', $contactNum);
            $statement->bindValue(':emailAddress', $emailAddress);
            $statement->execute();
            
            $guestExists = $statement->fetch();
            
            if (!$guestExists) {
                // Insert guest information if they don't exist
                $statement = $db->prepare("INSERT INTO guests (first_name, last_name, contact_num, email_address) 
                                       VALUES (:firstName, :lastName, :contactNum, :emailAddress)");
                $statement->execute([
                    ':firstName' => $firstName,
                    ':lastName' => $lastName,
                    ':contactNum' => $contactNum,
                    ':emailAddress' => $emailAddress
                ]);
                $guest_id = $db->lastInsertId();
            } else {
                $guest_id = $guestExists['guest_id'];
            }
            
            // Calculate nights
            $check_in = new DateTime($checkin_date);
            $check_out = new DateTime($checkout_date);
            $nights = $check_in->diff($check_out)->days;
            $confirmation_num = 'CNF' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

            // Calculate balance
            $balance = 0;

            // Insert reservation
            $statement = $db->prepare("INSERT INTO reservations (confirmation_num, guest_id, room_num, 
                                   checkin_date, checkout_date, nights, status, balance) 
                                   VALUES (:confirmation_num, :guest_id, :room_num, 
                                  :checkin_date, :checkout_date, :nights, 'confirmed', :balance)");
            
            $statement->execute([
                ':confirmation_num' => $confirmation_num,
                ':guest_id' => $guest_id,
                ':room_num' => $room_num,
                ':checkin_date' => $checkin_date,
                ':checkout_date' => $checkout_date,
                ':nights' => $nights,
                ':balance' => $balance
            ]);

            $db->commit();
            $success_message = "Booking successful! Confirmation number is: " . $confirmation_num;
            
            // Clear form data on success
            $_POST = array();
        } catch (PDOException $e) {
            $db->rollBack();
            $error_message = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="'../../Website/inc/homepage_main.css'">
    <title>Book Room</title>
</head>

<body>
    <form class="book-room-form" method="post" id="booking-form">
        <h2>Book a Room</h2>
        <?php if (!empty($error_message)): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>
        
        <h3>Guest Information:</h3>
		
		<br>
        
        <label>First Name</label>
        <input type="text" name="firstName" placeholder="First Name" required
               value="<?php echo isset($_POST['firstName']) ? htmlspecialchars($_POST['firstName']) : ''; ?>">
       
        <label>Last Name</label>
        <input type="text" name="lastName" placeholder="Last Name" required
               value="<?php echo isset($_POST['lastName']) ? htmlspecialchars($_POST['lastName']) : ''; ?>">
        
		<br>
		
        <label>Email Address</label>
        <input type="email" name="emailAddress" placeholder="Email Address" required
               value="<?php echo isset($_POST['emailAddress']) ? htmlspecialchars($_POST['emailAddress']) : ''; ?>">
        
		<br>
		
        <label>Phone Number</label>
        <input type="tel" name="contactNum" placeholder="Phone Number" required
               value="<?php echo isset($_POST['contactNum']) ? htmlspecialchars($_POST['contactNum']) : ''; ?>">
        
		<br>
		
        <label>Number of Guests:</label>
        <select name="num_guests" required>
            <option value="1" <?php echo (isset($_POST['num_guests']) && $_POST['num_guests'] == '1') ? 'selected' : '' ?>>1 Guest</option>
            <option value="2" <?php echo (isset($_POST['num_guests']) && $_POST['num_guests'] == '2') ? 'selected' : '' ?>>2 Guests</option>
        </select>
       
	   <br>
	   <br>
        
        <label>Check In Date:</label>
        <input type="date" name="checkin_date" id="checkin_date" placeholder="Select Check-in Date" required
               value="<?php echo isset($_POST['checkin_date']) ? htmlspecialchars($_POST['checkin_date']) : ''; ?>">
        
        <label>Check Out Date:</label>
        <input type="date" name="checkout_date" id="checkout_date" placeholder="Select Check-out Date" required
               value="<?php echo isset($_POST['checkout_date']) ? htmlspecialchars($_POST['checkout_date']) : ''; ?>">
        
		<br>
		
        <button type="submit" name="book_room" class="book-button">Book Room</button>
		<input type="hidden" name="submit_val"> 
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set min date for checkin
            document.getElementById('checkin_date').min = new Date().toISOString().split('T')[0];
            
            // Update checkout date min when checkin date changes
            document.getElementById('checkin_date').addEventListener('change', function() {
                document.getElementById('checkout_date').min = this.value;
            });
            
            // Clear form on success
            <?php if (!empty($success_message)): ?>
                document.getElementById('booking-form').reset();
            <?php endif; ?>
        });
    </script>
</body>
</html>