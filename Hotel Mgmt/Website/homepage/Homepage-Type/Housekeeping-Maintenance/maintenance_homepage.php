<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle page switching
if (isset($_POST['home'])) {
    include('../../Website/inc/maint_housekeep_dashboard.php');
}

elseif (isset($_POST['view_tasks'])) {
	include('view_tasks.php'); 
}

else {
    include('../../Website/inc/maint_housekeep_dashboard.php');
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../../inc/homepage_main.css">
</head>
<body>
    <div class="side-buttons-container">
        <div class="side-buttons-top">
            <form method="post">
                <button type="submit" name="home" id="side-buttons">Home</button>
            </form>
            <form method="post">
                <button type="submit" name="view_tasks" id="side-buttons">View Tasks</button>
            </form>
        </div>
    </div>
</body>
</html>