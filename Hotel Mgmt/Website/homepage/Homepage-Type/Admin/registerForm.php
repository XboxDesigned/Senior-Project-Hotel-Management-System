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
		<link rel="stylesheet" type="text/css" href="../../main.css">
	</head>
	<main>
		<body>
		<h3>Insert the values for the new User</h3>
			<br>
			<form action="addUsers.php" method="post" id="add_users_form">
			<div class="addUsers-input-form">
				<input type="submit" value="Add User" class="add-user-btn">
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
			</div>
			</form>
		</body>
	</main>
</html>
