<?php
//NOT COMPLETE

// Handle page switching
if (isset($_POST['home'])) {
    header('maintenance_homepage.php');
}else if (isset($_POST['view_tasks'])) {
    include('view_tasks.php');
}

?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../../inc/homepage_main.css">
    <style>
        /* Ensure the main content is not covered by the menu */
        .main-content {
            margin-left: 230px; /* Adjust based on menu width */
            
            color: black; /* Ensure text is readable */
        }

        h1 {
            color: black; /* Ensure title is visible */
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

            <!-- View Tasks Button -->
            <form method="post">
                <button type="submit" name="view_tasks" id="side-buttons">View Tasks</button>
            </form>
        </div>
    </div>

    <!-- Default Content -->
    <?php if (!isset($_POST['view_tasks'])): ?>
        <div class="main-content">
            <h1>Welcome to the Maintenance Portal</h1>
        </div>
    <?php endif; ?>
</body>
</html>
