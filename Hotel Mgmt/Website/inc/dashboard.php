<?php
require_once('db_connect.php');

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) 
{
    session_start();
}

if (!isset($_SESSION['user'])) 
{
    header('Location: ../../../login.php'); // Redirect to the login if not logged in
    exit();
}

// Check if the logout button is clicked
if (isset($_POST['logout'])) 
{
    // Destroy the session
    session_destroy();

    // Redirect the user to the login page
    header("Location: ../login.php");
    exit();
}

//	Initialize error and success message
$error_message = '';
$success_message = '';

// Get the user's role
$role = $_SESSION['user']['role'] ?? 'unknown';

// Redirect based on role
if (isset($_POST['homepage'])) {
	if ($role === 'front_desk') {
		header('Location: ../homepage/homepage.php');
		exit();
	}
	
	elseif ($role === 'admin') {
		header('Location: ../homepage/Homepage-Type/Admin/admin_homepage.php');
		exit();
	}
}

if (isset($_POST['in_house'])) {
  header('Location: in_house.php');
}

if (isset($_POST['rooms'])) {
  header('Location: ../homepage/Homepage-Type/Front-Desk/rooms.php');
}

if (isset($_POST['charge_manager'])) {
  header('Location: charge_manager.php');
}

if (isset($_POST['book_room'])) {
    header('Location: ../homepage/Homepage-Type/Front-Desk/book_room.php');
  exit;
}

// Query to get available rooms count
$available_query = "SELECT COUNT(*) as available_count FROM rooms WHERE room_status = 'available'";
$available_result = $db->query($available_query);
$available_count = $available_result->fetch(PDO::FETCH_ASSOC)['available_count'];

// Query to get occupied rooms count
$occupied_query = "SELECT COUNT(*) as occupied_count FROM rooms WHERE room_status = 'occupied'";
$occupied_result = $db->query($occupied_query);
$occupied_count = $occupied_result->fetch(PDO::FETCH_ASSOC)['occupied_count'];

// Query to get total rooms count
$total_query = "SELECT COUNT(*) as total_count FROM rooms";
$total_result = $db->query($total_query);
$total_count = $total_result->fetch(PDO::FETCH_ASSOC)['total_count'];

// Query to get check-ins for today
$checkin_query = "SELECT COUNT(*) as checkin_count FROM reservations WHERE checkin_date = CURDATE() AND status = 'confirmed'";
$checkin_result = $db->query($checkin_query);
$checkin_count = $checkin_result->fetch(PDO::FETCH_ASSOC)['checkin_count'];

// Query to get check-outs for today
$checkout_query = "SELECT COUNT(*) as checkout_count FROM reservations WHERE checkout_date = CURDATE() AND status = 'checked-in'";
$checkout_result = $db->query($checkout_query);
$checkout_count = $checkout_result->fetch(PDO::FETCH_ASSOC)['checkout_count'];

// Query to get current guests
$current_guests_query = "SELECT COUNT(*) as guests_count FROM reservations WHERE status = 'checked-in'";
$current_guests_result = $db->query($current_guests_query);
$current_guests_count = $current_guests_result->fetch(PDO::FETCH_ASSOC)['guests_count'];

// Query to get dirty rooms
$dirty_query = "SELECT COUNT(*) as dirty_count FROM rooms WHERE room_status = 'dirty'";
$dirty_result = $db->query($dirty_query);
$dirty_count = $dirty_result->fetch(PDO::FETCH_ASSOC)['dirty_count'];

// Query to get maintenance rooms
$maintenance_query = "SELECT COUNT(*) as maintenance_count FROM rooms WHERE room_status = 'maintenance'";
$maintenance_result = $db->query($maintenance_query);
$maintenance_count = $maintenance_result->fetch(PDO::FETCH_ASSOC)['maintenance_count'];

// Query to get pending housekeeping and maintenance tasks
$pending_housekeeping_query = "SELECT COUNT(*) as housekeeping_count FROM housekeeping_tasks WHERE status = 'pending'";
$pending_housekeeping_result = $db->query($pending_housekeeping_query);
$housekeeping_count = $pending_housekeeping_result->fetch(PDO::FETCH_ASSOC)['housekeeping_count'];

$pending_maintenance_query = "SELECT COUNT(*) as maintenance_tasks_count FROM maintenance_tasks WHERE status = 'pending'";
$pending_maintenance_result = $db->query($pending_maintenance_query);
$maintenance_tasks_count = $pending_maintenance_result->fetch(PDO::FETCH_ASSOC)['maintenance_tasks_count'];

$pending_tasks_count = $housekeeping_count + $maintenance_tasks_count;

