<?php
if (isset($_POST['home'])) {
  include('Homepage-Type/Front-Desk/page_template.php');
}

if (isset($_POST['rooms'])) {
  include('Homepage-Type/Front-Desk/page_template.php');
}

if (isset($_POST['cancellations'])) {
  include('Homepage-Type/Front-Desk/page_template.php');
}

if (isset($_POST['guests'])) {
  include('Homepage-Type/Front-Desk/guests.php');
}

if (isset($_POST['maintenance'])) {
  include('Homepage-Type/Front-Desk/page_template.php');
}

if (isset($_POST['night_audit'])) {
  echo "<script>window.open('../../Website/inc/night_audit.php', '_blank');</script>";
}

if (isset($_POST['book_room'])) {
  include('Homepage-Type/Front-Desk/book_room.php');
}

if (isset($_POST['check_in'])) {
  include('Homepage-Type/Front-Desk/check_in.php');
}

if (isset($_POST['check_out'])) {
  include('Homepage-Type/Front-Desk/check_out.php');
}

?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../inc/homepage_main.css">
</head>

<body>
    <h1>Front Desk Portal</h1>
	
    <div class="side-buttons-container">
	<div class="side-buttons-top">
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
        <button type="submit" name="maintenance" id="side-buttons">Maintenance</button>
    </form>
	
    <form method="post">
        <button type="submit" name="night_audit" id="side-buttons">Night Audit</button>
    </form>
	
	<form method="post">
        <button type="submit" name="book_room" id="side-buttons">Book Room</button>
    </form>
	
	<form method="post">
        <button type="submit" name="check_in" id="side-buttons">Check In</button>
    </form>
	
	<form method="post">
        <button type="submit" name="check_out" id="side-buttons">Check Out</button>
    </form>
	</div>
	
	
</body>
</html>