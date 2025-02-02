<?php
require_once('../../../inc/db_connect.php');
$status = session_status();
if ($status == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Users</title>
		<link rel="stylesheet" type="text/css" href="../edit.css">
	</head>
	<main>
		<body>
			<div id = "data">
				 <h3>Insert the values for the new User</h3>
			</div>
			<br>
			<form action="addUsers.php" method="post" id="add_users_form">
				<input type="submit" value="Add User">
				<table>
					<tr>
						<th>Username:</th>
						<th> <input type="text" name="username"required></th>
					</tr>
					<tr>
						<th>Password:</th>
						<th><input type="password" name="password" required> </th>
					</tr>
					<tr>
						<th>Role:</th>
						<td>
							<select name="role" required>
								<option value="">Select Role</option>
								<option value="admin">Admin</option>
								<option value="front_desk">Front Desk</option>
								<option value="maintenance">Maintenance/Housekeeper</option>
							</select>
						</td>
					</tr>
				</table>
			</form>
			
			<button><a class="back" href="../../homepage.php">Back</a></button> <br><br>
		</body>
	</main>
</html>