?>
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="homepage_main.css">
    <title>Hotel Management Dashboard</title>
    <style>

		.dashboard-container {
			margin-left: 15%;
			margin-top: 5%;
			padding: 20px;
			display: flex;
			flex-direction: column;
			gap: 30px;
		}


		
		.category-title {
			font-size: 18px;
			color: #333;
			margin: 0 0 10px;
			border-bottom: 2px solid #9887d5;
			padding-bottom: 5px;
            font-weight: bold;
		}


        .category-container {
            display: flex;
            flex-direction: column;
        }
        
        .card-row {
            display: flex;
            gap: 10px;
        }
        
        .card-group {
            display: flex;
            flex: 1;
        }

        .card {
			background-color: white;
			border-radius: 8px;
			box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
			padding: 15px;
			text-align: center;
			transition: transform 0.2s;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			position: relative;
			min-height: 120px;
            flex: 1;
		}

        .card:hover {
			transform: translateY(-5px);
			box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
		}
        
        .card-group .card {
            border-radius: 0;
            border-right: 1px solid #eee;
        }
        
        .card-group .card:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }
        
        .card-group .card:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
            border-right: none;
        }

		.card-title {
			font-size: 14px;
			color: #666;
			margin-top: 10px;
			font-weight: normal;
		}

		.card-number {
			font-size: 32px;
			font-weight: bold;
			color: #333;
		}

		.action-buttons {
			display: flex;
			justify-content: space-around;
			margin-top: 20px;
		}

		.action-button {
			background-color: #b5a8e7;
			color: white;
			border: none;
			border-radius: 4px;
			padding: 12px 25px;
			font-size: 16px;
			cursor: pointer;
			width: 200px;
			transition: background-color 0.3s;
		}

		.action-button:hover {
			background-color: #9887d5;
		}

        .date-display {
            background-color: white;
            border-radius: 8px;
            padding: 10px 20px;
            position: absolute;
            right: 20px;
            top: 15%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="date-display">
        <span id="current-date">Mon, Mar 10, 2025</span>
    </div>


<div class="dashboard-container">

	<!-- In House Category -->
    <div class="category-container">
        <div class="category-title">In House</div>
        <div class="card-row">
            <div class="card-group">
                <div class="card">
                    <div class="card-number"><?php echo $checkin_count; ?></div>
                    <div class="card-title">Arrivals</div>
                </div>
                <div class="card">
                    <div class="card-number"><?php echo $checkout_count; ?></div>
                    <div class="card-title">Departures</div>
                </div>
                <div class="card">
                    <div class="card-number"><?php echo $current_guests_count; ?></div>
                    <div class="card-title">Checked In</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Room Status Category -->
    <div class="category-container">
        <div class="category-title">Room Status</div>
        <div class="card-row">
            <div class="card-group">
                <div class="card">
                    <div class="card-number"><?php echo $available_count; ?></div>
                    <div class="card-title">Available Rooms</div>
                </div>
                <div class="card">
                    <div class="card-number"><?php echo $occupied_count; ?></div>
                    <div class="card-title">Occupied Rooms</div>
                </div>
                <div class="card">
                    <div class="card-number"><?php echo $total_count; ?></div>
                    <div class="card-title">Total Rooms</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Housekeeping Category -->
    <div class="category-container">
        <div class="category-title">Housekeeping & Maintenance</div>
        <div class="card-row">
            <div class="card-group">
                <div class="card">
                    <div class="card-number"><?php echo $dirty_count; ?></div>
                    <div class="card-title">Dirty Rooms</div>
                </div>
                <div class="card">
                    <div class="card-number"><?php echo $maintenance_count; ?></div>
                    <div class="card-title">Maintenance</div>
                </div>
                <div class="card">
                    <div class="card-number">
                    <?php
                    // Query to get number of active housekeeping tasks
                    $housekeeping_active_query = "SELECT COUNT(*) as active_count FROM housekeeping_tasks 
                                                WHERE status = 'in-progress'";
                    $housekeeping_active_result = $db->query($housekeeping_active_query);
                    $housekeeping_active_count = $housekeeping_active_result->fetch(PDO::FETCH_ASSOC)['active_count'];
                    echo $housekeeping_active_count;
                    ?>
                    </div>
                    <div class="card-title">Active Housekeeping</div>
                </div>
                <div class="card">
                    <div class="card-number"><?php echo $pending_tasks_count; ?></div>
                    <div class="card-title">Pending Tasks</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bookings & Revenue Category -->
    <div class="category-container">
        <div class="category-title">Bookings & Revenue</div>
        <div class="card-row">
            <div class="card-group">
                <div class="card">
                    <div class="card-number">
                    <?php
                    // Query to get number of reservations with confirmed status
                    $confirmed_query = "SELECT COUNT(*) as confirmed_count FROM reservations WHERE status = 'confirmed'";
                    $confirmed_result = $db->query($confirmed_query);
                    $confirmed_count = $confirmed_result->fetch(PDO::FETCH_ASSOC)['confirmed_count'];
                    echo $confirmed_count;
                    ?>
                    </div>
                    <div class="card-title">Confirmed Bookings</div>
                </div>
                <div class="card">
                    <div class="card-number">
                        <?php 
                        // Calculate occupancy percentage
                        $occupancy_percentage = ($occupied_count / $total_count) * 100;
                        echo round($occupancy_percentage) . "%"; 
                        ?>
                    </div>
                    <div class="card-title">Occupancy</div>
                </div>
                <div class="card">
                    <div class="card-number">
                    <?php
                    // Query to get revenue today
                    $revenue_query = "SELECT IFNULL(SUM(gc.amount), 0) as today_revenue 
                        FROM guest_charges gc
                        JOIN invoices i ON gc.confirmation_num = i.confirmation_num
                        WHERE DATE(i.created_at) = CURDATE()";
                    $revenue_result = $db->query($revenue_query);
                    $today_revenue = $revenue_result->fetch(PDO::FETCH_ASSOC)['today_revenue'];
                    echo '$' . number_format($today_revenue, 2);
                    ?>
                    </div>
                    <div class="card-title">Today's Revenue</div>
                </div>
            </div>
        </div>
    </div>
    
  

    <script>
        // Set current date
        const dateElement = document.getElementById('current-date');
        const options = { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' };
        dateElement.textContent = new Date().toLocaleDateString('en-US', options);
    </script>
</body>
</html>