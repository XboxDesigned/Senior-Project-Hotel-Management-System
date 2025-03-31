<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle page switching and store in session
if (isset($_POST['home'])) {
    $_SESSION['housekeeping_view'] = 'home';
} elseif (isset($_POST['view_tasks'])) {
    $_SESSION['housekeeping_view'] = 'view_tasks';
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
    <h1>Housekeeping Portal</h1>
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
    if (!isset($_SESSION['housekeeping_view']) || $_SESSION['housekeeping_view'] === 'home') {
        echo '<div class="main-content"><h1>Welcome to the Housekeeping Portal</h1></div>';
    } elseif ($_SESSION['housekeeping_view'] === 'view_tasks') {
        include('view_tasks.php');
    }
    ?>
</body>
</html>
