<?php
require_once('../../Website/inc/db_connect.php');

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) 
{
    session_start();
}

if (!isset($_SESSION['user'])) 
{
    header('Location: ../../../login.php'); // Redirect to the login if not logged in
    exit();
}

// Check if the logout button is clicked
if (isset($_POST['logout'])) 
{
    // Destroy the session
    session_destroy();

    // Redirect the user to the login page
    header("Location: ../login.php");
    exit();
}
	$name = $_SESSION['user']['username'];
	$role = $_SESSION['user']['role'];
	
//	Initialize error and success message
$error_message = '';
$success_message = '';



// Get available rooms

$query = "SELECT room_num, room_type, rate_plan 
          FROM rooms 
          ORDER BY room_num";
$statement = $db->prepare($query);
$statement->execute();
$rooms = $statement->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['submit_booking'])) {
	//	Get guest details
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $emailAddress = $_POST['emailAddress'];
    $contactNum = $_POST['contactNum'];
	
	//	Get booking details
	$room_num = $_POST['room_num'];
    $num_guests = $_POST['num_guests'];
    $checkin_date = $_POST['checkin_date'];
    $checkout_date = $_POST['checkout_date'];
	
	//	Check for empty fields
	if (empty($firstName) || empty($lastName) || empty($emailAddress) || empty($contactNum) || 
		empty($room_num) || empty($num_guests) || empty($checkin_date) || empty($checkout_date)) {
		$error_message = 'Please provide more information.';
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
	
	//	Successful reservation
	if (empty($error_message)) {
		
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
		
			if ($statement->fetch()) {
				
				// Insert guest information
				$statement = $db->prepare("INSERT INTO guests (first_name, last_name, contact_num, email_address) 
										   VALUES (:firstName, :lastName, :contactNum, :emailAddress)");
				$statement->execute([':firstName' => $_POST['firstName'],
									 ':lastName' => $_POST['lastName'],
									 ':contactNum' => $_POST['contactNum'],
									 ':emailAddress' => $_POST['emailAddress']]);
        
				$guest_id = $db->lastInsertId();
        
				// Calculate nights
				$check_in = new DateTime($_POST['checkin_date']);
				$check_out = new DateTime($_POST['checkout_date']);
				$nights = $check_in->diff($check_out)->days;
				$confirmation_num = 'CNF' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
	
				// Get rate plan based on selected room
				$room_num = $_POST['room_num'];
				
				$query = "SELECT rate_plan FROM rooms WHERE room_num = :room_num";
				$statement = $db->prepare($query);
				$statement->execute([':room_num' => $room_num]);
				$room = $statement->fetch(PDO::FETCH_ASSOC);
    
				// Calculate balance
				$balance = $room['rate_plan'] * $nights;
    
				// Insert reservation
				$statement = $db->prepare("INSERT INTO reservations (confirmation_num, guest_id, room_num, 
										   checkin_date, checkout_date, nights, status, balance) 
										   VALUES (:confirmation_num, :guest_id, :room_num, 
										  :checkin_date, :checkout_date, :nights, 'confirmed', :balance)");
							   
        
				$statement->execute([':confirmation_num' => $confirmation_num,
									 ':guest_id' => $guest_id,
									 ':room_num' => $_POST['room_num'],
									 ':checkin_date' => $_POST['checkin_date'],
									 ':checkout_date' => $_POST['checkout_date'],
									 ':nights' => $nights,
									 ':balance' => $balance]);
		
				// Update reservation status to checked-in
				$updateReservationStatus = $db->prepare("UPDATE reservations 
														 SET status = 'checked-in' 
														 WHERE confirmation_num = :confirmation_num ");
				$updateReservationStatus->execute([':confirmation_num' => $_POST['confirmation_num']]);
    
				$success_message = "Booking successful! Confirmation number is: " . $confirmation_num;
		}
	
	// Guest not checked in
	else {
		$error_message = 'Guest not checked in.';
	}
	}
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>Book Room</title>


</head>

<body>

</div>
	<form class="book-room-form" method="post">
	<h1>BOOK ROOM</h1><br>
	
	<?php if (!empty($error_message)){ ?>
			<p class="error"><?php echo $error_message; ?></p>
	<?php } ?>
	<?php if (!empty($success_message)){ ?>
			<p class="success"><?php echo $success_message; ?></p>
	<?php } ?>
	
	<h3>Guest Information:</h3><br>
		<label>First Name</label>
		<input type="text" name="firstName" placeholder="Enter First Name:"><br>
		
		<label>Last Name</label>
		<input type="text" name="lastName" placeholder="Enter Last Name:"><br>
		
		<label>Email Address</label>
		<input type="username" name="emailAddress" placeholder="Enter Email Address:"><br>
		
		<label>Phone Number</label>
		<input type="tel" name="contactNum" placeholder="Enter Phone Number:"><br>
		
		<br>
		
	<h3>Booking Details:</h3><br>
        <label>Assign Room:</label>
        <select name="room_num">
            <option value="">Select Room</option>
            <?php foreach ($rooms as $room): ?>
                <option value="<?php echo ($room['room_num']); ?>">
                    Room <?php echo ($room['room_num']); ?> - 
                    <?php echo ($room['room_type']); ?> - $
					<?php echo ($room['rate_plan']); ?>/night
                </option>
            <?php endforeach; ?>
        </select><br>
        
        <input type="hidden" name="room_rate" id="room_rate" value="">
        
        <label>Number of Guests:</label>
        <select name="num_guests">
            <option value="1">1 Guest</option>
            <option value="2">2 Guests</option>
        </select><br>
        
        <label>Check In Date:</label>
        <input type="text" name="checkin_date" id="checkin_date" placeholder="Select Check-in Date"><br>
        
        <label>Check Out Date:</label>
        <input type="text" name="checkout_date" id="checkout_date" placeholder="Select Check-out Date"><br>
        
        <button type="submit" name="submit_booking">Book Room</button>
		
		<script>
		
		// document.addEventListener('DOMContentLoaded', function() {
			// const roomSelect = document.querySelector('select[name="room_num"]');
			// let checkInPicker, checkOutPicker;
			// let unavailableDates = [];

			Initialize date pickers
			// checkInPicker = flatpickr("#checkin_date", {
				// minDate: "today",
				// disable: unavailableDates,
				// onChange: function(selectedDates) {
					// if (selectedDates[0]) {
						Set minimum date for checkout to day after checkin
						// checkOutPicker.set('minDate', selectedDates[0].fp_incr(1));
						Clear checkout date when check in changes
						// checkOutPicker.clear();
					// }
				// }
			// });

			// checkOutPicker = flatpickr("#checkout_date", {
				// minDate: "today",
				// disable: unavailableDates
			// });

			// roomSelect.addEventListener('change', async function() {
				// const selectedRoom = this.value;
					// if (selectedRoom) {
						// try {
							// const response = await fetch(`check_availability.php?room_num=${selectedRoom}`);
							// const reservations = await response.json();
                    
							Convert reservations to disabled date ranges
							// unavailableDates = reservations.map(res => ({
								// from: new Date(res.checkin_date),
								// to: new Date(res.checkout_date)
							// }));

							Update both date pickers with new disabled dates
							// checkInPicker.set('disable', unavailableDates);
							// checkOutPicker.set('disable', unavailableDates);
                    
							Clear any selected dates
							// checkInPicker.clear();
							// checkOutPicker.clear();
						// } 
					
					// catch (error) {
						// console.error('Error fetching unavailable dates:', error);
					// }
					// }
			// });
		// });
		// </script>
	
</body>
</html>