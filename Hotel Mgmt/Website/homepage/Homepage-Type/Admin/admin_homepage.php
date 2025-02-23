<?php

    if (isset($_POST['register'])) {
        include('Homepage-Type/Admin/registerUser.php');
    }
	
	if (isset($_POST['night_audit'])) {
		echo "<script>window.open('../../Website/inc/night_audit.php', '_blank');</script>";
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
	?>


<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../inc/homepage_main.css">
</head>

<body>
    <h1>Admin Portal</h1>
	<div class="side-buttons-container">
	<div class="side-buttons-top">
    <form method="post">
        <button type="submit" name="register" id="side-buttons">Add New User</button>
    </form>
    
    <form method="post">
        <button type="submit" name="select" id="side-buttons">View Users</button>
    </form>
	
	<form method="post">
        <button type="submit" name="night_audit" id="side-buttons">Night Audit</button>
    </form>
	<br><br><br>
	
	<form method="post">
        <button type="submit" name="placeholder" id="side-buttons">Function 2</button>
    </form>
	
	<form method="post">
        <button type="submit" name="placeholder" id="side-buttons">Function 3</button>
    </form>
</body>
</html>
