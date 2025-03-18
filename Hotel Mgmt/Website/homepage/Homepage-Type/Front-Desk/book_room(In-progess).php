<?php
require_once('../../Website/inc/db_connect.php');

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) 
{
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

// Check if the logout button is clicked
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

$name = $_SESSION['user']['username'];
$role = $_SESSION['user']['role'];

// Initialize error and success message
$error_message = '';
$success_message = '';

if (isset($_POST['submit_booking'])) {
	
    // Get guest details
    $firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';
    $lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';
    $emailAddress = isset($_POST['emailAddress']) ? trim($_POST['emailAddress']) : '';
    $contactNum = isset($_POST['contactNum']) ? trim($_POST['contactNum']) : '';
    
    // Get booking details
    $num_guests = isset($_POST['num_guests']) ? $_POST['num_guests'] : '';
    $checkin_date = isset($_POST['checkin_date']) ? $_POST['checkin_date'] : '';
    $checkout_date = isset($_POST['checkout_date']) ? $_POST['checkout_date'] : '';
    
    // Check for empty fields
    if (empty($firstName) || empty($lastName) || empty($emailAddress) || empty($contactNum) || 
        empty($num_guests) || empty($checkin_date) || empty($checkout_date)) {
        $error_message = 'Please provide all required information.';
    } else {
        // Only execute this if all fields are filled
        try {
            // Check if guest exists
            $statement = $db->prepare("SELECT guest_id FROM guests 
                                      WHERE email_address = :emailAddress");
            $statement->execute([':emailAddress' => $emailAddress]);
            $guest = $statement->fetch(PDO::FETCH_ASSOC);
            
            if ($guest) {
                $guest_id = $guest['guest_id'];
            } else {
				
                // Insert new guest
                $statement = $db->prepare("INSERT INTO guests (first_name, last_name, contact_num, email_address) 
                                          VALUES (:firstName, :lastName, :contactNum, :emailAddress)");
                $statement->execute([
                    ':firstName' => $firstName,
                    ':lastName' => $lastName,
                    ':contactNum' => $contactNum,
                    ':emailAddress' => $emailAddress
                ]);
                $guest_id = $db->lastInsertId();
            }
            
            // Calculate nights and create booking
            $check_in = new DateTime($checkin_date);
            $check_out = new DateTime($checkout_date);
            $nights = $check_in->diff($check_out)->days;
            $confirmation_num = 'CNF' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            
            // Insert reservation 
            $statement = $db->prepare("INSERT INTO reservations (confirmation_num, guest_id, 
                                      checkin_date, checkout_date, nights, status) 
                                      VALUES (:confirmation_num, :guest_id, 
                                      :checkin_date, :checkout_date, :nights, 'confirmed')");
            
            $statement->execute([
                ':confirmation_num' => $confirmation_num,
                ':guest_id' => $guest_id,
                ':checkin_date' => $checkin_date,
                ':checkout_date' => $checkout_date,
                ':nights' => $nights
            ]);
            
            $success_message = "Booking successful! Confirmation number is: " . $confirmation_num;
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Room</title>
    <!-- Add Flatpickr CSS and JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
    <style>
        .flatpickr-day.selected {
            background-color: #b5a8e7 !important;
            border-color: #b5a8e7 !important;
            color: #fff !important;
        }
        .flatpickr-day:hover {
            background-color: #e2dbf7;
        }
    </style>
</head>
<body>
    <form class="book-room-form" method="post">
        <h1>BOOK ROOM</h1><br>
        
        <?php if (!empty($error_message)){ ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php } ?>
        <?php if (!empty($success_message)){ ?>
            <p class="success"><?php echo $success_message; ?></p>
        <?php } ?>
        
        <h3>Guest Information:</h3><br>
		
        <input type="text" name="firstName" placeholder="Enter First Name:" value="<?php echo isset($_POST['firstName']) ? htmlspecialchars($_POST['firstName']) : ''; ?>"><br>
		
        <input type="text" name="lastName" placeholder="Enter Last Name:" value="<?php echo isset($_POST['lastName']) ? htmlspecialchars($_POST['lastName']) : ''; ?>"><br>
        
        <input type="email" name="emailAddress" placeholder="Enter Email Address:" value="<?php echo isset($_POST['emailAddress']) ? htmlspecialchars($_POST['emailAddress']) : ''; ?>"><br>
        
        <input type="tel" name="contactNum" placeholder="Enter Phone Number:" value="<?php echo isset($_POST['contactNum']) ? htmlspecialchars($_POST['contactNum']) : ''; ?>"><br>
        
        <br>
        
        <h3>Booking Details:</h3><br>
        
        <select name="num_guests">
            <option value="" <?php echo !isset($_POST['num_guests']) ? 'selected' : ''; ?>>Select Number of Guests</option>
            <option value="1" <?php echo (isset($_POST['num_guests']) && $_POST['num_guests'] == '1') ? 'selected' : ''; ?>>1 Guest</option>
            <option value="2" <?php echo (isset($_POST['num_guests']) && $_POST['num_guests'] == '2') ? 'selected' : ''; ?>>2 Guests</option>
        </select><br>
        
        <input type="text" name="checkin_date" id="checkin_date" placeholder="Select Check-in Date" value="<?php echo isset($_POST['checkin_date']) ? htmlspecialchars($_POST['checkin_date']) : ''; ?>"><br>
        
        <input type="text" name="checkout_date" id="checkout_date" placeholder="Select Check-out Date" value="<?php echo isset($_POST['checkout_date']) ? htmlspecialchars($_POST['checkout_date']) : ''; ?>"><br>
        
        <button type="submit" name="submit_booking">Book Room</button>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkInPicker = flatpickr("#checkin_date", {
            minDate: "today",
            dateFormat: "Y-m-d",
            onChange: function(selectedDates) {
                if (selectedDates[0]) {
                    // Set minimum date for checkout to day after checkin
                    checkOutPicker.set('minDate', selectedDates[0].fp_incr(1));
                    // Clear checkout date when checkin changes
                    checkOutPicker.clear();
                }
            }
        });

        const checkOutPicker = flatpickr("#checkout_date", {
            minDate: "today",
            dateFormat: "Y-m-d"
        });
        
        const checkInValue = document.getElementById('checkin_date').value;
        const checkOutValue = document.getElementById('checkout_date').value;
        
        if (checkInValue) {
            checkInPicker.setDate(checkInValue);
            
            if (checkOutValue) {
                checkOutPicker.set('minDate', new Date(checkInValue).fp_incr(1));
                checkOutPicker.setDate(checkOutValue);
            }
        }
    });
    </script>
</body>
</html>