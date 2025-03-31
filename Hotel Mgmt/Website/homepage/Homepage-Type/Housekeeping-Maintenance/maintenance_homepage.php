<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle page switching
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
    <style>
        .main-content {
            margin-left: 230px;
            color: black;
        }

        h1 {
            color: black;
        }
    </style>
</head>
<body>
    <h1>Maintenance Portal</h1>
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
    if (!isset($_SESSION['maintenance_view']) || $_SESSION['maintenance_view'] === 'home') {
        echo '<div class="main-content"><h1>Welcome to the Maintenance Portal</h1></div>';
    } elseif ($_SESSION['maintenance_view'] === 'view_tasks') {
        include('view_tasks.php');
    }
    ?>
</body>
</html>
