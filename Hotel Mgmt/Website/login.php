<?php
require_once '../Website/inc/db_connect.php';

// Check if the user is already logged in
$status = session_status();
if ($status == PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user'])) {
    header('Location: index.php'); // Redirect to the home page if already logged in
    exit();
}

//Initialize error msg
$error_message = '';

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
            $error_message = 'Invalid Username or Password.';
        }
    } else {
        $error_message = 'Invalid Username or Password.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="inc/main.css">
</head>
<body>
	<img src="./images/hilton_logo.jpg" alt="hilton-img" class="hilton-logo">
	<div class="input-container">
		<?php if (!empty($error_message)){ ?>
			<p class="error"><?php echo $error_message; ?></p>
		<?php } ?>
    <form action="" method="POST">
	<h2>Login</h2>
		<input type="username" name="username" class="box" placeholder="Enter Username:" ><br>
		<input type="password" name="password" class="box" placeholder="Enter Password:" ><br>
		<label>&nbsp;</label>
		<button type="submit" name="login" id="submit" class="login-register-btn">LOGIN</button><br>
		<h3>Don't have an account? <a href="register.php" class="register-link">Create Account</a></h3>
	</form>
	</div>
</body>
