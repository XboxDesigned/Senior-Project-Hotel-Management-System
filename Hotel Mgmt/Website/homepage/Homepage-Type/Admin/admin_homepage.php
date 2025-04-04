<?php

    

    if (isset($_POST['register'])) {
        include('Homepage-Type/Admin/registerUser.php');
    }
	
	if (isset($_POST['select'])) {
        include('Homepage-Type/Admin/selectUsers.php');
    }

    if (isset($_POST['placeholder'])) {
        include('Homepage-Type/Admin/page_template.php');
    }
    
	// Check if update_id is set, then include updateUsers.php
	if (isset($_GET['update_id'])) {
		include('Homepage-Type/admin/updateUsers.php');
	}
	
if (isset($_POST['home'])) {
  include('../../Website/inc/dashboard.php');
}

else if (isset($_POST['rooms'])) {
  include('Homepage-Type/Front-Desk/rooms.php');
}

else if (isset($_POST['cancellations'])) {
  include('Homepage-Type/Front-Desk/cancellations.php');
}

else if (isset($_POST['guests'])) {
  include('Homepage-Type/Front-Desk/guests.php');
}

else if (isset($_POST['maintenance'])) {
  include('Homepage-Type/Front-Desk/page_template.php');
}

else if (isset($_POST['housekeeping'])) {
  include('Homepage-Type/Front-Desk/page_template.php');
}

else if (isset($_POST['modify'])) {
  include('Homepage-Type/Front-Desk/modify_reservation.php');
}

else if (isset($_POST['night_audit'])) {
	include('Homepage-Type/Front-Desk/night_audit.php');
  echo "<script>window.open('../../Website/inc/night_audit.php', '_blank');</script>";
}

else if (isset($_POST['book_room'])) {
  include('Homepage-Type/Front-Desk/book_room.php');
}

else if (isset($_POST['check_in'])) {
  include('Homepage-Type/Front-Desk/check_in.php');
}

else if (isset($_POST['check_out'])) {
  include('Homepage-Type/Front-Desk/check_out.php');
}

else
{
}
?>



<!DOCTYPE html>
<html>
<head>
   
</head>

<body>

	<div class="side-buttons-container">
	<div class="side-buttons-top">
    <form method="post">
        <button type="submit" name="register" id="side-buttons">Add New User</button>
    </form>
    
    <form method="post">
        <button type="submit" name="select" id="side-buttons">View Users</button>
		
    </form>
	<form method="post">
        <button type="submit" name="home" id="side-buttons">Home</button>
    </form>
	
	<form method="post">
        <button type="submit" name="rooms" id="side-buttons">Rooms</button>
    </form>
	
	<form method="post">
        <button type="submit" name="cancellations" id="side-buttons">Cancellations</button>
    </form>
	
	<form method="post">
        <button type="submit" name="guests" id="side-buttons">Guests</button>
    </form>
	</div>
	

	<div class="side-buttons-bottom">
	
	<form method="post">
        <button type="submit" name="check_in" id="side-buttons">Check In</button>
    </form>
	
	<form method="post">
        <button type="submit" name="check_out" id="side-buttons">Check Out</button>
    </form>

	<form method="post">
        <button type="submit" name="modify" id="side-buttons">Modify Reservation</button>
    </form>

	<form method="post">
        <button type="submit" name="maintenance" id="side-buttons">Maintenance</button>
    </form>
	
	<form method="post">
        <button type="submit" name="housekeeping" id="side-buttons">Housekeeping</button>
    </form>
	
    <form method="post">
        <button type="submit" name="night_audit" id="side-buttons">Night Audit</button>
    </form>
	</div>
	
	
</body>
</html>
