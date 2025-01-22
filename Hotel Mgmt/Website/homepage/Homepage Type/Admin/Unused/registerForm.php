<!-- WIP PAGE
<?php
require_once('Website/inc/db_connect.php');
$status = session_status();
if ($status == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Users</title>
		<link rel="stylesheet" type="text/css" href="edit.css">
	</head>
	<main>
		<body>
			<div id = "data">
				 <h3>Enter your information.</h3>
			</div>
			<br>
			<form action="addUsers.php" method="post" id="add_users_form">
				<table>
					<tr>
						<th>Username:</th>
						<th> <input type="type" name="user"required></th>
					</tr>
					<tr>
						<th>Password:</th>
						<th><input type="password" name="pass" required> </th>
					</tr>
				</table>
                <input type="submit" value="Register">
			</form>
			<button><a class="back" href="login.php">Back</a></button> <br><br>
		</body>
	</main>
</html>
-->
