<?php
require_once('../../Website/inc/db_connect.php');

$status = session_status();
if ($status == PHP_SESSION_NONE) {
    session_start();
}

$queryUsers = "
SELECT
  u_id,
  username,
  password,
  role
FROM
  users
";
$statementUsers = $db->prepare($queryUsers);
$statementUsers->execute();
$items = $statementUsers->fetchAll();
$statementUsers->closeCursor();

?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Users</title>
		<link rel="stylesheet" type="text/css" href="../inc/homepage_main.css">
		<style>
 			td {
       			padding: 10px 20px; /* Adjust space inside each cell, temporary */
   			}
		</style>
	</head>
	<main>
		<body>
			<div id="data">
				 <h3>Select an option: </h3>
			</div>
			<br>
			<table>
				<tr>
					<th>User ID</th>
					<th>Username</th>
					<th>Hashed Password</th>
					<th>Role</th>
					<th>Actions</th>
				</tr>

				<?php foreach ($items as $item) : ?>
				<tr>
					<td><?php echo $item['u_id']; ?></td>
					<td><?php echo $item['username']; ?></td>
					<td><?php echo $item['password']; ?></td>
					<td><?php echo $item['role']; ?></td>
					<td><button><a href="?update_id=<?php echo $item['u_id']; ?>">Update User</a></button></td>
				</tr>
				<?php endforeach; ?>
			</table>

			
		</body>
	</main>
</html>
