<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../inc/homepage_main.css">
</head>

<body>
    <h1>Admin Portal</h1>

    <?php

    if (isset($_POST['register'])) {
        include('Homepage-Type/Admin/registerUser.php');
    }
	
	if (isset($_POST['night_audit'])) {
        header('Homepage-Type/Admin/night_audit.php');
    }
	
	if (isset($_POST['placeholder'])) {
        include('Homepage-Type/Admin/page_template.php');
    }
    ?>

    <form method="post">
        <button type="submit" name="register">Add New User</button>
    </form>
    <br>
    
    <form method="post">
        <button type="submit" name="placeholder">Function 1</button>
    </form>
	<br>
	
	<form method="post">
        <button type="submit" name="placeholder">Function 2</button>
    </form>
	<br>
	
	<form method="post">
        <button type="submit" name="placeholder">Function 3</button>
    </form>
    <br>
</body>
</html>
