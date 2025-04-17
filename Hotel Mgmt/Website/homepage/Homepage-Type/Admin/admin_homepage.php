<?php
    // If any side menu POST button is clicked, clear any leftover GET variables
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      unset($_GET['update_room_id']);
      unset($_GET['delete_room_id']);
      unset($_GET['update_id']);
    }

    if (isset($_POST['addroom'])) {
      include('Homepage-Type/admin/add_room.php');
   }  

    if (isset($_POST['register'])) {
        include('Homepage-Type/Admin/registerUser.php');
    }
	
	if (isset($_POST['select'])) {
        include('Homepage-Type/Admin/selectUsers.php');
    }

    if (isset($_POST['placeholder'])) {
        include('Homepage-Type/Admin/page_template.php');
    }
    
    if (isset($_GET['delete_room_id'])) {
      include('Homepage-Type/admin/delete_rooms.php');
    }  

  // Check if update_id is set, then include updateUsers.php
  if (isset($_GET['update_id'])) {
    include('Homepage-Type/admin/updateUsers.php');
  }
  
  if (isset($_POST['confirm_delete'])) {
    include('Homepage-Type/admin/delete_rooms.php');
}


  if (isset($_POST['update_room'])) {
    include('Homepage-Type/admin/update_rooms.php');
}


	// Check if update_room_id is set, then include update_rooms.php
	if (isset($_GET['update_room_id'])) {
		include('Homepage-Type/admin/update_rooms.php');
	}
	
  // Check if modroomy is set, then include Modify_rooms.php
if (isset($_GET['modroomy'])) {
  include('Homepage-Type/admin/Modify_rooms.php');
  echo "<script>
      if (history.replaceState) {
          const url = new URL(window.location.href);
          url.searchParams.delete('modroomy');
          window.history.replaceState({}, document.title, url.pathname + url.search);
      }
  </script>";
}

if (isset($_POST['home'])) {
  include('../../Website/inc/dashboard.php');
}

else if (isset($_POST['rooms'])) {
  include('Homepage-Type/Front-Desk/rooms.php');
}

else if (isset($_POST['cancellations'])) {
  include('Homepage-Type/Front-Desk/cancellations.php');
}

else if (isset($_POST['guests'])) {
  include('Homepage-Type/Front-Desk/guests.php');
}

else if (isset($_POST['maintenance'])) {
  include('Homepage-Type/Front-Desk/maintenance.php');
}

else if (isset($_POST['modify'])) {
  include('Homepage-Type/Front-Desk/modify_reservation.php');
}

else if (isset($_POST['night_audit'])) {
	include('Homepage-Type/Front-Desk/night_audit.php');
  echo "<script>window.open('../../Website/inc/night_audit.php', '_blank');</script>";
}

else if (isset($_POST['book_room'])) {
  include('Homepage-Type/Front-Desk/book_room.php');
}

else if (isset($_POST['charge_manager'])) {
	include('Homepage-Type/Front-Desk/charge_manager.php');
  echo "<script>window.open('../../Website/inc/charge_manager.php', '_blank');</script>";
}

else if (isset($_POST['check_in'])) {
  include('Homepage-Type/Front-Desk/check_in.php');
}

else if (isset($_POST['check_out'])) {
  include('Homepage-Type/Front-Desk/check_out.php');
}

else if (isset($_POST['modroom'])) {
  include('Homepage-Type/admin/Modify_rooms.php');
}
if (isset($_GET['returnviewusers'])) {
    include('Homepage-Type/Admin/selectUsers.php');
    echo "<script>
        if (history.replaceState) {
            const url = new URL(window.location.href);
            url.searchParams.delete('returnviewusers');
            window.history.replaceState({}, document.title, url.pathname + url.search);
        }
    </script>";
}
if (isset($_POST['update_user'])) {
  include('Homepage-Type/admin/updateUsers.php');
}


else
{
}
?>



<!DOCTYPE html>
<html>
<head>
   
</head>

<body>

	<div class="side-buttons-container">
	<div class="side-buttons-top">
	

  
	<form method="post">
        <button type="submit" name="home" id="side-buttons">Home</button>
    </form>
	
    <form method="post">
        <button type="submit" name="register" id="side-buttons">Add New User</button>
    </form>
    
    <form method="post">
        <button type="submit" name="select" id="side-buttons">View Users</button>
        </form>
	
	<form method="post">
        <button type="submit" name="rooms" id="side-buttons">Rooms</button>
    </form>
    
  <form method="post">
       <button type="submit" name="modroom" id="side-buttons">Modify Rooms</button>
  </form>
	
	<form method="post">
        <button type="submit" name="cancellations" id="side-buttons">Cancellations</button>
    </form>
	
	<form method="post">
        <button type="submit" name="guests" id="side-buttons">Guests</button>
    </form>
	
	<form method="post">
        <button type="submit" name="check_in" id="side-buttons">Check In</button>
    </form>
	
	<form method="post">
        <button type="submit" name="check_out" id="side-buttons">Check Out</button>
    </form>

	<form method="post">
        <button type="submit" name="modify" id="side-buttons">Modify Reservation</button>
    </form>

	<form method="post">
        <button type="submit" name="maintenance" id="side-buttons">Action Request</button>
    </form>
	
	<form method="post">
        <button type="submit" name="charge_manager" id="side-buttons">Charge Manager</button>
    </form>
	
    <form method="post">
        <button type="submit" name="night_audit" id="side-buttons">Night Audit</button>
    </form>
	</div>
	
	</div>
	
	
	
</body>
</html>
