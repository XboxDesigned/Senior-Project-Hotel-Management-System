<?php
require_once('../../Website/inc/db_connect.php');

// Clear leftover GET parameters on POST (to stop update forms from reloading accidentally)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    unset($_GET['update_room_id']);
    unset($_GET['delete_room_id']);
    unset($_GET['update_id']);
}

$page_loaded = false;

// ========== Update + Delete Rooms ==========
if (isset($_POST['update_room'])) {
    include('Homepage-Type/admin/update_rooms.php');
    $page_loaded = true;
}

if (isset($_POST['confirm_delete'])) {
    include('Homepage-Type/admin/delete_rooms.php');
    $page_loaded = true;
}

if (isset($_GET['update_room_id'])) {
    include('Homepage-Type/admin/update_rooms.php');
    $page_loaded = true;
}

if (isset($_GET['delete_room_id'])) {
    include('Homepage-Type/admin/delete_rooms.php');
    $page_loaded = true;
}

if (isset($_GET['modroomy'])) {
    include('Homepage-Type/Admin/modify_room.php');
    echo "<script>
        if (history.replaceState) {
            const url = new URL(window.location.href);
            url.searchParams.delete('modroomy');
            window.history.replaceState({}, document.title, url.pathname + url.search);
        }
    </script>";
    $page_loaded = true;
}

if (isset($_POST['addroom'])) {
    include('Homepage-Type/admin/add_room.php');
    $page_loaded = true;
}

// ========== Update Users ==========
if (isset($_POST['update_user'])) {
    include('Homepage-Type/admin/updateUsers.php');
    $page_loaded = true;
}

if (isset($_GET['update_id'])) {
    include('Homepage-Type/admin/updateUsers.php');
    $page_loaded = true;
}

// ========== Other Admin Actions ==========
if (isset($_POST['register'])) {
    include('Homepage-Type/Admin/registerUser.php');
    $page_loaded = true;
}

if (isset($_POST['select'])) {
    include('Homepage-Type/Admin/selectUsers.php');
    $page_loaded = true;
}

if (isset($_POST['placeholder'])) {
    include('Homepage-Type/Admin/page_template.php');
    $page_loaded = true;
}

if (isset($_POST['home'])) {
    include('../../Website/inc/dashboard.php');
    $page_loaded = true;
}

if (isset($_POST['modify_room'])) {
    include('Homepage-Type/Admin/modify_room.php');
    $page_loaded = true;
}

if (isset($_POST['add_room'])) {
    include('Homepage-Type/Admin/add_room.php');
    $page_loaded = true;
}

if (isset($_POST['cancellations'])) {
    include('Homepage-Type/Front-Desk/cancellations.php');
    $page_loaded = true;
}

if (isset($_POST['guests'])) {
    include('Homepage-Type/Front-Desk/guests.php');
    $page_loaded = true;
}

if (isset($_POST['maintenance'])) {
    include('Homepage-Type/Front-Desk/maintenance.php');
    $page_loaded = true;
}

if (isset($_POST['modify'])) {
    include('Homepage-Type/Front-Desk/modify_reservation.php');
    $page_loaded = true;
}

if (isset($_POST['night_audit'])) {
    include('Homepage-Type/Front-Desk/night_audit.php');
    echo "<script>window.open('../../Website/inc/night_audit.php', '_blank');</script>";
    $page_loaded = true;
}

if (isset($_POST['book_room'])) {
  include('Homepage-Type/Front-Desk/book_room.php');
}

if (isset($_POST['book_room'])) {
    include('Homepage-Type/Front-Desk/book_room.php');
    $page_loaded = true;
}

if (isset($_POST['charge_manager'])) {
    include('Homepage-Type/Front-Desk/charge_manager.php');
    echo "<script>window.open('../../Website/inc/charge_manager.php', '_blank');</script>";
    $page_loaded = true;
}

if (isset($_POST['check_in'])) {
    include('Homepage-Type/Front-Desk/check_in.php');
    $page_loaded = true;
}

if (isset($_POST['check_out'])) {
    include('Homepage-Type/Front-Desk/check_out.php');
    $page_loaded = true;
}

if (!$page_loaded) {
    include('../../Website/inc/dashboard.php');
}
?>

<!DOCTYPE html>
<html>
<head></head>
<body>
    <div class="side-buttons-container">
        <div class="side-buttons-top">
            <form method="post"><button type="submit" name="home" id="side-buttons">Home</button></form>
            <form method="post"><button type="submit" name="register" id="side-buttons">Add New User</button></form>
            <form method="post"><button type="submit" name="select" id="side-buttons">Update User</button></form>
            <form method="post"><button type="submit" name="add_room" id="side-buttons">Create Room</button></form>
            <form method="post"><button type="submit" name="modify_room" id="side-buttons">Update Rooms</button></form>
            <form method="post"><button type="submit" name="cancellations" id="side-buttons">Cancellations</button></form>
            <form method="post"><button type="submit" name="guests" id="side-buttons">Guests</button></form>
			<form method="post"><button type="submit" name="book_room" id="side-buttons">Book Room</button></form>
            <form method="post"><button type="submit" name="check_in" id="side-buttons">Check In</button></form>
            <form method="post"><button type="submit" name="check_out" id="side-buttons">Check Out</button></form>
            <form method="post"><button type="submit" name="modify" id="side-buttons">Modify Reservation</button></form>
            <form method="post"><button type="submit" name="maintenance" id="side-buttons">Action Request</button></form>
            <form method="post"><button type="submit" name="charge_manager" id="side-buttons">Charge Manager</button></form>
            <form method="post"><button type="submit" name="night_audit" id="side-buttons">Night Audit</button></form>
        </div>
    </div>
</body>
</html>
