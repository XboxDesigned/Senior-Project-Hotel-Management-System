<?php
require_once 'Website/inc/db_connect.php';

// Check if the user is already logged in
$status = session_status();
if ($status == PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user'])) {
    header('Location: index.php'); // Redirect to the home page if already logged in
    exit();
}

// Check if the login form was submitted
if (isset($_POST['login'])) {
    // Retrieve user input (email and password)
    $user = $_POST['username'];
    $password = $_POST['password'];
	//var_dump($user);
    //var_dump($password);
    // Query the database to check if the provided user exists and matches the password
    $query = 'SELECT u_id, password, role
                FROM users 
                WHERE username = :user';
    $statement = $db->prepare($query);
    $statement->bindParam(':user', $user);
    $statement->execute();
    $user = $statement->fetch();
	//var_dump($user);
    if ($user) {
        // Verify the entered password against the stored hashed password
        if (password_verify($password, $user['password'])) {
            // Successful login
			echo 'itwork';
            $_SESSION['user'] = array(
                'user_id' => $user['user_id'],
                'role' => $user['role']
            );
            header('Location: homepage/homepage.php');
            exit();
        } else {
            $error_message = 'Invalid login credentials';
        }
    } else {
        $error_message = 'Invalid login credentials';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
</head>

<body>
    <h2>Login</h2>
    <?php if (!empty($error_message)){ ?>
			<p class="error"><?php echo $error_message; ?></p>
		<?php } ?>
    <form action="" method="POST">
        <div id="data">
			<table>
				<tr>
					<th> <label>Username:</label> </th>
					<th> <input type="username" name="username"required></th>
					<br>
				</tr>
				<tr> 
					<th> <label>Password:</label> </th>
					<th><input type="password" name="password" required> </th>
				</tr>
				<br>
				<tr>
					<th> <div id="buttons"> </th>
					<label>&nbsp;</label>
					<th><input type="submit" name= "login" value="Log In"><br></th>
					</div>
				</tr>
				
			</table>
	    </form>
        <button><a class="login-logout" href="registerForm.php">Register</a></button>
</body>