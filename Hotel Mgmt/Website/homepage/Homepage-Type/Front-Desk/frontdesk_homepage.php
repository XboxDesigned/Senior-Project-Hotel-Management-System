<?php

//Redirect to check_in.php
if (isset($_POST['check_in'])) {
  header('Location: check_in.php');
  exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../inc/homepage_main.css">
</head>

<body>
    <h1>Front Desk Portal</h1>
	
    <form method="post">
        <button type="submit" name="admin_1">Front Desk Function 1</button>
    </form>
	<br>
	
	<form method="post">
        <button type="submit" name="admin_2">Front Desk Function 2</button>
    </form>
	<br>
	
	<form method="post">
        <button type="submit" name="check_in">Check In</button>
    </form>
	
	
</body>
</html>