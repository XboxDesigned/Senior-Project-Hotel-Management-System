<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle page switching and store selected view in session
if (isset($_POST['home'])) {
    $_SESSION['maintenance_view'] = 'home';
} elseif (isset($_POST['view_tasks'])) {
    $_SESSION['maintenance_view'] = 'view_tasks';
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

    <?php
    // Include content based on session view
    if (!isset($_SESSION['maintenance_view']) || $_SESSION['maintenance_view'] === 'home') {
        include('../../Website/inc/maint_housekeep_dashboard.php');
    } elseif ($_SESSION['maintenance_view'] === 'view_tasks') {
        include('view_tasks.php');
    }
    ?>
</body>
</html>
