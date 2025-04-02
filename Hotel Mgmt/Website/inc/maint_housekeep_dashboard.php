<?php
require_once('../inc/db_connect.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current user info
$current_user = isset($_SESSION['username']) ? $_SESSION['username'] : 'Unknown User';
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'Unknown Role';

// Redirect based on role
if (isset($_POST['homepage'])) {
	if ($role === 'housekeeper') {
		header('Location: ../homepage/homepage.php');
		exit();
	}
	
	elseif ($role === 'maintenance') {
		header('Location: ../homepage/Homepage-Type/Admin/admin_homepage.php');
		exit();
	}
}

// Get counts for summary cards
// Room status counts
$room_status_query = "SELECT room_status, COUNT(*) as count FROM rooms GROUP BY room_status";
$room_status_result = $db->query($room_status_query);
$room_status_counts = [];
while ($row = $room_status_result->fetch(PDO::FETCH_ASSOC)) {
    $room_status_counts[$row['room_status']] = $row['count'];
}

// Housekeeping task counts
$hk_task_query = "SELECT status, COUNT(*) as count FROM housekeeping_tasks GROUP BY status";
$hk_task_result = $db->query($hk_task_query);
$hk_task_counts = [];
while ($row = $hk_task_result->fetch(PDO::FETCH_ASSOC)) {
    $hk_task_counts[$row['status']] = $row['count'];
}

// Maintenance task counts
$maint_task_query = "SELECT status, COUNT(*) as count FROM maintenance_tasks GROUP BY status";
$maint_task_result = $db->query($maint_task_query);
$maint_task_counts = [];
while ($row = $maint_task_result->fetch(PDO::FETCH_ASSOC)) {
    $maint_task_counts[$row['status']] = $row['count'];
}

// Get latest tasks for the current user based on role
$user_id_query = "SELECT u_id FROM users WHERE username = :username";
$stmt = $db->prepare($user_id_query);
$stmt->execute([':username' => $current_user]);
$user_id = $stmt->fetch(PDO::FETCH_ASSOC)['u_id'] ?? 0;

// Get latest tasks based on role
$latest_tasks = [];
if ($user_role == 'housekeeper') {
    $latest_tasks_query = "SELECT 'housekeeping' as task_type, task_id, room_num, task_description, status 
                          FROM housekeeping_tasks 
                          WHERE assigned_to = :user_id AND status != 'completed' 
                          ORDER BY status = 'in-progress' DESC, task_id DESC 
                          LIMIT 5";
    $stmt = $db->prepare($latest_tasks_query);
    $stmt->execute([':user_id' => $user_id]);
    $latest_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($user_role == 'maintenance') {
    $latest_tasks_query = "SELECT 'maintenance' as task_type, task_id, room_num, task_description, status 
                          FROM maintenance_tasks 
                          WHERE assigned_to = :user_id AND status != 'completed' 
                          ORDER BY status = 'in-progress' DESC, created_at DESC 
                          LIMIT 5";
    $stmt = $db->prepare($latest_tasks_query);
    $stmt->execute([':user_id' => $user_id]);
    $latest_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // For admin or other roles, show combined most recent tasks
    $latest_hk_query = "SELECT 'housekeeping' as task_type, task_id, room_num, task_description, status 
                        FROM housekeeping_tasks 
                        WHERE status != 'completed' 
                        ORDER BY status = 'in-progress' DESC, task_id DESC 
                        LIMIT 3";
    $latest_hk_result = $db->query($latest_hk_query);
    $latest_tasks = $latest_hk_result->fetchAll(PDO::FETCH_ASSOC);

    $latest_maint_query = "SELECT 'maintenance' as task_type, task_id, room_num, task_description, status 
                          FROM maintenance_tasks 
                          WHERE status != 'completed' 
                          ORDER BY status = 'in-progress' DESC, created_at DESC 
                          LIMIT 3";
    $latest_maint_result = $db->query($latest_maint_query);
    $latest_tasks = array_merge($latest_tasks, $latest_maint_result->fetchAll(PDO::FETCH_ASSOC));
}

// Get rooms that need attention 
$attention_rooms_query = "SELECT room_num, room_type, room_status 
                         FROM rooms 
                         WHERE room_status IN ('pending', 'maintenance') 
                         ORDER BY room_status, room_num 
                         LIMIT 10";
$attention_rooms_result = $db->query($attention_rooms_query);
$attention_rooms = $attention_rooms_result->fetchAll(PDO::FETCH_ASSOC);

// Calculate housekeeping workload by floor
$floor_workload_query = "SELECT 
                          FLOOR(r.room_num/100) as floor,
                          COUNT(CASE WHEN r.room_status = 'pending' THEN 1 END) as pending_rooms,
                          COUNT(CASE WHEN r.room_status = 'occupied' THEN 1 END) as occupied_rooms,
                          COUNT(CASE WHEN r.room_status = 'maintenance' THEN 1 END) as maintenance_rooms
                        FROM rooms r
                        GROUP BY FLOOR(r.room_num/100)
                        ORDER BY floor";
$floor_workload_result = $db->query($floor_workload_query);
$floor_workload = $floor_workload_result->fetchAll(PDO::FETCH_ASSOC);

// Dashboard counts
$available_query = "SELECT COUNT(*) as available_count FROM rooms WHERE room_status = 'available'";
$available_result = $db->query($available_query);
$available_count = $available_result->fetch(PDO::FETCH_ASSOC)['available_count'];

$occupied_query = "SELECT COUNT(*) as occupied_count FROM rooms WHERE room_status = 'occupied'";
$occupied_result = $db->query($occupied_query);
$occupied_count = $occupied_result->fetch(PDO::FETCH_ASSOC)['occupied_count'];

$total_query = "SELECT COUNT(*) as total_count FROM rooms";
$total_result = $db->query($total_query);
$total_count = $total_result->fetch(PDO::FETCH_ASSOC)['total_count'];

$dirty_query = "SELECT COUNT(*) as dirty_count FROM rooms WHERE room_status = 'dirty'";
$dirty_result = $db->query($dirty_query);
$dirty_count = $dirty_result->fetch(PDO::FETCH_ASSOC)['dirty_count'];

$maintenance_query = "SELECT COUNT(*) as maintenance_count FROM rooms WHERE room_status = 'maintenance'";
$maintenance_result = $db->query($maintenance_query);
$maintenance_count = $maintenance_result->fetch(PDO::FETCH_ASSOC)['maintenance_count'];

$pending_tasks_query = "SELECT 
    (SELECT COUNT(*) FROM housekeeping_tasks WHERE status = 'pending') + 
    (SELECT COUNT(*) FROM maintenance_tasks WHERE status = 'pending') as pending_tasks_count";
$pending_tasks_result = $db->query($pending_tasks_query);
$pending_tasks_count = $pending_tasks_result->fetch(PDO::FETCH_ASSOC)['pending_tasks_count'];

// Function to get status badge style
function getStatusBadge($status) {
    switch (strtolower($status)) {
        case 'pending':
            return 'status-pending';
        case 'in-progress':
        case 'in progress':
            return 'status-progress';
        case 'completed':
            return 'status-completed';
        case 'available':
            return 'status-available';
        case 'occupied':
            return 'status-occupied';
        case 'maintenance':
            return 'status-maintenance';
        default:
            return 'status-default';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../inc/homepage_main.css">
    <title>Housekeeping/Maintenance Dashboard</title>
    <style>
        .main-content {
            margin-left: 230px;
            color: black;
        }

        h1 {
            color: black;
        }

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
        <span id="current-date"></span>
    </div>

        <div class="dashboard-container">
 
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
						<div class="card">
                            <div class="card-number">
                                <?php 
                                $occupancy_percentage = ($occupied_count / $total_count) * 100;
                                echo round($occupancy_percentage) . "%"; 
                                ?>
							</div>
                            <div class="card-title">Occupancy</div>
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
        </div>
    <script>
        const dateElement = document.getElementById('current-date');
        const options = { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' };
        dateElement.textContent = new Date().toLocaleDateString('en-US', options);
    </script>
</body>
</html>