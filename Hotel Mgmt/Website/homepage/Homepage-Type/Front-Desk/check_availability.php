<?php
require_once '../../../inc/db_connect.php';

if (isset($_GET['room_num'])) {
    // Get all reservations for this room
    $query = "SELECT check_in_date, check_out_date 
              FROM reservations 
              WHERE room_num = :room_num 
              AND status IN ('confirmed', 'checked-in')
              AND check_out_date >= CURDATE()";
              
    $stmt = $db->prepare($query);
    $stmt->execute([':room_num' => $_GET['room_num']]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($reservations);
}

?>